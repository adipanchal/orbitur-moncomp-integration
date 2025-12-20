<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Provision WP user from registration data (basic).
 * Used when creating user account during registration.
 */
if (!function_exists('orbitur_provision_wp_user')) {
    function orbitur_provision_wp_user($email, $first_name = '', $last_name = '')
    {
        // if exists return
        if ($u = get_user_by('email', $email)) {
            return $u;
        }

        $uid = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => wp_generate_password(32, true),
        ]);
        if (is_wp_error($uid))
            return $uid;

        $display = trim($first_name . ' ' . $last_name) ?: $email;
        wp_update_user([
            'ID' => $uid,
            'display_name' => $display,
            'first_name' => $first_name,
            'last_name' => $last_name
        ]);

        return get_user_by('ID', $uid);
    }
}

// NOTE: Password is NOT authoritative here.
// MonCompte login always re-syncs the WordPress password.
/**
 * Provision WP user from MonCompte customer data.
 */
if (!function_exists('orbitur_provision_wp_user_from_moncomp')) {
    function orbitur_provision_wp_user_from_moncomp($email, $customer = [])
    {
        // if exists return
        if ($u = get_user_by('email', $email)) {
            // optionally map new fields
            return $u;
        }

        $uid = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => wp_generate_password(32, true),
        ]);
        if (is_wp_error($uid))
            return $uid;

        $display = trim(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) ?: $email;
        wp_update_user(['ID' => $uid, 'display_name' => $display, 'first_name' => $customer['firstName'] ?? '', 'last_name' => $customer['lastName'] ?? '']);

        if (!empty($customer['phone']))
            update_user_meta($uid, 'billing_phone', sanitize_text_field($customer['phone']));
        if (!empty($customer['address']))
            update_user_meta($uid, 'billing_address_1', sanitize_text_field($customer['address']));

        return get_user_by('ID', $uid);
    }
}

/**
 * Try refresh moncomp session (non-blocking)
 */
if (!function_exists('orbitur_try_refresh_moncomp')) {
    function orbitur_try_refresh_moncomp($email, $pw, $uid)
    {
        $mc = orbitur_moncomp_login($email, $pw);
        if (is_wp_error($mc))
            return $mc;
        if (!empty($mc['idSession'])) {
            update_user_meta($uid, 'moncomp_idSession', sanitize_text_field($mc['idSession']));
            update_user_meta($uid, 'moncomp_last_sync', time());
        }
        return $mc;
    }
}
