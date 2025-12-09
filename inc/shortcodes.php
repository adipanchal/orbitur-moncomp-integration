<?php
if (!defined('ABSPATH'))
    exit;

/**
 * template renderer helper
 */
if (!function_exists('orbitur_render_template')) {
    function orbitur_render_template($tpl, $data = [])
    {
        $file = ORBITUR_PLUGIN_DIR . 'templates/' . $tpl;
        if (!file_exists($file))
            return '';
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}

/* Login shortcode - your existing login template file should output form that posts to admin-post.php */
add_shortcode('orbitur_login_form', function ($atts) {
    if (is_user_logged_in()) {
        return '<p>Já está conectado. <a href="' . esc_url(wp_logout_url(site_url('/area-cliente/'))) . '">Sair</a></p>';
    }
    return orbitur_render_template('tpl-login-form.php');
});

/* Register shortcode */
add_shortcode('orbitur_register_form', function ($atts) {
    if (is_user_logged_in()) {
        return '<p>Já tem conta. <a href="' . esc_url(site_url('/area-cliente/bem-vindo/')) . '">Ir para Área Cliente</a></p>';
    }
    return orbitur_render_template('tpl-register-form.php');
});

/* Area main (welcome) shortcode */
add_shortcode('orbitur_area_main', function ($atts) {
    if (!is_user_logged_in()) {
        // if not logged-in, redirect to login page (server side safety)
        wp_safe_redirect(site_url('/area-cliente/'));
        exit;
    }
    return orbitur_render_template('tpl-area-main.php');
});