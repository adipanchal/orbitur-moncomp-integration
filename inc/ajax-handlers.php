<?php
if (!defined('ABSPATH')) exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/api.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/user-provision.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/parser.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';

// Health check
add_action('admin_post_nopriv_orbitur_health_check', function(){
    header('Content-Type: application/json');
    $cfg = orbitur_get_config();
    $out = ['plugin_loaded'=>true, 'endpoint'=> $cfg['endpoint'] ? $cfg['endpoint'] : 'NO_ENDPOINT'];
    $email = $cfg['admin_email']; $pw = $cfg['admin_pw'];
    if ($cfg['endpoint'] && $email && $pw) {
        $login = orbitur_login_moncomp($email, $pw);
        if (is_wp_error($login)) $out['moncomp_login'] = 'error: '.$login->get_error_message();
        else { $out['moncomp_login']='ok'; $out['idSession'] = $login; }
    } else {
        $out['moncomp_login'] = 'skipped - no creds';
    }
    echo json_encode($out);
    exit;
});

// Login handler
add_action('admin_post_nopriv_orbitur_login', function(){
    try {
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $pw = isset($_POST['password']) ? $_POST['password'] : '';
        $redirect = site_url('/area-cliente/bem-vindo');

        if (empty($email) || empty($pw)) {
            wp_safe_redirect(site_url('/area-cliente/?err=missing'));
            exit;
        }

        $login = orbitur_login_moncomp($email, $pw);
        if (is_wp_error($login)) {
            orbitur_log('Login failed: '. $login->get_error_message(), ['email'=>$email]);
            wp_safe_redirect(site_url('/area-cliente/?err=auth'));
            exit;
        }

        $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
        if (is_wp_error($user_id)) {
            orbitur_log('Provision failed: '. $user_id->get_error_message(), ['email'=>$email]);
            wp_safe_redirect(site_url('/area-cliente/?err=provision'));
            exit;
        }

        delete_transient('orbitur_bookings_'.$user_id);
        wp_safe_redirect($redirect);
        exit;

    } catch (Throwable $e) {
        orbitur_log('Login handler exception: '.$e->getMessage());
        wp_safe_redirect(site_url('/area-cliente/?err=exception'));
        exit;
    }
});

// Register handler (basic mapping)
add_action('admin_post_nopriv_orbitur_register', function(){
    try {
        $first = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $pw    = isset($_POST['password']) ? $_POST['password'] : '';
        $redirect = isset($_POST['redirect']) ? esc_url_raw($_POST['redirect']) : site_url('/area-cliente/bem-vindo');

        if (empty($first) || empty($last) || empty($email) || empty($pw)) {
            wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=missing'));
            exit;
        }

        $xml_body = '<ns1:createAccount><RqCreateAccount>'
            . '<email>'.esc_html($email).'</email>'
            . '<pw>'.esc_html($pw).'</pw>'
            . '<firstName>'.esc_html($first).'</firstName>'
            . '<lastName>'.esc_html($last).'</lastName>'
            . '<name>'.esc_html($first . ' ' . $last).'</name>'
            . '</RqCreateAccount></ns1:createAccount>';

        $res = orbitur_call_soap('createAccount', $xml_body);
        if (is_wp_error($res)) {
            orbitur_log('createAccount call failed', ['err'=>$res->get_error_message()]);
            wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=api'));
            exit;
        }

        $login = orbitur_login_moncomp($email, $pw);
        if (is_wp_error($login)) {
            orbitur_log('Login after create failed', ['email'=>$email, 'err' => $login->get_error_message()]);
            wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=login_after'));
            exit;
        }

        $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
        if (is_wp_error($user_id)) {
            orbitur_log('Provision after create failed', ['email'=>$email]);
            wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=provision'));
            exit;
        }

        delete_transient('orbitur_bookings_'.$user_id);
        wp_safe_redirect($redirect);
        exit;

    } catch (Throwable $e) {
        orbitur_log('Register handler exception: '.$e->getMessage());
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=exception'));
        exit;
    }
});

// Logout
add_action('admin_post_orbitur_logout', function(){
    if (!is_user_logged_in()) {
        wp_safe_redirect(site_url('/'));
        exit;
    }
    $uid = get_current_user_id();
    delete_user_meta($uid, 'moncomp_idSession');
    wp_logout();
    wp_safe_redirect(site_url('/'));
    exit;
});