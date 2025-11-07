<?php
if (!defined('ABSPATH')) exit;

// enqueue assets
add_action('wp_enqueue_scripts', function(){
    wp_register_style('orbitur-style', ORBITUR_PLUGIN_URL . 'assets/css/orbitur.css');
    wp_register_script('orbitur-js', ORBITUR_PLUGIN_URL . 'assets/js/orbitur.js', ['jquery'], null, true);
    wp_localize_script('orbitur-js', 'orbitur_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('orbitur_ajax_nonce'),
    ]);
    wp_enqueue_style('orbitur-style');
    wp_enqueue_script('orbitur-js');
});

// helper to render templates
function orbitur_render_template($tpl, $data = []) {
    $file = ORBITUR_PLUGIN_DIR . 'templates/' . $tpl;
    if (!file_exists($file)) return '';
    extract($data, EXTR_SKIP);
    ob_start();
    include $file;
    return ob_get_clean();
}

// LOGIN shortcode
add_shortcode('orbitur_login_form', function($atts){
    // if user logged-in show message
    if (is_user_logged_in()) {
        return '<p>Já está conectado. <a href="' . esc_url(wp_logout_url(site_url('/area-cliente/'))) . '">Sair</a></p>';
    }
    return orbitur_render_template('tpl-login-form.php');
});

// REGISTER shortcode
add_shortcode('orbitur_register_form', function($atts){
    if (is_user_logged_in()) {
        return '<p>Já tem conta. <a href="' . esc_url(site_url('/area-cliente/bem-vindo')) . '">Ir para Área Cliente</a></p>';
    }
    return orbitur_render_template('tpl-register-form.php');
});

// AREA main (protected)
add_shortcode('orbitur_area_main', function($atts){
    if (!is_user_logged_in()) {
        wp_safe_redirect(site_url('/area-cliente/'));
        exit;
    }
    return orbitur_render_template('tpl-area-main.php');
});

// bookings list (separate shortcode, shows bookings live)
add_shortcode('orbitur_bookings', function($atts){
    if (!is_user_logged_in()) return '<p>Faça login para ver as suas reservas.</p>';
    $uid = get_current_user_id();
    $tkey = 'orbitur_bookings_'.$uid;
    $lists = get_transient($tkey);
    if ($lists === false) {
        // call the api helper (returns parsed array or WP_Error)
        $idSession = get_user_meta($uid, 'moncomp_idSession', true);
        if (empty($idSession)) return '<p>Sem sessão MonCompte. Por favor inicie sessão.</p>';
        $raw = orbitur_getBookingList_raw($idSession);
        if (is_wp_error($raw)) return '<p>Erro ao obter reservas: '.esc_html($raw->get_error_message()).'</p>';
        $parsed = orbitur_parse_booking_xml_string($raw);
        if (is_wp_error($parsed)) return '<p>Erro ao interpretar reservas.</p>';
        $lists = orbitur_split_bookings_list($parsed);
        set_transient($tkey, $lists, 10 * MINUTE_IN_SECONDS);
    }
    return orbitur_render_template('tpl-bookings.php', ['lists' => $lists]);
});