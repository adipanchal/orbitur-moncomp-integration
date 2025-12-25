<?php
if (!defined('ABSPATH'))
    exit;

// ---------- AJAX hooks ----------
add_action('wp_ajax_orbitur_login_ajax', 'orbitur_handle_login_ajax');
add_action('wp_ajax_nopriv_orbitur_login_ajax', 'orbitur_handle_login_ajax');

add_action('wp_ajax_orbitur_register_ajax', 'orbitur_handle_register_ajax');
add_action('wp_ajax_nopriv_orbitur_register_ajax', 'orbitur_handle_register_ajax');

/**
 * AJAX: Check if session is still valid
 */
add_action('wp_ajax_orbitur_check_session', 'orbitur_check_session');
function orbitur_check_session()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['code' => 'session_expired', 'message' => 'Session expired']);
    }

    $uid = get_current_user_id();
    $idSession = get_user_meta($uid, 'moncomp_idSession', true);

    if (!$idSession) {
        wp_send_json_error(['code' => 'session_expired', 'message' => 'Session expired']);
    }

    // Verify session is still valid by calling MonCompte
    $person = orbitur_moncomp_get_person($idSession);
    if (is_wp_error($person)) {
        wp_send_json_error(['code' => 'session_expired', 'message' => 'Session expired']);
    }

    wp_send_json_success(['valid' => true]);
}

/**
 * AJAX: get fresh form nonce (cache-safe)
 */
add_action('wp_ajax_orbitur_get_form_nonce', 'orbitur_get_form_nonce');
add_action('wp_ajax_nopriv_orbitur_get_form_nonce', 'orbitur_get_form_nonce');

function orbitur_get_form_nonce()
{
    wp_send_json_success([
        'nonce' => wp_create_nonce('orbitur_form_action')
    ]);
}
function orbitur_do_login_procedure($email, $pw, $remember = false)
{
    // 1. LOGIN MONCOMPTE
    $mc = orbitur_moncomp_login($email, $pw);
    if (is_wp_error($mc)) {
        return ['success' => false, 'error' => $mc->get_error_message()];
    }

    $idSession = $mc['idSession'] ?? '';
    if (!$idSession) {
        return ['success' => false, 'error' => 'No idSession'];
    }

    // 2. GET PERSON (SOURCE OF TRUTH)
    $person = orbitur_moncomp_get_person($idSession);
    if (is_wp_error($person)) {
        return ['success' => false, 'error' => $person->get_error_message()];
    }

    // 3. GET OR CREATE WP USER
    $user = get_user_by('email', $email);
    if (!$user) {
        $uid = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => wp_generate_password(32),
        ]);
        if (is_wp_error($uid)) {
            return ['success' => false, 'error' => 'User create failed'];
        }
        $user = get_user_by('ID', $uid);
    }

    // 4. CACHE MONCOMP DATA (NOT AUTHORITATIVE)
    wp_update_user([
        'ID' => $user->ID,
        'first_name' => $person['first'],
        'last_name' => $person['last'],
        'display_name' => trim($person['first'] . ' ' . $person['last']),
    ]);

    update_user_meta($user->ID, 'billing_phone', $person['phone']);
    update_user_meta($user->ID, 'billing_address_1', $person['address']);
    update_user_meta($user->ID, 'billing_postcode', $person['zipcode']);
    update_user_meta($user->ID, 'billing_city', $person['city']);
    update_user_meta($user->ID, 'billing_country', $person['country']);

    update_user_meta($user->ID, 'occ_status', $person['occ_status']);
    update_user_meta($user->ID, 'moncomp_customer_id', $person['occ_id']);
    update_user_meta($user->ID, 'occ_valid_until', $person['occ_valid']);

    update_user_meta($user->ID, 'moncomp_idSession', $idSession);

    // 5. LOGIN WORDPRESS
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, $remember);

    return ['success' => true];
}
function orbitur_handle_login_ajax()
{
    check_ajax_referer('orbitur_form_action', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $pw = $_POST['pw'] ?? '';
    $remember = !empty($_POST['remember']);

    if (!$email || !$pw) {
        wp_send_json_error('Missing credentials');
    }

    $res = orbitur_do_login_procedure($email, $pw, $remember);

    if (empty($res['success'])) {
        wp_send_json_error($res['error'] ?? 'login_failed');
    }

    wp_send_json_success([
        'redirect' => site_url('/area-cliente/bem-vindo')
    ]);
}
/**
 * AJAX: Forgot password (MonCompte)
 */
add_action('wp_ajax_orbitur_forgot_password', 'orbitur_forgot_password_ajax');
add_action('wp_ajax_nopriv_orbitur_forgot_password', 'orbitur_forgot_password_ajax');

function orbitur_forgot_password_ajax()
{
    check_ajax_referer('orbitur_form_action', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    if (!$email) {
        wp_send_json_error('Email inválido');
    }

    $res = orbitur_moncomp_reset_password($email);

    if (is_wp_error($res)) {
        wp_send_json_error($res->get_error_message());
    }

    wp_send_json_success([
        'message' => 'Enviámos um email para redefinir a sua palavra-passe.'
    ]);
}
/* ======================================================
 * REGISTER (NO login, NO idSession)
 * ====================================================== */
function orbitur_handle_register_ajax()
{
    check_ajax_referer('orbitur_form_action', 'nonce');

    $required = [
        'email',
        'first_name',
        'last_name',
        'password',
        'civility',
        'dob',
    ];

    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            wp_send_json_error('missing ' . $f);
        }
    }

    $email = sanitize_email($_POST['email']);

    if (email_exists($email)) {
        wp_send_json_error('email_exists');
    }

    $birthDateRaw = sanitize_text_field($_POST['dob']);
    $birthDateIso = $birthDateRaw . 'T00:00:00.000+01:00';

    $civility = sanitize_text_field($_POST['civility']);
    if (!in_array($civility, ['Mr.', 'Ms.', 'Miss'], true)) {
        $civility = 'Mr.';
    }

    // --- Create MonCompte account FIRST ---
    $mc = orbitur_moncomp_create_account([
        'email' => $email,
        'password' => $_POST['password'],
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        'civility' => $civility,
        'birthDate' => $birthDateIso,
        // Contact / address
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'mobile' => sanitize_text_field($_POST['mobile'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'postcode' => sanitize_text_field($_POST['postcode'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'country' => sanitize_text_field($_POST['country'] ?? 'PT'),
        // Additional identity fields required by MonCompte
        'id_number' => sanitize_text_field($_POST['id_number'] ?? ''),
        'tax_number' => sanitize_text_field($_POST['tax_number'] ?? ''),
        'nationality' => sanitize_text_field($_POST['nationality'] ?? ''),
        'newsletter' => !empty($_POST['newsletter']) ? 'true' : 'false',
    ]);

    if (is_wp_error($mc)) {
        wp_send_json_error($mc->get_error_message());
    }

    // --- Create WordPress user AFTER MonCompte success ---
    $uid = wp_create_user($email, $_POST['password'], $email);
    if (is_wp_error($uid)) {
        wp_send_json_error($uid->get_error_message());
    }

    wp_update_user([
        'ID' => $uid,
        'display_name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
    ]);

    // Persist useful fields in user meta for reuse across the dashboard
    update_user_meta($uid, 'civility', $civility);
    update_user_meta($uid, 'identity_number', sanitize_text_field($_POST['id_number'] ?? ''));
    // Accept either 'nif' (form) or 'tax_number' (generic) if present
    update_user_meta($uid, 'tax_number', sanitize_text_field($_POST['nif'] ?? ($_POST['tax_number'] ?? '')));
    update_user_meta($uid, 'nationality', sanitize_text_field($_POST['nationality'] ?? ''));
    update_user_meta($uid, 'mobile_phone', sanitize_text_field($_POST['mobile'] ?? ''));
    // Save id_type and birthdate if provided
    update_user_meta($uid, 'id_type', sanitize_text_field($_POST['id_type'] ?? ''));
    update_user_meta($uid, 'birthdate', sanitize_text_field($_POST['dob'] ?? ''));

    // Billing-related fields (mirror existing naming used elsewhere)
    update_user_meta($uid, 'billing_phone', sanitize_text_field($_POST['phone'] ?? ''));
    update_user_meta($uid, 'billing_address_1', sanitize_text_field($_POST['address'] ?? ''));
    update_user_meta($uid, 'billing_postcode', sanitize_text_field($_POST['postcode'] ?? ''));
    update_user_meta($uid, 'billing_city', sanitize_text_field($_POST['city'] ?? ''));
    update_user_meta($uid, 'billing_country', sanitize_text_field($_POST['country'] ?? 'PT'));

    // Save MonCompte idCustomer if createAccount returned it
    if (!empty($mc['idCustomer'])) {
        update_user_meta($uid, 'moncomp_customer_id', sanitize_text_field($mc['idCustomer']));
    }

    wp_send_json_success([
        'redirect' => site_url('/area-cliente/')
    ]);
}
/**
 * AJAX: get profile
 */
add_action('wp_ajax_orbitur_get_profile', function () {
    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in', 401);
    }

    $uid = get_current_user_id();
    $user = get_userdata($uid);

    if (!$user) {
        wp_send_json_error('no_user');
    }

    // Raw fields (used for edit form & API updates)
    $address = get_user_meta($uid, 'billing_address_1', true);
    $zipcode = get_user_meta($uid, 'billing_postcode', true);
    $city = get_user_meta($uid, 'billing_city', true);
    $country = get_user_meta($uid, 'billing_country', true);

    // Display-only combined address (PROFILE VIEW ONLY)
    $morada_display = trim(
        implode(', ', array_filter([
            $address,
            $zipcode,
            $city,
        ]))
    );

    $profile = [
        // ===== NAME =====
        'name' => $user->display_name ?: trim($user->first_name . ' ' . $user->last_name),
        'first' => $user->first_name,
        'last' => $user->last_name,

        // ===== CONTACT =====
        'email' => $user->user_email,
        'phone' => get_user_meta($uid, 'billing_phone', true),

        // ===== ADDRESS (RAW – FOR EDIT FORM) =====
        'address' => $address,
        'zipcode' => $zipcode,
        'city' => $city,
        'country' => $country,

        // ===== ADDRESS (DISPLAY ONLY) =====
        'morada_display' => $morada_display,

        // ===== OCC / MONCOMP =====
        'memberNumber' => get_user_meta($uid, 'moncomp_customer_id', true),
        'occ_status' => get_user_meta($uid, 'occ_status', true),
        'occ_valid' => get_user_meta($uid, 'occ_valid_until', true),

        // ===== SESSION =====
        'idSession' => get_user_meta($uid, 'moncomp_idSession', true),
    ];

    // Additional identity fields stored in user meta (if present)
    $profile['civility'] = get_user_meta($uid, 'civility', true);
    $profile['id_number'] = get_user_meta($uid, 'identity_number', true);
    $profile['tax_number'] = get_user_meta($uid, 'tax_number', true);
    $profile['nationality'] = get_user_meta($uid, 'nationality', true);
    $profile['mobile'] = get_user_meta($uid, 'mobile_phone', true);
    $profile['id_type'] = get_user_meta($uid, 'id_type', true);
    $profile['birthdate'] = get_user_meta($uid, 'birthdate', true);

    wp_send_json_success($profile);
});
/**
 * AJAX: Get OCC membership status (Live from MonCompte)
 */
add_action('wp_ajax_orbitur_get_occ_status', function () {
    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in');
    }

    $uid = get_current_user_id();

    // Always start from a safe default response
    $response = [
        'has_membership' => false,
        'member_number' => '',
        'status' => '',
        'valid_until' => '',
        'email' => '',
    ];

    $idSession = get_user_meta($uid, 'moncomp_idSession', true);
    if (!$idSession) {
        wp_send_json_success($response);
    }

    $person = orbitur_moncomp_get_person($idSession);

    // 🚨 CRITICAL GUARD
    if (is_wp_error($person) || !is_array($person)) {
        wp_send_json_success($response);
    }

    $occId = $person['occ_id'] ?? '';
    $occStatus = $person['occ_status'] ?? '';
    $occValid = $person['occ_valid'] ?? '';

    // Membership exists ONLY if idFid exists
    if (!$occId) {
        wp_send_json_success($response);
    }

    // Membership detected
    $response['has_membership'] = true;
    $response['member_number'] = $occId;
    $response['email'] = $person['email'] ?? '';
    $response['start_date'] = '';

    /**
     * STATUS RULE (matches old site)
     * Active ONLY if:
     * - fidelity == true
     * - AND validity date exists
     */
    if ($occStatus === 'active' && !empty($occValid)) {
        $response['status'] = 'active';
    } else {
        $response['status'] = 'inactive';
    }

    /**
     * EXPIRY DATE LOGIC (OLD SITE COMPATIBLE)
     * MonCompte gives fidelityDate as START date.
     * OCC validity = END OF SAME YEAR (December).
     *
     * Example:
     * fidelityDate = 2025-10-22
     * expiry       = 12/2025
     */
    if (!empty($occValid)) {
        try {
            $dt = new DateTime($occValid);
            $year = $dt->format('Y');
            $response['start_date'] = $dt->format(format: 'Y-m-d');
            $response['valid_until'] = $year . '-12-31';
        } catch (Exception $e) {
            // silently ignore
        }
    }

    wp_send_json_success($response);
});
/**
 * AJAX: update profile (MonCompte is source of truth)
 */
add_action('wp_ajax_orbitur_update_profile', function () {

    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in', 401);
    }

    $uid = get_current_user_id();
    $idSession = get_user_meta($uid, 'moncomp_idSession', true);

    if (!$idSession) {
        wp_send_json_error('no_session');
    }

    $payload = [
        'email' => sanitize_email($_POST['email'] ?? ''),
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'zipcode' => sanitize_text_field($_POST['zipcode'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'country' => sanitize_text_field($_POST['country'] ?? ''),
    ];

    $res = orbitur_moncomp_update_person($idSession, $payload);

    if (is_wp_error($res)) {
        $error_msg = $res->get_error_message();
        // Detect session expired
        if (stripos($error_msg, 'session') !== false || stripos($error_msg, 'expiré') !== false) {
            wp_send_json_error(['code' => 'session_expired', 'message' => $error_msg]);
        }
        wp_send_json_error($error_msg);
    }

    // Always re-fetch from MonCompte
    $person = orbitur_moncomp_get_person($idSession);
    if (!is_wp_error($person)) {
        update_user_meta($uid, 'billing_phone', $person['phone']);
        update_user_meta($uid, 'billing_address_1', $person['address']);
        update_user_meta($uid, 'billing_postcode', $person['zipcode']);
        update_user_meta($uid, 'billing_city', $person['city']);
        update_user_meta($uid, 'billing_country', $person['country']);
    }

    wp_send_json_success([
        'status' => $res['status'] ?? 'updated',
        'message' => $res['message'] ?? 'Perfil atualizado'
    ]);
});
/**
 * AJAX: change password (DUAL MODE: strict → fallback)
 */
add_action('wp_ajax_orbitur_change_password', function () {

    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in');
    }

    $oldPw = trim($_POST['oldpw'] ?? '');
    $newPw = trim($_POST['newpw'] ?? '');

    $uid = get_current_user_id();
    $idSession = get_user_meta($uid, 'moncomp_idSession', true);

    if (!$idSession) {
        wp_send_json_error('No active session');
    }

    $result = orbitur_moncomp_update_password($idSession, $oldPw, $newPw);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_clear_auth_cookie();
    wp_set_current_user(0);

    wp_send_json_success([
        'redirect' => home_url('/area-cliente/'),
    ]);
});

/**
 * AJAX: get bookings (returns parsed upcoming/past arrays)
 */
add_action('wp_ajax_orbitur_get_bookings', function () {
    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');
    if (!is_user_logged_in())
        wp_send_json_error('not_logged_in', 401);

    $uid = get_current_user_id();
    $idSession = get_user_meta($uid, 'moncomp_idSession', true);
    if (empty($idSession)) {
        wp_send_json_error('no_session');
    }

    // Use orbitur_getBookingList_raw() from inc/api.php
    $raw = orbitur_getBookingList_raw($idSession);
    if (is_wp_error($raw)) {
        wp_send_json_error(['error' => 'soap_error', 'message' => $raw->get_error_message()]);
    }

    $parsed = orbitur_parse_booking_xml_string($raw);
    if (is_wp_error($parsed)) {
        wp_send_json_error(['error' => 'parse_error']);
    }

    $lists = orbitur_split_bookings_list($parsed);
    // Respond with lists (both upcoming and past)
    wp_send_json_success($lists);
});

/**
 * AJAX: logout (clears WP auth cookie and redirects)
 */
add_action('wp_ajax_orbitur_logout', function () {
    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');
    if (!is_user_logged_in())
        wp_send_json_error('not_logged_in', 401);

    wp_logout();
    wp_send_json_success(['redirect' => site_url('/area-cliente/')]);
});



add_action('wp_ajax_orbitur_occ_register', function () {

    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in', 401);
    }

    $uid = get_current_user_id();

    // Prevent duplicates
    if (get_user_meta($uid, 'occ_status', true) === 'active') {
        wp_send_json_error('already_member');
    }

    $payload = [
        'first_name' => sanitize_text_field($_POST['firstname'] ?? ''),
        'last_name' => sanitize_text_field($_POST['lastname'] ?? ''),
        'email' => sanitize_email($_POST['email'] ?? ''),
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'zipcode' => sanitize_text_field($_POST['zipcode'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'country' => sanitize_text_field($_POST['country'] ?? ''),
        'birthdate' => sanitize_text_field($_POST['birthdate'] ?? ''),
        'id_type' => sanitize_text_field($_POST['id_type'] ?? ''),
        'id_number' => sanitize_text_field($_POST['id_number'] ?? ''),
        'tax_number' => sanitize_text_field($_POST['tax_number'] ?? ''),
    ];

    if (empty($payload['email']) || empty($payload['first_name'])) {
        wp_send_json_error('missing_required');
    }

    /**
     * Call MonCompte createAccount / enrollment
     * (async membership creation)
     */
    $res = orbitur_moncomp_create_occ_member($payload);

    if (is_wp_error($res)) {
        wp_send_json_error($res->get_error_message());
    }

    update_user_meta($uid, 'occ_status', 'registering');
    update_user_meta($uid, 'occ_requested_at', time());

    wp_send_json_success([
        'status' => 'registering'
    ]);
});