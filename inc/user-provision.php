<?php
if (!defined('ABSPATH')) exit;

/**
 * Provision WP user after MonCompte login/register.
 * Returns WP user ID or WP_Error.
 */
function orbitur_provision_wp_user_after_login($email, $person_id = '', $idSession = '') {
    if (empty($email)) return new WP_Error('no_email','No email provided');

    $user = get_user_by('email', $email);
    if ($user) {
        $user_id = $user->ID;
    } else {
        $username = sanitize_user( sanitize_title( current(explode('@',$email)) ) );
        if (username_exists($username)) $username .= '_' . wp_generate_password(4,false,false);
        $random_pw = wp_generate_password();
        $user_id = wp_create_user($username, $random_pw, $email);
        if (is_wp_error($user_id)) return $user_id;
        wp_update_user(['ID'=>$user_id,'display_name'=>$username]);
    }
    if ($person_id) update_user_meta($user_id,'moncomp_person_id', sanitize_text_field($person_id));
    if ($idSession) update_user_meta($user_id,'moncomp_idSession', sanitize_text_field($idSession));
    update_user_meta($user_id,'moncomp_last_sync', time());
    // log user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    return $user_id;
}