<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Unified handlers for AJAX and admin-post.
 * Uses nonce action: 'orbitur_form_action'
 *
 * Exposes:
 * - AJAX actions: orbitur_login_ajax, orbitur_register_ajax
 * - admin-post actions: orbitur_login, orbitur_register
 */
// ---------- AJAX hooks ----------
add_action('wp_ajax_orbitur_login_ajax', 'orbitur_handle_login_ajax');
add_action('wp_ajax_nopriv_orbitur_login_ajax', 'orbitur_handle_login_ajax');

add_action('wp_ajax_orbitur_register_ajax', 'orbitur_handle_register_ajax');
add_action('wp_ajax_nopriv_orbitur_register_ajax', 'orbitur_handle_register_ajax');

// ---------- admin-post hooks (form posts) ----------
// add_action('admin_post_orbitur_login', 'orbitur_handle_login_post');
// add_action('admin_post_nopriv_orbitur_login', 'orbitur_handle_login_post');

// add_action('admin_post_orbitur_register', 'orbitur_handle_register_post');
// add_action('admin_post_nopriv_orbitur_register', 'orbitur_handle_register_post');

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
/* ------------------------
 * REGISTER handlers
 * -----------------------*/
/**
 * Generate MonCompte-compatible password
 * 6–10 alphanumeric characters
 */
function orbitur_generate_password()
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $len = rand(6, 10);
    $pw = '';
    for ($i = 0; $i < $len; $i++) {
        $pw .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pw;
}

/**
 * Send credentials email
 */
function orbitur_send_register_email($email, $password)
{

    $subject = 'Acesso à Área Cliente Orbitur';

    $message = "
Olá,

A sua conta foi criada com sucesso.

Email: {$email}
Password: {$password}

Pode alterar a password na Área Cliente após o primeiro login.

Obrigado,
Orbitur
";

    wp_mail($email, $subject, $message);
}
/* ======================================================
 * REGISTER (NO login, NO idSession)
 * ====================================================== */
function orbitur_handle_register_ajax()
{
    check_ajax_referer('orbitur_form_action', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $first = sanitize_text_field($_POST['first_name'] ?? '');
    $last = sanitize_text_field($_POST['last_name'] ?? '');

    if (!$email || !$first || !$last) {
        wp_send_json_error('missing_fields');
    }

    if (email_exists($email)) {
        wp_send_json_error('email_exists');
    }

    $password = wp_generate_password(8, false, false);

    /* Create MonCompte account FIRST */
    $mc = orbitur_moncomp_create_account([
        'email' => $email,
        'password' => $password,
        'first_name' => $first,
        'last_name' => $last,
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'postcode' => $_POST['postcode'] ?? '',
        'city' => $_POST['city'] ?? '',
        'country' => $_POST['country'] ?? 'PT',
    ]);
    if (is_wp_error($mc)) {
        wp_send_json_error($mc->get_error_message());
    }

    /* Create WP user */
    $uid = wp_create_user($email, $password, $email);
    if (is_wp_error($uid)) {
        wp_send_json_error($uid->get_error_message());
    }

    wp_update_user([
        'ID' => $uid,
        'display_name' => "$first $last",
        'first_name' => $first,
        'last_name' => $last,
    ]);

    wp_send_json_success([
        'password' => $password,
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
    if (!$user)
        wp_send_json_error('no_user');

    $profile = [
        'name' => $user->display_name ?: trim($user->first_name . ' ' . $user->last_name),
        'first' => $user->first_name,
        'last' => $user->last_name,
        'email' => $user->user_email,
        'phone' => get_user_meta($uid, 'billing_phone', true),
        'address' => get_user_meta($uid, 'billing_address_1', true),
        'zipcode' => get_user_meta($uid, 'billing_postcode', true),
        'city' => get_user_meta($uid, 'billing_city', true),
        'country' => get_user_meta($uid, 'billing_country', true),
        'memberNumber' => get_user_meta($uid, 'moncomp_customer_id', true),
        'occ_status' => get_user_meta($uid, 'occ_status', true),
        'occ_valid' => get_user_meta($uid, 'occ_valid_until', true),
        'idSession' => get_user_meta($uid, 'moncomp_idSession', true),
    ];

    wp_send_json_success($profile);
});
/**
 * AJAX: Get OCC membership status
 */
add_action('wp_ajax_orbitur_get_occ_status', function () {

    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in', 401);
    }

    $uid = get_current_user_id();

    $occ_id = get_user_meta($uid, 'moncomp_customer_id', true);
    $status = get_user_meta($uid, 'occ_status', true);
    $valid = get_user_meta($uid, 'occ_valid_until', true);

    if ($status === 'active' && $occ_id) {
        wp_send_json_success([
            'has_membership' => true,
            'member_number' => $occ_id,
            'valid_until' => $valid,
            'email' => wp_get_current_user()->user_email,
        ]);
    }

    wp_send_json_success([
        'has_membership' => false,
    ]);
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
        wp_send_json_error($res->get_error_message());
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