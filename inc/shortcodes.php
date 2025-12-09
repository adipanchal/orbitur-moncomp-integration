<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register shortcodes on init
 */
add_action('init', function () {
    // Login shortcode
    add_shortcode('orbitur_login_form', function ($atts) {
        if (is_user_logged_in()) {
            return '<p>Já está conectado. <a href="' . esc_url(wp_logout_url(site_url('/area-cliente/'))) . '">Sair</a></p>';
        }
        return orbitur_render_template('tpl-login-form.php');
    });

    // Register shortcode
    add_shortcode('orbitur_register_form', function ($atts) {
        if (is_user_logged_in()) {
            return '<p>Já tem conta. <a href="' . esc_url(site_url('/area-cliente/bem-vindo')) . '">Ir para Área Cliente</a></p>';
        }
        return orbitur_render_template('tpl-register-form.php');
    });

    // Protected area main
    add_shortcode('orbitur_area_main', function ($atts) {
        if (!is_user_logged_in()) {
            wp_safe_redirect(site_url('/area-cliente/'));
            exit;
        }
        return orbitur_render_template('tpl-area-main.php');
    });

    // bookings list
    add_shortcode('orbitur_bookings', function ($atts) {
        if (!is_user_logged_in())
            return '<p>Faça login para ver as suas reservas.</p>';
        $uid = get_current_user_id();
        $tkey = 'orbitur_bookings_' . $uid;
        $lists = get_transient($tkey);
        if ($lists === false) {
            $idSession = get_user_meta($uid, 'moncomp_idSession', true);
            if (empty($idSession))
                return '<p>Sem sessão MonCompte. Por favor inicie sessão.</p>';
            $raw = orbitur_getBookingList_raw($idSession);
            if (is_wp_error($raw))
                return '<p>Erro ao obter reservas: ' . esc_html($raw->get_error_message()) . '</p>';
            $parsed = orbitur_parse_booking_xml_string($raw);
            if (is_wp_error($parsed))
                return '<p>Erro ao interpretar reservas.</p>';
            $lists = orbitur_split_bookings_list($parsed);
            set_transient($tkey, $lists, 10 * MINUTE_IN_SECONDS);
        }
        return orbitur_render_template('tpl-bookings.php', ['lists' => $lists]);
    });
});

/**
 * Helper: render template files in templates/ folder
 */
if (!function_exists('orbitur_render_template')) {
    function orbitur_render_template($tpl, $data = [])
    {
        $file = ORBITUR_PLUGIN_DIR . 'templates/' . $tpl;
        if (!file_exists($file)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}

/**
 * Enqueue assets on front-end pages where area client exists.
 */
add_action('wp_enqueue_scripts', function () {
    // optional: list of pages slug where we want assets. adjust as needed.
    $load_on = ['area-cliente', 'registo-de-conta', 'bem-vindo'];
    if (!is_admin()) {
        // Only load if we are on matching page or if shortcode exists on page (just a simple detection)
        global $post;
        $should = false;
        if ($post && in_array($post->post_name, $load_on, true))
            $should = true;
        // OR if the content has our shortcode
        if (!$should && isset($post->post_content) && has_shortcode($post->post_content, 'orbitur_login_form'))
            $should = true;
        if (!$should && isset($post->post_content) && has_shortcode($post->post_content, 'orbitur_register_form'))
            $should = true;
        if (!$should)
            return;
    }

    // CSS
    $css = ORBITUR_PLUGIN_DIR . 'assets/css/orbitur-style.css';
    if (file_exists($css)) {
        wp_enqueue_style('orbitur-style', ORBITUR_PLUGIN_URL . 'assets/css/orbitur-style.css', [], filemtime($css));
    }

    // JS
    $js = ORBITUR_PLUGIN_DIR . 'assets/js/orbitur-forms.js';
    if (file_exists($js)) {
        wp_enqueue_script('orbitur-forms', ORBITUR_PLUGIN_URL . 'assets/js/orbitur-forms.js', ['jquery'], filemtime($js), true);
        wp_localize_script('orbitur-forms', 'orbitur_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('orbitur_form_action'),
            'redirect' => site_url('/area-cliente/bem-vindo'),
        ]);
    }
});
