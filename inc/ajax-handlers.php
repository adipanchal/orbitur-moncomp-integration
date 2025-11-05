<?php
if (!defined('ABSPATH')) exit;

/* Use admin-post (non-AJAX) handlers to process normal form submits */

/* Login handler */
add_action('admin_post_nopriv_orbitur_login_submit','orbitur_handle_login_submit');
add_action('admin_post_orbitur_login_submit','orbitur_handle_login_submit');
function orbitur_handle_login_submit(){
    if (!isset($_POST['orbitur_login_nonce']) || !wp_verify_nonce($_POST['orbitur_login_nonce'],'orbitur_login_action')) {
        wp_safe_redirect(site_url('/area-cliente/?err=nonce'));
        exit;
    }
    $email = sanitize_email($_POST['email'] ?? '');
    $password = sanitize_text_field($_POST['password'] ?? '');
    if (empty($email) || empty($password)) {
        wp_safe_redirect(site_url('/area-cliente/?err=missing'));
        exit;
    }
    $login_res = orbitur_login($email, $password);
    if (is_wp_error($login_res)) {
        wp_safe_redirect(site_url('/area-cliente/?err=invalid'));
        exit;
    }
    $idSession = $login_res;
    // optional getPerson
    $person_id = '';
    $person = orbitur_getPerson($idSession);
    if (!is_wp_error($person) && isset($person['personId'])) $person_id = $person['personId'];
    $user_id = orbitur_provision_wp_user_after_login($email, $person_id, $idSession);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/?err=usercreate'));
        exit;
    }
    wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
    exit;
}

/* Register handler */
add_action('admin_post_nopriv_orbitur_register_submit','orbitur_handle_register_submit');
add_action('admin_post_orbitur_register_submit','orbitur_handle_register_submit');
function orbitur_handle_register_submit(){
    if (!isset($_POST['orbitur_register_nonce']) || !wp_verify_nonce($_POST['orbitur_register_nonce'],'orbitur_register_action')) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta?err=nonce'));
        exit;
    }
    $first = sanitize_text_field($_POST['first_name'] ?? '');
    $last  = sanitize_text_field($_POST['last_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $password = sanitize_text_field($_POST['password'] ?? '');
    $accept = isset($_POST['accept_terms']) ? true : false;
    if (!$first || !$last || !$email || !$password || !$accept) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta?err=missing'));
        exit;
    }
    // check existing person
    $exists = orbitur_findPersonWithEmail($email);
    if (is_wp_error($exists)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta?err=soaper'));
        exit;
    }
    if ($exists === true) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta?err=exists'));
        exit;
    }
    $payload = ['firstname'=>$first,'lastname'=>$last,'email'=>$email,'password'=>$password];
    $create = orbitur_createAccount($payload);
    if (is_wp_error($create)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta?err=createfail'));
        exit;
    }
    // try auto-login
    $login_res = orbitur_login($email, $password);
    if (is_wp_error($login_res)) {
        wp_safe_redirect(site_url('/area-cliente/?msg=created'));
        exit;
    }
    $idSession = $login_res;
    $person_id = is_string($create) ? $create : '';
    $user_id = orbitur_provision_wp_user_after_login($email, $person_id, $idSession);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(site_url('/area-cliente/registo-de-conta?err=usercreate'));
        exit;
    }
    wp_safe_redirect(site_url('/area-cliente/bem-vindo'));
    exit;
}