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

add_action('admin_post_orbitur_register', 'orbitur_handle_register_post');
add_action('admin_post_nopriv_orbitur_register', 'orbitur_handle_register_post');

/* ------------------------
 * LOGIN handlers
 * -----------------------*/
if (!function_exists('orbitur_do_login_procedure')) {
    function orbitur_do_login_procedure($email, $pw, $remember = false)
    {
        // 1) try local WP login
        $user = get_user_by('email', $email);
        if ($user) {
            $creds = ['user_login' => $user->user_login, 'user_password' => $pw, 'remember' => $remember];
            $signed = wp_signon($creds, is_ssl());
            if (!is_wp_error($signed)) {
                // try to refresh moncomp session non-blocking
                orbitur_try_refresh_moncomp($email, $pw, $signed->ID);
                return ['success' => true, 'user_id' => $signed->ID];
            }
        }

        // 2) Call remote MonCompte
        $mc = orbitur_moncomp_login($email, $pw);
        if (is_wp_error($mc)) {
            return ['success' => false, 'error' => $mc->get_error_message()];
        }
        $idSession = $mc['idSession'] ?? '';
        if (empty($idSession)) {
            return ['success' => false, 'error' => 'No idSession from remote'];
        }

        // 3) Provision local user
        $user_obj = orbitur_provision_wp_user_from_moncomp($email, $mc['customer'] ?? []);
        if (is_wp_error($user_obj)) {
            return ['success' => false, 'error' => $user_obj->get_error_message()];
        }

        $uid = intval($user_obj->ID);
        update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($idSession));
        update_user_meta($uid, 'moncomp_last_sync', time());

        wp_set_current_user($uid);
        wp_set_auth_cookie($uid, $remember);

        return ['success' => true, 'user_id' => $uid];
    }
}

function orbitur_handle_login_ajax()
{
    // allow either the new unified nonce OR legacy login nonce for backward compatibility
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');
    $ok = false;
    if (!empty($nonce)) {
        if (wp_verify_nonce($nonce, 'orbitur_form_action') || wp_verify_nonce($nonce, 'orbitur_login_action')) {
            $ok = true;
        }
    }
    if (!$ok) {
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
        !wp_verify_nonce($_POST['orbitur_login_nonce'], 'orbitur_login_action')
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

/* ------------------------
 * REGISTER handlers
 * -----------------------*/
function orbitur_handle_register_ajax()
{
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'orbitur_form_action')) {
        wp_send_json_error('Invalid request (security).', 403);
    }

    $first = sanitize_text_field($_POST['first_name'] ?? '');
    $last = sanitize_text_field($_POST['last_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? wp_generate_password();
    if (empty($email) || empty($first) || empty($last)) {
        wp_send_json_error('Por favor preencha nome e email.');
    }
    if (email_exists($email)) {
        wp_send_json_error('Este email já existe. Faça login.');
    }

    // create WP user
    $username = $email;
    $uid = wp_create_user($username, $pw, $email);
    if (is_wp_error($uid)) {
        wp_send_json_error('Erro ao criar o utilizador: ' . $uid->get_error_message());
    }

    wp_update_user(['ID' => $uid, 'display_name' => $first . ' ' . $last, 'first_name' => $first, 'last_name' => $last]);

    // save some meta
    update_user_meta($uid, 'billing_phone', sanitize_text_field($_POST['phone'] ?? ''));
    update_user_meta($uid, 'billing_address_1', sanitize_text_field($_POST['address'] ?? ''));
    update_user_meta($uid, 'billing_postcode', sanitize_text_field($_POST['postcode'] ?? ''));
    update_user_meta($uid, 'billing_city', sanitize_text_field($_POST['city'] ?? ''));
    update_user_meta($uid, 'billing_country', sanitize_text_field($_POST['country'] ?? ''));
    update_user_meta($uid, 'nif', sanitize_text_field($_POST['nif'] ?? ''));

    // optional create on MonCompte (non-blocking)
    $mc_res = orbitur_moncomp_create_account($uid, ['first' => $first, 'last' => $last, 'email' => $email, 'password' => $pw]);
    if (!is_wp_error($mc_res) && !empty($mc_res['customerId'])) {
        update_user_meta($uid, 'moncomp_customer_id', sanitize_text_field($mc_res['customerId']));
    }

    // login user
    wp_set_current_user($uid);
    wp_set_auth_cookie($uid);

    wp_send_json_success(['redirect' => site_url('/area-cliente/bem-vindo')]);
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
        'idSession' => get_user_meta($uid, 'moncomp_idSession', true),
    ];

    wp_send_json_success($profile);
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
 * AJAX: change password (server-side)
 */
add_action('wp_ajax_orbitur_change_password', function () {
    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');
    if (!is_user_logged_in())
        wp_send_json_error('not_logged_in', 401);
    $uid = get_current_user_id();
    $old = isset($_POST['oldpw']) ? wp_unslash($_POST['oldpw']) : '';
    $new = isset($_POST['newpw']) ? wp_unslash($_POST['newpw']) : '';
    if (empty($old) || empty($new))
        wp_send_json_error('missing');

    $user = wp_get_current_user();
    // Verify current password
    if (!wp_check_password($old, $user->user_pass, $uid)) {
        wp_send_json_error('wrong_old');
    }

    // Validate new password strength minimally
    if (strlen($new) < 8) {
        wp_send_json_error('weak_password');
    }

    wp_set_password($new, $uid);
    // After wp_set_password, current session invalid — re-login needed; but we can set a flag
    wp_send_json_success(['msg' => 'password_changed']);
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

/**
 * AJAX: OCC register member
 */
add_action('wp_ajax_orbitur_occ_register', 'orbitur_occ_register');
function orbitur_occ_register()
{

    check_ajax_referer('orbitur_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in', 401);
    }

    $uid = get_current_user_id();

    // Prevent duplicate registration
    $existing = get_user_meta($uid, 'moncomp_customer_id', true);
    if (!empty($existing)) {
        wp_send_json_error('already_member');
    }

    // -------- Sanitize input --------
    $data = [
        'first_name' => sanitize_text_field($_POST['firstname'] ?? ''),
        'last_name' => sanitize_text_field($_POST['lastname'] ?? ''),
        'email' => sanitize_email($_POST['email'] ?? ''),
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'zipcode' => sanitize_text_field($_POST['zipcode'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'country' => sanitize_text_field($_POST['country'] ?? ''),
        'nationality' => sanitize_text_field($_POST['nationality'] ?? ''),
        'birthdate' => sanitize_text_field($_POST['birthdate'] ?? ''),
        'id_type' => sanitize_text_field($_POST['id_type'] ?? ''),
        'id_number' => sanitize_text_field($_POST['id_number'] ?? ''),
        'tax_number' => sanitize_text_field($_POST['tax_number'] ?? ''),
    ];

    if (empty($data['email']) || empty($data['first_name'])) {
        wp_send_json_error('missing_required_fields');
    }

    /**
     * ------------------------------------
     * CREATE OCC MEMBER (MONCOMPTE)
     * ------------------------------------
     * Replace this stub with real SOAP call later
     */

    // TEMP: generate OCC number (production-safe placeholder)
    $occ_number = 'OCC' . time() . rand(100, 999);

    // Store OCC data
    update_user_meta($uid, 'moncomp_customer_id', $occ_number);
    update_user_meta($uid, 'occ_status', 'active');
    update_user_meta($uid, 'occ_valid_until', date('Y-m-d', strtotime('+1 year')));

    // Optional: store submitted data
    foreach ($data as $k => $v) {
        if (!empty($v)) {
            update_user_meta($uid, 'occ_' . $k, $v);
        }
    }

    wp_send_json_success([
        'memberNumber' => $occ_number
    ]);
}