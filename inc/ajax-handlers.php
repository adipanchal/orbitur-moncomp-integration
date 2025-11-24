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

<?php
// inc/ajax-handlers.php (add these lines at the end of the file)

add_action('admin_post_nopriv_orbitur_login', 'orbitur_handle_login');
add_action('admin_post_orbitur_login', 'orbitur_handle_login');

function orbitur_handle_login() {
    // Always redirect back after processing (avoid printing)
    $ref = wp_get_referer() ? wp_get_referer() : site_url('/area-cliente/');

    // verify nonce and method
    if ( ! isset($_POST['orbitur_login_nonce']) || ! wp_verify_nonce( $_POST['orbitur_login_nonce'], 'orbitur_login_action' ) ) {
        wp_safe_redirect( add_query_arg('error','invalid_nonce', $ref) );
        exit;
    }

    // require POST
    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
        wp_safe_redirect( add_query_arg('error','bad_method', $ref) );
        exit;
    }

    $email = isset($_POST['email']) ? sanitize_email( wp_unslash($_POST['email']) ) : '';
    $pw    = isset($_POST['pw']) ? wp_unslash( $_POST['pw'] ) : '';

    if ( empty($email) || empty($pw) ) {
        wp_safe_redirect( add_query_arg('error','missing', $ref) );
        exit;
    }

    // --- CALL ORBITUR SOAP LOGIN (use your existing wrapper) ---
    // replace orbitur_soap_login() with your SOAP login function which returns idSession or WP_Error
    $login_result = orbitur_soap_login($email, $pw);

    if ( is_wp_error($login_result) ) {
        // failed at SOAP level (bad creds, remote error, etc)
        wp_safe_redirect( add_query_arg('error', 'soap_failed', $ref) );
        exit;
    }

    // login_result must include idSession (e.g. "4109968|176192...")
    $idSession = isset($login_result['idSession']) ? $login_result['idSession'] : '';

    if ( empty($idSession) ) {
        wp_safe_redirect( add_query_arg('error','invalid_credentials', $ref) );
        exit;
    }

    // --- Map or create local WP user ---
    // We create/find a WP user corresponding to the MonCompte email.
    if ( email_exists($email) ) {
        $user = get_user_by('email', $email);
        $user_id = $user->ID;
    } else {
        // create a local WP user with a random password
        $random_pw = wp_generate_password( 20, true );
        $user_id = wp_create_user( $email, $random_pw, $email );
        if ( is_wp_error($user_id) ) {
            wp_safe_redirect( add_query_arg('error','create_user_failed', $ref) );
            exit;
        }
        // optional: set display name etc
        wp_update_user( [ 'ID'=> $user_id, 'display_name' => strtok($email,'@') ] );
    }

    // Save MonCompte session & last sync time in usermeta (secure storage)
    update_user_meta($user_id, 'moncomp_idSession', $idSession);
    update_user_meta($user_id, 'moncomp_last_sync', time());

    // Log the user in to WP (create auth cookie)
    wp_set_auth_cookie( $user_id, isset($_POST['remember']) && $_POST['remember'] ? true : false );
    wp_set_current_user( $user_id );

    // Redirect to client area page (no credentials in URL)
    $redirect = site_url('/area-cliente/bem-vindo');
    wp_safe_redirect( $redirect );
    exit;
}

/**
 * orbitur_soap_login - placeholder wrapper
 * Replace with your SOAP call (your existing working code).
 * Should return array('idSession' => '...') or WP_Error on failure.
 */
function orbitur_soap_login($email, $pw) {
    // Example: call your existing SOAP helper and parse response
    // return array('idSession'=>'4109968|12345') or WP_Error
    try {
        $res = orbitur_call_login_soap($email, $pw); // implement this
        if ( is_wp_error($res) ) return $res;
        if ( empty($res['idSession']) ) return new WP_Error('no_session','No idSession returned');
        return $res;
    } catch (Exception $e) {
        return new WP_Error('soap_exc', $e->getMessage());
    }
}