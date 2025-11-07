<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcodes registration for Orbitur plugin.
 * IMPORTANT: do not call WP functions at file scope
 */

add_action('plugins_loaded', 'orbitur_register_shortcodes', 5);
add_action('init', 'orbitur_register_shortcodes', 5); 
add_filter('the_content', function($content){
    if (is_page(array('area-cliente','registo-de-conta','bem-vindo'))) {
        $content = do_shortcode($content);
    }
    return $content;
}, 12);
function orbitur_register_shortcodes() {
    // avoid duplicate registration
    static $registered = false;
    if ($registered) return;
    $registered = true;

    // Helper to safely load template from plugin templates folder
    $load_template = function($name) {
        $path = ORBITUR_PLUGIN_DIR . 'templates/' . $name;
        if (file_exists($path)) {
            ob_start();
            include $path;
            return ob_get_clean();
        }
        return '<!-- missing template: '.esc_html($name).' -->';
    };

    // LOGIN shortcode
    add_shortcode('orbitur_login_form', function($atts = []) use ($load_template) {
        // runtime check is fine
        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            return '<div class="orbitur-already-logged">Já ligado — <a href="'.esc_url(site_url('/area-cliente/bem-vindo')).'">Ir para Área Cliente</a></div>';
        }
        return $load_template('tpl-login-form.php');
    });

    // REGISTER shortcode
    add_shortcode('orbitur_register_form', function($atts = []) use ($load_template) {
        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            return '<div class="orbitur-already-logged">Já ligado — <a href="'.esc_url(site_url('/area-cliente/bem-vindo')).'">Ir para Área Cliente</a></div>';
        }
        return $load_template('tpl-register-form.php');
    });

    // AREA main
    add_shortcode('orbitur_area_main', function($atts = []) use ($load_template) {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            // don't redirect during rendering; show link
            return '<div class="orbitur-need-login">Por favor <a href="'.esc_url(site_url('/area-cliente/')).'">inicie sessão</a> para aceder à Área Cliente.</div>';
        }
        return $load_template('tpl-area-main.php');
    });

    // Bookings list shortcode (simple safe implementation)
    add_shortcode('orbitur_bookings', function($atts = []) {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            return '<p>Faça login para ver as suas reservas.</p>';
        }

        $uid = get_current_user_id();
        $tkey = 'orbitur_bookings_'.$uid;
        if (false !== ($cached = get_transient($tkey))) {
            $lists = $cached;
        } else {
            // Ensure helpers exist; orbitur_getBookingList_raw must be defined in inc/api.php
            if (!function_exists('orbitur_getBookingList_raw') || !function_exists('orbitur_parse_booking_xml_string')) {
                return '<p>Serviço de reservas indisponível.</p>';
            }
            $idSession = get_user_meta($uid, 'moncomp_idSession', true);
            if (empty($idSession)) return '<p>Sem sessão MonCompte. Por favor inicie sessão.</p>';

            $raw = orbitur_getBookingList_raw($idSession);
            if (is_wp_error($raw)) return '<p>Erro ao obter reservas: '.esc_html($raw->get_error_message()).'</p>';

            $parsed = orbitur_parse_booking_xml_string($raw);
            if (is_wp_error($parsed)) return '<p>Erro ao interpretar reservas.</p>';

            $lists = function_exists('orbitur_split_bookings_list') ? orbitur_split_bookings_list($parsed) : ['upcoming'=>[], 'past'=>[]];
            set_transient($tkey, $lists, 10*MINUTE_IN_SECONDS);
        }

        ob_start();
        echo '<div class="orbitur-bookings"><h3>Próximas</h3>';
        if (empty($lists['upcoming'])) {
            echo '<p>Não há estadias próximas.</p>';
        } else {
            echo '<ul>';
            foreach($lists['upcoming'] as $b) {
                echo '<li><strong>'.esc_html($b['site'] ?? '').'</strong> — '.esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))).
                     (!empty($b['url']) ? ' — <a href="'.esc_url($b['url']).'" target="_blank" rel="noopener">Gerir</a>' : '').
                     '</li>';
            }
            echo '</ul>';
        }
        echo '<h3>Anteriores</h3>';
        if (empty($lists['past'])) {
            echo '<p>Não há estadias anteriores.</p>';
        } else {
            echo '<ul>';
            foreach($lists['past'] as $b) {
                echo '<li><strong>'.esc_html($b['site'] ?? '').'</strong> — '.esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))).
                     (!empty($b['url']) ? ' — <a href="'.esc_url($b['url']).'" target="_blank" rel="noopener">Detalhes</a>' : '').
                     '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        return ob_get_clean();
    });
}