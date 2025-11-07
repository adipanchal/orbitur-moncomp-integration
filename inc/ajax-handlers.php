<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX: test connection & optional login test (admin)
 * Accessible in admin to quickly verify endpoint and test creds (staging only)
 */
add_action('wp_ajax_orbitur_wsdl_test', function(){
    if (!current_user_can('manage_options')) wp_die('Forbidden', '', 403);
    header('Content-Type: text/plain');

    $cfg = orbitur_get_config();
    if (empty($cfg['endpoint'])) {
        echo "No endpoint configured.\n";
        wp_die();
    }

    // config for test — prefer constants, fall back to options
    $email = defined('ORBITUR_MONCOMP_ADMIN_EMAIL') ? ORBITUR_MONCOMP_ADMIN_EMAIL : get_option('orbitur_moncomp_email','');
    $pw    = defined('ORBITUR_MONCOMP_ADMIN_PW') ? ORBITUR_MONCOMP_ADMIN_PW : get_option('orbitur_moncomp_password','');

    echo "Testing login for {$email}\n\n";

    $login = orbitur_login_moncomp($email, $pw);
    if (is_wp_error($login)) {
        echo "Login error: " . $login->get_error_message() . "\n";
        if ($data = $login->get_error_data()) {
            echo "Debug:\n";
            print_r($data);
        }
        wp_die();
    }

    echo "✅ Login successful, idSession = {$login}\n\n";

    $raw = orbitur_getBookingList_raw($login);
    if (is_wp_error($raw)) {
        echo "Booking list error: " . $raw->get_error_message() . "\n";
        if ($d = $raw->get_error_data()) print_r($d);
        wp_die();
    }

    echo "Fetching booking list...\n\n";
    echo "HTTP response snippet:\n";
    echo substr($raw,0,4000);
    wp_die();
});

/**
 * AJAX: login from frontend form (action 'orbitur_login')
 */
add_action('admin_post_nopriv_orbitur_login', function(){
    // handle login form (non-ajax)
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pw    = isset($_POST['password']) ? $_POST['password'] : '';
    $redirect = site_url('/area-cliente/bem-vindo');

    if (empty($email) || empty($pw)) {
        wp_safe_redirect(site_url('/area-cliente/?err=missing'));
        exit;
    }

    $login = orbitur_login_moncomp($email, $pw);
    if (is_wp_error($login)) {
        // login failed on MonCompte: redirect back with error
        wp_safe_redirect(site_url('/area-cliente/?err=auth'));
        exit;
    }

    // Fetch person (optional) - not implemented fully here; could call getPerson if needed
    // Provision a WP user and store idSession
    $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/?err=provision'));
        exit;
    }

    // Ensure transient cleared for fresh bookings
    delete_transient('orbitur_bookings_'.$user_id);

    // redirect to dashboard
    wp_safe_redirect($redirect);
    exit;
});

/**
 * AJAX: register handler (action 'orbitur_register')
 * Note: createAccount requires additional required fields per MonCompte docs.
 */
add_action('admin_post_nopriv_orbitur_register', function(){
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pw    = isset($_POST['password']) ? $_POST['password'] : '';
    $name  = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';

    if (empty($email) || empty($pw) || empty($name)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=missing'));
        exit;
    }

    // Build createAccount XML based on MonCompte docs (minimal example)
    $xml_body = '<ns1:createAccount>'
              . '<RqCreateAccount>'
              . '<email>'.esc_html($email).'</email>'
              . '<pw>'.esc_html($pw).'</pw>'
              . '<name>'.esc_html($name).'</name>'
              . '</RqCreateAccount>'
              . '</ns1:createAccount>';

    $res = orbitur_call_soap('createAccount', $xml_body);
    if (is_wp_error($res)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=api'));
        exit;
    }

    // After creation, try to login the user
    $login = orbitur_login_moncomp($email, $pw);
    if (is_wp_error($login)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=login_after'));
        exit;
    }

    $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=provision'));
        exit;
    }

    delete_transient('orbitur_bookings_'.$user_id);
    wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
    exit;
});

/**
 * Logout (clear MonCompte session and WP logout)
 */
add_action('admin_post_orbitur_logout', function(){
    if (!is_user_logged_in()) {
        wp_safe_redirect(site_url('/'));
        exit;
    }
    $user_id = get_current_user_id();
    delete_user_meta($user_id, 'moncomp_idSession');
    wp_logout();
    wp_safe_redirect(site_url('/'));
    exit;
});