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
add_action('admin_post_orbitur_login', 'orbitur_handle_login_post');
add_action('admin_post_nopriv_orbitur_login', 'orbitur_handle_login_post');

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
/* ------------------------
 * LOGIN handlers
 * -----------------------*/
if (!function_exists('orbitur_do_login_procedure')) {
    function orbitur_do_login_procedure($email, $pw, $remember = false)
    {
        // 1) Authenticate ONLY via MonCompte
        $mc = orbitur_moncomp_login($email, $pw);
        if (is_wp_error($mc)) {
            return ['success' => false, 'error' => $mc->get_error_message()];
        }

        $idSession = $mc['idSession'] ?? '';
        if (empty($idSession)) {
            return ['success' => false, 'error' => 'No idSession from MonCompte'];
        }

        // 2) Provision or fetch WP user
        $user_obj = orbitur_provision_wp_user_from_moncomp($email, $mc['customer'] ?? []);
        if (is_wp_error($user_obj)) {
            return ['success' => false, 'error' => $user_obj->get_error_message()];
        }

        $uid = intval($user_obj->ID);

        // 3) ALWAYS sync WordPress password with MonCompte password
        wp_set_password($pw, $uid);

        // 4) Store MonCompte session
        update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($idSession));
        update_user_meta($uid, 'moncomp_last_sync', time());

        // 5) Login user in WordPress
        wp_set_current_user($uid);
        wp_set_auth_cookie($uid, $remember);

        return ['success' => true, 'user_id' => $uid];
    }
}

function orbitur_handle_login_ajax()
{
    // strict single-nonce check
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'orbitur_form_action')) {
        wp_send_json_error('Invalid request (security).', 403);
    }

    $email = sanitize_email($_POST['email'] ?? '');
    $pw = isset($_POST['pw']) ? wp_unslash($_POST['pw']) : '';
    $remember = !empty($_POST['remember']);

    if (empty($email) || empty($pw)) {
        wp_send_json_error('Por favor preencha email e password.');
    }

    $res = orbitur_do_login_procedure($email, $pw, $remember);
    if (empty($res['success'])) {
        wp_send_json_error($res['error'] ?? 'login_failed');
    }

    wp_send_json_success(['redirect' => site_url('/area-cliente/bem-vindo')]);
}

function orbitur_handle_login_post()
{
    $ref = wp_get_referer() ?: site_url('/area-cliente/');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect($ref);
        exit;
    }

    // ✅ CORRECT NONCE CHECK
    if (
        !isset($_POST['orbitur_login_nonce']) ||
        !wp_verify_nonce($_POST['orbitur_login_nonce'], 'orbitur_form_action')
    ) {
        wp_safe_redirect(add_query_arg('error', 'invalid_nonce', $ref));
        exit;
    }

    $email = sanitize_email($_POST['email'] ?? '');
    $pw = $_POST['pw'] ?? '';
    $remember = !empty($_POST['remember']);

    if (!$email || !$pw) {
        wp_safe_redirect(add_query_arg('error', 'missing', $ref));
        exit;
    }

    $res = orbitur_do_login_procedure($email, $pw, $remember);

    if (!$res['success']) {
        wp_safe_redirect(add_query_arg('error', 'login_failed', $ref));
        exit;
    }

    wp_safe_redirect(site_url('/area-cliente/bem-vindo/'));
    exit;
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
    if (empty($email)) {
        wp_send_json_error('email_required');
    }

    $res = orbitur_moncomp_reset_password($email);

    if (is_wp_error($res)) {
        wp_send_json_error($res->get_error_message());
    }

    wp_send_json_success([
        'message' => 'Email de redefinição enviado. Verifique a sua caixa de entrada.'
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

    // Generate MonCompte-safe password (6–10 alphanumeric)
    $password = wp_generate_password(8, false, false);

    // Create WordPress user
    $uid = wp_create_user($email, $password, $email);
    if (is_wp_error($uid)) {
        wp_send_json_error($uid->get_error_message());
    }

    wp_update_user([
        'ID' => $uid,
        'display_name' => "{$first} {$last}",
        'first_name' => $first,
        'last_name' => $last,
    ]);

    // Save profile meta
    update_user_meta($uid, 'billing_phone', sanitize_text_field($_POST['phone'] ?? ''));
    update_user_meta($uid, 'billing_address_1', sanitize_text_field($_POST['address'] ?? ''));
    update_user_meta($uid, 'billing_postcode', sanitize_text_field($_POST['postcode'] ?? ''));
    update_user_meta($uid, 'billing_city', sanitize_text_field($_POST['city'] ?? ''));
    update_user_meta($uid, 'billing_country', sanitize_text_field($_POST['country'] ?? ''));
    update_user_meta($uid, 'nif', sanitize_text_field($_POST['nif'] ?? ''));

    // OPTIONAL: create MonCompte account (non-blocking)
    if (function_exists('orbitur_moncomp_create_account')) {
        orbitur_moncomp_create_account([
            'email' => $email,
            'password' => $password,
            'first_name' => $first,
            'last_name' => $last,
        ]);
    }

    // 🔑 Immediately login to MonCompte to obtain idSession
    $mc = orbitur_moncomp_login($email, $password);
    if (!is_wp_error($mc) && !empty($mc['idSession'])) {
        update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($mc['idSession']));
        update_user_meta($uid, 'moncomp_last_sync', time());
    }

    // TEMP (no SMTP): DO NOT auto-login user
    // Return password once so frontend can show alert
    wp_send_json_success([
        'redirect' => site_url('/area-cliente/'),
        'password' => $password
    ]);
}

function orbitur_handle_register_post()
{
    $ref = wp_get_referer() ?: site_url('/area-cliente/');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(add_query_arg('error', 'bad_method', $ref));
        exit;
    }
    if (!isset($_POST['orbitur_nonce']) || !wp_verify_nonce($_POST['orbitur_nonce'], 'orbitur_form_action')) {
        wp_safe_redirect(add_query_arg('error', 'invalid_nonce', $ref));
        exit;
    }

    $first = sanitize_text_field($_POST['first_name'] ?? '');
    $last = sanitize_text_field($_POST['last_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? wp_generate_password();

    if (empty($email) || empty($first) || empty($last)) {
        wp_safe_redirect(add_query_arg('error', 'missing', $ref));
        exit;
    }
    if (email_exists($email)) {
        wp_safe_redirect(add_query_arg('error', 'email_exists', $ref));
        exit;
    }

    $username = $email;
    $uid = wp_create_user($username, $pw, $email);
    if (is_wp_error($uid)) {
        wp_safe_redirect(add_query_arg('error', 'create_failed', $ref));
        exit;
    }

    wp_update_user(['ID' => $uid, 'display_name' => $first . ' ' . $last, 'first_name' => $first, 'last_name' => $last]);
    update_user_meta($uid, 'billing_phone', sanitize_text_field($_POST['phone'] ?? ''));
    // ... other meta as in AJAX handler

    wp_set_current_user($uid);
    wp_set_auth_cookie($uid);

    wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
    exit;
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
        'email' => $user->user_email,
        'phone' => get_user_meta($uid, 'billing_phone', true),
        'address' => get_user_meta($uid, 'billing_address_1', true),
        'zipcode' => get_user_meta($uid, 'billing_postcode', true),
        'city' => get_user_meta($uid, 'billing_city', true),
        'country' => get_user_meta($uid, 'billing_country', true),
        'nif' => get_user_meta($uid, 'nif', true),
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

    $status = get_user_meta($uid, 'occ_status', true);
    $member = get_user_meta($uid, 'moncomp_customer_id', true);
    $valid_until = get_user_meta($uid, 'occ_valid_until', true);

    if ($status === 'active' && !empty($member)) {
        wp_send_json_success([
            'status' => 'active',
            'memberNumber' => $member,
            'email' => wp_get_current_user()->user_email,
            'validUntil' => $valid_until,
        ]);
    }

    if ($status === 'pending') {
        wp_send_json_success([
            'status' => 'pending'
        ]);
    }

    wp_send_json_success([
        'status' => 'none'
    ]);
});
/**
 * AJAX: update profile (partial)
 */
add_action('wp_ajax_orbitur_update_profile', function () {
    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');
    if (!is_user_logged_in())
        wp_send_json_error('not_logged_in', 401);
    $uid = get_current_user_id();

    $allowed = [
        'name' => FILTER_SANITIZE_STRING,
        'email' => FILTER_SANITIZE_EMAIL,
        'phone' => FILTER_SANITIZE_STRING,
        'address' => FILTER_SANITIZE_STRING,
        'zipcode' => FILTER_SANITIZE_STRING,
        'city' => FILTER_SANITIZE_STRING,
        'country' => FILTER_SANITIZE_STRING,
        'nif' => FILTER_SANITIZE_STRING,
    ];

    $input = [];
    foreach ($allowed as $k => $filter) {
        if (isset($_POST[$k])) {
            $input[$k] = is_string($_POST[$k]) ? wp_strip_all_tags($_POST[$k]) : '';
        }
    }

    // Basic validations
    if (!empty($input['email']) && !is_email($input['email'])) {
        wp_send_json_error('invalid_email');
    }

    // Update WP profile fields
    if (!empty($input['name'])) {
        wp_update_user(['ID' => $uid, 'display_name' => sanitize_text_field($input['name'])]);
    }
    if (!empty($input['email'])) {
        // ensure email not used by other user
        $other = get_user_by('email', $input['email']);
        if ($other && intval($other->ID) !== intval($uid)) {
            wp_send_json_error('email_taken');
        }
        wp_update_user(['ID' => $uid, 'user_email' => sanitize_email($input['email'])]);
    }

    // update meta
    if (array_key_exists('phone', $input))
        update_user_meta($uid, 'billing_phone', sanitize_text_field($input['phone']));
    if (array_key_exists('address', $input))
        update_user_meta($uid, 'billing_address_1', sanitize_text_field($input['address']));
    if (array_key_exists('zipcode', $input))
        update_user_meta($uid, 'billing_postcode', sanitize_text_field($input['zipcode']));
    if (array_key_exists('city', $input))
        update_user_meta($uid, 'billing_city', sanitize_text_field($input['city']));
    if (array_key_exists('country', $input))
        update_user_meta($uid, 'billing_country', sanitize_text_field($input['country']));
    if (array_key_exists('nif', $input))
        update_user_meta($uid, 'nif', sanitize_text_field($input['nif']));

    wp_send_json_success(['msg' => 'saved']);
});

/**
 * AJAX: change password (DUAL MODE: strict → fallback)
 */
add_action('wp_ajax_orbitur_change_password', function () {

    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in', 401);
    }

    $uid = get_current_user_id();

    $oldPw = isset($_POST['oldpw']) ? wp_unslash($_POST['oldpw']) : '';
    $newPw = isset($_POST['newpw']) ? wp_unslash($_POST['newpw']) : '';

    if (empty($oldPw) || empty($newPw)) {
        wp_send_json_error('missing_password');
    }

    // MonCompte password rules: 6–10 alphanumeric ONLY
    if (!preg_match('/^[a-zA-Z0-9]{6,10}$/', $newPw)) {
        wp_send_json_error('invalid_password_format');
    }

    $idSession = get_user_meta($uid, 'moncomp_idSession', true);
    if (empty($idSession)) {
        wp_send_json_error('no_moncomp_session');
    }

    // 1️⃣ CHANGE PASSWORD IN MONCOMPTE (SOURCE OF TRUTH)
    $res = orbitur_moncomp_update_password($idSession, $oldPw, $newPw);

    if (is_wp_error($res)) {
        wp_send_json_error($res->get_error_message());
    }

    // 2️⃣ SYNC WORDPRESS PASSWORD
    wp_set_password($newPw, $uid);

    // 3️⃣ CLEAR SESSION (FORCE RELOGIN)
    wp_clear_auth_cookie();

    wp_send_json_success([
        'message' => 'password_changed',
        'redirect' => site_url('/area-cliente/')
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