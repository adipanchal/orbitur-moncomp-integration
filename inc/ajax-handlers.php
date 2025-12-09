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
    // expect nonce param 'nonce'
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
    if (!$res['success']) {
        wp_send_json_error($res['error']);
    }

    wp_send_json_success(['redirect' => site_url('/area-cliente/bem-vindo')]);
}

function orbitur_handle_login_post()
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

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $pw = isset($_POST['pw']) ? wp_unslash($_POST['pw']) : '';
    $remember = !empty($_POST['remember']);

    if (empty($email) || empty($pw)) {
        wp_safe_redirect(add_query_arg('error', 'missing', $ref));
        exit;
    }

    $res = orbitur_do_login_procedure($email, $pw, $remember);
    if (!$res['success']) {
        wp_safe_redirect(add_query_arg('error', 'login_failed', $ref));
        exit;
    }

    wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
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
