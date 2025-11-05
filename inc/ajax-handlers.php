<?php
if (!defined('ABSPATH')) exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/api.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/user-provision.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/parser.php';

/**
 * Frontend login form handler (admin-post)
 * form action -> admin-post.php?action=orbitur_login
 */
add_action('admin_post_nopriv_orbitur_login', function(){
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pw = isset($_POST['password']) ? $_POST['password'] : '';
    $redirect = site_url('/area-cliente/bem-vindo');

    if (empty($email) || empty($pw)) {
        wp_safe_redirect(site_url('/area-cliente/?err=missing'));
        exit;
    }

    $login = orbitur_login_moncomp($email, $pw);
    if (is_wp_error($login)) {
        // return error to login page
        wp_safe_redirect(site_url('/area-cliente/?err=auth'));
        exit;
    }

    $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/?err=provision'));
        exit;
    }

    // clear user booking transient
    delete_transient('orbitur_bookings_'.$user_id);

    wp_safe_redirect($redirect);
    exit;
});

/**
 * Frontend register handler (admin-post)
 * form action -> admin-post.php?action=orbitur_register
 * Note: createAccount XML fields may need adjustment to match MonCompte expectation.
 */
// ---------- Register handler (expanded fields) ----------
add_action('admin_post_nopriv_orbitur_register', function(){
    // collect & sanitize
    $first = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pw    = isset($_POST['password']) ? $_POST['password'] : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
    $postal_code = isset($_POST['postal_code']) ? sanitize_text_field($_POST['postal_code']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    $nationality = isset($_POST['nationality']) ? sanitize_text_field($_POST['nationality']) : '';
    $birthdate = isset($_POST['birthdate']) ? sanitize_text_field($_POST['birthdate']) : '';
    $id_type = isset($_POST['id_type']) ? sanitize_text_field($_POST['id_type']) : '';
    $id_number = isset($_POST['id_number']) ? sanitize_text_field($_POST['id_number']) : '';
    $nif = isset($_POST['nif']) ? sanitize_text_field($_POST['nif']) : '';
    $redirect = isset($_POST['redirect']) ? esc_url_raw($_POST['redirect']) : site_url('/area-cliente/bem-vindo');

    // basic validation
    if (empty($first) || empty($last) || empty($email) || empty($pw)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=missing'));
        exit;
    }

    // Build createAccount XML - adjust node names if MonCompte requires different ones
    // I include many standard nodes; if the API expects other names change them.
    $xml_body = '<ns1:createAccount>'
              . '<RqCreateAccount>'
              . '<email>'.esc_html($email).'</email>'
              . '<pw>'.esc_html($pw).'</pw>'
              . '<firstName>'.esc_html($first).'</firstName>'
              . '<lastName>'.esc_html($last).'</lastName>'
              . '<name>'.esc_html($first . ' ' . $last).'</name>'
              . '<tel>'.esc_html($phone).'</tel>'
              . '<address>'.esc_html($address).'</address>'
              . '<postalCode>'.esc_html($postal_code).'</postalCode>'
              . '<city>'.esc_html($city).'</city>'
              . '<country>'.esc_html($country).'</country>'
              . '<nationality>'.esc_html($nationality).'</nationality>'
              . '<birthdate>'.esc_html($birthdate).'</birthdate>'
              . '<idType>'.esc_html($id_type).'</idType>'
              . '<idNumber>'.esc_html($id_number).'</idNumber>'
              . '<nif>'.esc_html($nif).'</nif>'
              . '</RqCreateAccount>'
              . '</ns1:createAccount>';

    $res = orbitur_call_soap('createAccount', $xml_body);
    if (is_wp_error($res)) {
        // API call failed (network/http)
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=api'));
        exit;
    }

    // Try to log in after creation
    $login = orbitur_login_moncomp($email, $pw);
    if (is_wp_error($login)) {
        // Provide some debugging hint if needed; redirect for now
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=login_after'));
        exit;
    }

    // Provision WP user and store profile data
    $user_id = orbitur_provision_wp_user_after_login($email, '', $login);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta/?err=provision'));
        exit;
    }

    // Save all submitted fields into usermeta for later editing/display
    update_user_meta($user_id, 'first_name', $first);
    update_user_meta($user_id, 'last_name', $last);
    update_user_meta($user_id, 'phone', $phone);
    update_user_meta($user_id, 'address', $address);
    update_user_meta($user_id, 'postal_code', $postal_code);
    update_user_meta($user_id, 'city', $city);
    update_user_meta($user_id, 'country', $country);
    update_user_meta($user_id, 'nationality', $nationality);
    update_user_meta($user_id, 'birthdate', $birthdate);
    update_user_meta($user_id, 'id_type', $id_type);
    update_user_meta($user_id, 'id_number', $id_number);
    update_user_meta($user_id, 'nif', $nif);

    // Clear booking cache / transient if any
    delete_transient('orbitur_bookings_'.$user_id);

    // Redirect to dashboard
    wp_safe_redirect($redirect);
    exit;
});

/**
 * Logout action
 */
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