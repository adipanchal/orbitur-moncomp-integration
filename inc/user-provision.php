<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure WP user exists for given email; store idSession + last sync
 */
function orbitur_provision_wp_user_after_login($email, $person_id = '', $idSession = '') {
    if (empty($email)) return new WP_Error('no_email','No email provided');

    $user = get_user_by('email', $email);
    if ($user) {
        $user_id = $user->ID;
    } else {
        // create a user (random password) - user will authenticate via MonCompte
        $base = sanitize_user( current(explode('@', $email)) );
        $username = $base ? $base : 'user' . time();
        if (username_exists($username)) $username .= '_' . wp_generate_password(4,false,false);
        $pw = wp_generate_password(12, false);
        $user_id = wp_create_user($username, $pw, $email);
        if (is_wp_error($user_id)) return $user_id;
        wp_update_user(['ID'=>$user_id, 'display_name'=>$username]);
    }

    if (!empty($person_id)) update_user_meta($user_id, 'moncomp_person_id', sanitize_text_field($person_id));
    if (!empty($idSession)) update_user_meta($user_id, 'moncomp_idSession', sanitize_text_field($idSession));
    update_user_meta($user_id, 'moncomp_last_sync', time());

    // log in the WP user
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    return $user_id;
}