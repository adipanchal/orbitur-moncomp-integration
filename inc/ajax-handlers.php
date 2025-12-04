<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Unified login & register handlers: AJAX + admin-post
 *
 * Notes:
 * - Form template uses nonce field name 'orbitur_login_nonce' with action 'orbitur_login_action'.
 * - AJAX calls should send 'nonce' parameter with value from wp_create_nonce('orbitur_login_action').
 * - Field names expected: email, pw, remember (optional).
 */

/* ------------------------
 * LOGIN (AJAX + form)
 * -----------------------*/

// AJAX hook
add_action('wp_ajax_orbitur_ajax_login', 'orbitur_handle_login_ajax');
add_action('wp_ajax_nopriv_orbitur_ajax_login', 'orbitur_handle_login_ajax');

// admin-post (form POST)
add_action('admin_post_orbitur_login', 'orbitur_handle_login_post');
add_action('admin_post_nopriv_orbitur_login', 'orbitur_handle_login_post');

/**
 * AJAX login handler (returns JSON)
 */
function orbitur_handle_login_ajax()
{
    // Expect nonce param name 'nonce' from JS
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'orbitur_login_action')) {
        wp_send_json_error('Invalid request (security).', 403);
    }

    $email = sanitize_email($_POST['email'] ?? '');
    $pw = isset($_POST['pw']) ? wp_unslash($_POST['pw']) : '';
    $remember = !empty($_POST['remember']);

    if (empty($email) || empty($pw)) {
        wp_send_json_error('Por favor preencha email e password.');
    }

    // First try local WP login if user exists (username may not be email on some sites)
    $wp_user = get_user_by('email', $email);
    if ($wp_user) {
        // attempt WP signon using email as login
        $creds = [
            'user_login' => $wp_user->user_login,
            'user_password' => $pw,
            'remember' => $remember
        ];
        $signed = wp_signon($creds, is_ssl());
        if (!is_wp_error($signed)) {
            // optionally refresh moncomp session using SOAP (non-blocking)
            $maybe_mc = orbitur_try_refresh_moncomp($email, $pw, $signed->ID);
            $redirect = site_url('/area-cliente/bem-vindo');
            wp_send_json_success(['redirect' => $redirect]);
        }
        // else fallback to MonCompte login/provision
    }

    // If WP login not present or failed, call MonCompte SOAP login
    $mc = orbitur_soap_login($email, $pw);
    if (is_wp_error($mc)) {
        wp_send_json_error('Credenciais inválidas ou serviço indisponível.');
    }

    // Expect idSession in result
    $idSession = $mc['idSession'] ?? '';
    if (empty($idSession)) {
        wp_send_json_error('Login remoto falhou (sem sessão).');
    }

    // Provision local WP user from MonCompte info (implement in inc/user-provision.php)
    $user_obj = orbitur_provision_wp_user_from_moncomp($email, $mc['customer'] ?? []);
    if (is_wp_error($user_obj)) {
        wp_send_json_error('Erro ao criar utilizador local: ' . $user_obj->get_error_message());
    }

    // Save session and login locally
    $uid = intval($user_obj->ID);
    update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($idSession));
    update_user_meta($uid, 'moncomp_last_sync', time());

    wp_set_current_user($uid);
    wp_set_auth_cookie($uid, $remember);

    $redirect = site_url('/area-cliente/bem-vindo');
    wp_send_json_success(['redirect' => $redirect]);
}

/**
 * admin-post login handler (form submit)
 * uses nonce field named 'orbitur_login_nonce' created by wp_nonce_field('orbitur_login_action','orbitur_login_nonce')
 */
function orbitur_handle_login_post()
{
    $ref = wp_get_referer() ?: site_url('/area-cliente/');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(add_query_arg('error', 'bad_method', $ref));
        exit;
    }

    if (!isset($_POST['orbitur_login_nonce']) || !wp_verify_nonce($_POST['orbitur_login_nonce'], 'orbitur_login_action')) {
        wp_safe_redirect(add_query_arg('error', 'invalid_nonce', $ref));
        exit;
    }

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $pw = isset($_POST['pw']) ? wp_unslash($_POST['pw']) : '';
    $remember = !empty($_POST['remember']);

    if (empty($email) || empty($pw)) {
        wp_safe_redirect(add_query_arg('error', 'missing', $ref));
        exit;
    }

    // Try local WP user first
    $wp_user = get_user_by('email', $email);
    if ($wp_user) {
        $creds = [
            'user_login' => $wp_user->user_login,
            'user_password' => $pw,
            'remember' => $remember
        ];
        $signed = wp_signon($creds, is_ssl());
        if (!is_wp_error($signed)) {
            // optionally refresh moncomp session
            orbitur_try_refresh_moncomp($email, $pw, $signed->ID);
            wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
            exit;
        }
    }

    // Fall back to MonCompte SOAP login
    $mc = orbitur_soap_login($email, $pw);
    if (is_wp_error($mc)) {
        wp_safe_redirect(add_query_arg('error', 'soap_failed', $ref));
        exit;
    }

    $idSession = $mc['idSession'] ?? '';
    if (empty($idSession)) {
        wp_safe_redirect(add_query_arg('error', 'no_session', $ref));
        exit;
    }

    // Provision WP user
    $user_obj = orbitur_provision_wp_user_from_moncomp($email, $mc['customer'] ?? []);
    if (is_wp_error($user_obj)) {
        wp_safe_redirect(add_query_arg('error', 'provision_failed', $ref));
        exit;
    }

    $uid = intval($user_obj->ID);
    update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($idSession));
    update_user_meta($uid, 'moncomp_last_sync', time());

    wp_set_current_user($uid);
    wp_set_auth_cookie($uid, $remember);

    wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
    exit;
}

/* ------------------------
 * REGISTER (AJAX)
 * -----------------------*/

// AJAX register
add_action('wp_ajax_orbitur_ajax_register', 'orbitur_handle_register_ajax');
add_action('wp_ajax_nopriv_orbitur_ajax_register', 'orbitur_handle_register_ajax');

/**
 * Handle registration via AJAX (returns JSON)
 * Expected fields: first_name, last_name, email, password (optional), phone, address, postcode, city, country, nif
 */
function orbitur_handle_register_ajax()
{
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'orbitur_login_action')) {
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

    $username = $email; // use email as username
    $uid = wp_create_user($username, $pw, $email);
    if (is_wp_error($uid)) {
        wp_send_json_error('Erro ao criar o utilizador: ' . $uid->get_error_message());
    }

    wp_update_user([
        'ID' => $uid,
        'display_name' => $first . ' ' . $last,
        'first_name' => $first,
        'last_name' => $last
    ]);

    // save profile meta
    update_user_meta($uid, 'billing_phone', sanitize_text_field($_POST['phone'] ?? ''));
    update_user_meta($uid, 'billing_address_1', sanitize_text_field($_POST['address'] ?? ''));
    update_user_meta($uid, 'billing_postcode', sanitize_text_field($_POST['postcode'] ?? ''));
    update_user_meta($uid, 'billing_city', sanitize_text_field($_POST['city'] ?? ''));
    update_user_meta($uid, 'billing_country', sanitize_text_field($_POST['country'] ?? ''));
    update_user_meta($uid, 'nif', sanitize_text_field($_POST['nif'] ?? ''));

    // Optionally call MonCompte account creation wrapper (implement in inc/api.php)
    $mc_res = orbitur_moncomp_create_account($uid, [
        'first' => $first,
        'last' => $last,
        'email' => $email,
        'password' => $pw
    ]);
    if (!is_wp_error($mc_res) && !empty($mc_res['customerId'])) {
        update_user_meta($uid, 'moncomp_customer_id', sanitize_text_field($mc_res['customerId']));
    }

    // login user
    wp_set_current_user($uid);
    wp_set_auth_cookie($uid);

    wp_send_json_success(['redirect' => site_url('/area-cliente/bem-vindo')]);
}

/* ------------------------
 * Helpers / placeholders
 * -----------------------*/

/**
 * orbitur_soap_login: replace or implement this using your working SOAP code.
 * Must return array('idSession' => '...', 'customer' => [...]) or WP_Error on failure.
 */
function orbitur_soap_login($email, $pw)
{
    // TODO: replace this with your SOAP call wrapper. Example:
    // $res = orbitur_call_login_soap($email, $pw);
    // if (is_wp_error($res)) return $res;
    // return ['idSession' => $res['idSession'], 'customer' => $res['customer']];
    return new WP_Error('not_implemented', 'orbitur_soap_login is not implemented. Replace with your SOAP wrapper.');
}

/**
 * orbitur_provision_wp_user_from_moncomp: must create or update a WP user given MonCompte data.
 * Return WP_User on success or WP_Error on failure.
 */
function orbitur_provision_wp_user_from_moncomp($email, $customer = [])
{
    // If a user exists, return it
    if ($u = get_user_by('email', $email)) {
        // optionally map customer fields to usermeta here
        return $u;
    }

    // else create user
    $random_pw = wp_generate_password(24, true);
    $uid = wp_create_user($email, $random_pw, $email);
    if (is_wp_error($uid))
        return $uid;

    $user_data = [
        'ID' => $uid,
        'display_name' => trim(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) ?: $email,
        'first_name' => $customer['firstName'] ?? '',
        'last_name' => $customer['lastName'] ?? '',
    ];
    wp_update_user($user_data);

    // map a few fields to usermeta if available
    if (!empty($customer['phone']))
        update_user_meta($uid, 'billing_phone', sanitize_text_field($customer['phone']));
    if (!empty($customer['address']))
        update_user_meta($uid, 'billing_address_1', sanitize_text_field($customer['address']));

    return get_user_by('ID', $uid);
}

/**
 * Optional: attempt to refresh MonCompte session for a WP user (non-blocking).
 * If login succeeds, store session in usermeta.
 */
function orbitur_try_refresh_moncomp($email, $pw, $uid)
{
    // call SOAP login silently
    $mc = orbitur_soap_login($email, $pw);
    if (is_wp_error($mc))
        return $mc;
    if (!empty($mc['idSession'])) {
        update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($mc['idSession']));
        update_user_meta($uid, 'moncomp_last_sync', time());
    }
    return $mc;
}