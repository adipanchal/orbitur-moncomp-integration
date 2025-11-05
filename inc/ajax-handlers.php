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
    // collect & sanitize
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pw    = isset($_POST['password']) ? $_POST['password'] : '';
    $first = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
    $postal = isset($_POST['postal_code']) ? sanitize_text_field($_POST['postal_code']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    $nationality = isset($_POST['nationality']) ? sanitize_text_field($_POST['nationality']) : '';
    $birthdate = isset($_POST['birthdate']) ? sanitize_text_field($_POST['birthdate']) : '';
    $id_type = isset($_POST['id_type']) ? sanitize_text_field($_POST['id_type']) : '';
    $id_number = isset($_POST['id_number']) ? sanitize_text_field($_POST['id_number']) : '';
    $nif = isset($_POST['nif']) ? sanitize_text_field($_POST['nif']) : '';
    $redirect = isset($_POST['redirect']) ? esc_url_raw($_POST['redirect']) : site_url('/area-cliente/bem-vindo');

    if (empty($email) || empty($pw) || empty($first) || empty($last)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=missing'));
        exit;
    }

    // Build a simple createAccount XML — adjust fields later to match MonCompte exact schema
    $fullName = $first . ' ' . $last;
    $xml_body = '<ns1:createAccount>'
              . '<RqCreateAccount>'
              . '<email>'.esc_html($email).'</email>'
              . '<pw>'.esc_html($pw).'</pw>'
              . '<name>'.esc_html($fullName).'</name>'
              . '<phone>'.esc_html($phone).'</phone>'
              . '<address>'.esc_html($address).'</address>'
              . '<postalCode>'.esc_html($postal).'</postalCode>'
              . '<city>'.esc_html($city).'</city>'
              . '<country>'.esc_html($country).'</country>'
              . '<birthdate>'.esc_html($birthdate).'</birthdate>'
              // note: other fields may require specific node names in MonCompte
              . '</RqCreateAccount>'
              . '</ns1:createAccount>';

    $res = orbitur_call_soap('createAccount', $xml_body);
    if (is_wp_error($res)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=api'));
        exit;
    }

    // Attempt to log the user in via MonCompte and provision WP user
    $login = orbitur_login_moncomp($email, $pw);
    if (is_wp_error($login)) {
        // account might be created but login failed — redirect with message
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=login_after'));
        exit;
    }

    // Create or update WP user and store extra meta
    $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=provision'));
        exit;
    }

    // store submitted profile data as user meta (so you can show/edit it later)
    update_user_meta($user_id, 'first_name', $first);
    update_user_meta($user_id, 'last_name', $last);
    update_user_meta($user_id, 'phone', $phone);
    update_user_meta($user_id, 'address', $address);
    update_user_meta($user_id, 'postal_code', $postal);
    update_user_meta($user_id, 'city', $city);
    update_user_meta($user_id, 'country', $country);
    update_user_meta($user_id, 'nationality', $nationality);
    update_user_meta($user_id, 'birthdate', $birthdate);
    update_user_meta($user_id, 'id_type', $id_type);
    update_user_meta($user_id, 'id_number', $id_number);
    update_user_meta($user_id, 'nif', $nif);

    // clear booking transient and redirect to dashboard
    delete_transient('orbitur_bookings_'.$user_id);
    wp_safe_redirect($redirect);
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