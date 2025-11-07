<?php
if (!defined('ABSPATH')) exit;

// LOGIN AJAX
add_action('wp_ajax_orbitur_login', 'orbitur_ajax_login');
add_action('wp_ajax_nopriv_orbitur_login', 'orbitur_ajax_login');

function orbitur_ajax_login() {
    check_ajax_referer('orbitur_ajax_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $pw    = $_POST['password'] ?? '';

    if (empty($email) || empty($pw)) {
        wp_send_json_error('Preencha email e password.');
    }

    // Try to sign in WP user first (if user exists)
    $creds = ['user_login' => $email, 'user_password' => $pw, 'remember' => true];
    $user = wp_signon($creds, is_ssl());
    if (is_wp_error($user)) {
        // If WP login fails, attempt MonCompte login (if configured) and provision WP user
        $mc = orbitur_moncomp_login($email, $pw);
        if (is_wp_error($mc)) {
            wp_send_json_error('Credenciais inválidas.');
        }
        // provision WP user
        $user = orbitur_provision_wp_user_from_moncomp($mc, $email, $pw);
        if (is_wp_error($user)) wp_send_json_error('Erro ao criar utilizador local.');
        // sign on newly created user
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
    } else {
        // successful WP login - optionally re-check moncomp session
        $mc = orbitur_moncomp_login($email, $pw);
        if (!is_wp_error($mc) && !empty($mc['idSession'])) {
            update_user_meta($user->ID, 'moncomp_idSession', $mc['idSession']);
            update_user_meta($user->ID, 'moncomp_last_sync', time());
        }
    }

    // success
    wp_send_json_success(['redirect' => site_url('/area-cliente/bem-vindo')]);
}

// REGISTER AJAX
add_action('wp_ajax_orbitur_register', 'orbitur_ajax_register');
add_action('wp_ajax_nopriv_orbitur_register', 'orbitur_ajax_register');

function orbitur_ajax_register() {
    check_ajax_referer('orbitur_ajax_nonce', 'nonce');

    // collect expected fields
    $first = sanitize_text_field($_POST['first_name'] ?? '');
    $last  = sanitize_text_field($_POST['last_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $pw    = $_POST['password'] ?? wp_generate_password();
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $address = sanitize_text_field($_POST['address'] ?? '');
    $postcode = sanitize_text_field($_POST['postcode'] ?? '');
    $city = sanitize_text_field($_POST['city'] ?? '');
    $country = sanitize_text_field($_POST['country'] ?? '');
    $nif = sanitize_text_field($_POST['nif'] ?? '');

    if (empty($email) || empty($first) || empty($last)) {
        wp_send_json_error('Por favor preencha nome e email.');
    }

    if (email_exists($email)) {
        wp_send_json_error('Este email já existe. Faça login.');
    }

    // create WP user
    $username = $email; // use email as username
    $uid = wp_create_user($username, $pw, $email);
    if (is_wp_error($uid)) {
        wp_send_json_error('Erro ao criar o utilizador: ' . $uid->get_error_message());
    }

    // set profile fields
    wp_update_user([
        'ID' => $uid,
        'display_name' => $first . ' ' . $last,
        'first_name' => $first,
        'last_name' => $last
    ]);
    update_user_meta($uid, 'billing_phone', $phone);
    update_user_meta($uid, 'billing_address_1', $address);
    update_user_meta($uid, 'billing_postcode', $postcode);
    update_user_meta($uid, 'billing_city', $city);
    update_user_meta($uid, 'billing_country', $country);
    update_user_meta($uid, 'nif', $nif);

    // Optionally call MonCompte createAccount (if endpoint set)
    $mc_create = orbitur_moncomp_create_account($uid, [
        'first' => $first, 'last' => $last, 'email' => $email, 'password' => $pw
    ]);
    if (!is_wp_error($mc_create) && !empty($mc_create['customerId'])) {
        update_user_meta($uid, 'moncomp_customer_id', $mc_create['customerId']);
    }

    // auto signon
    wp_set_current_user($uid);
    wp_set_auth_cookie($uid);

    wp_send_json_success(['redirect' => site_url('/area-cliente/bem-vindo')]);
}