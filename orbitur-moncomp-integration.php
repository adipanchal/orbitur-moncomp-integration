<?php
/**
 * Plugin Name: Orbitur MonCompte Integration
 * Plugin URI: https://github.com/adipanchal/orbitur-moncomp-integration/
 * Description: Integrates WordPress with Orbitur MonCompte (SOAP) and WebCamp widgets.
 * Version: 1.3
 * Author: Blendd
 */

if (!defined('ABSPATH')) {
    exit;
}

// constants
define('ORBITUR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ORBITUR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ORBITUR_LOG', WP_CONTENT_DIR . '/uploads/orbitur.log');

// ---------- include core files (require_once to avoid duplicate includes) ----------
require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/api.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/parser.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/user-provision.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/ajax-handlers.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/shortcodes.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/webcamp-widgets.php';
/* --- Enqueue assets for client-area pages only --- */
add_action('wp_enqueue_scripts', function () {

    /* ---------- COMMON CSS (all client pages) ---------- */
    if (is_page(['area-cliente', 'bem-vindo', 'registo-de-conta'])) {

        $css = ORBITUR_PLUGIN_DIR . 'assets/css/orbitur-style.css';
        if (file_exists($css)) {
            wp_enqueue_style(
                'orbitur-style',
                ORBITUR_PLUGIN_URL . 'assets/css/orbitur-style.css',
                [],
                filemtime($css)
            );
        }

        // Enqueue intl-tel-input CSS
        wp_enqueue_style(
            'intl-tel-input-css',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/css/intlTelInput.css',
            [],
            '25.10.1'
        );
    }

    /* ---------- AUTH FORMS JS (login + register) ---------- */
    if (is_page(['area-cliente', 'registo-de-conta'])) {

        // Enqueue intl-tel-input JS
        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/intlTelInput.min.js',
            [],
            '25.10.1',
            true
        );

        $js_forms = ORBITUR_PLUGIN_DIR . 'assets/js/orbitur-forms.js';
        if (file_exists($js_forms)) {
            wp_enqueue_script(
                'orbitur-forms',
                ORBITUR_PLUGIN_URL . 'assets/js/orbitur-forms.js',
                ['jquery', 'intl-tel-input-js'],
                filemtime($js_forms),
                true
            );

            wp_localize_script('orbitur-forms', 'orbitur_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('orbitur_form_action'),
                'redirect' => site_url('/area-cliente/bem-vindo/')
            ]);

            // Enqueue phone input initialization
            $js_phone = ORBITUR_PLUGIN_DIR . 'assets/js/intl-tel-input-init.js';
            if (file_exists($js_phone)) {
                wp_enqueue_script(
                    'intl-tel-input-init',
                    ORBITUR_PLUGIN_URL . 'assets/js/intl-tel-input-init.js',
                    ['jquery', 'intl-tel-input-js'],
                    filemtime($js_phone),
                    true
                );
            }
        }
    }

    /* ---------- DASHBOARD JS (ONLY bem-vindo) ---------- */
    if (is_page('bem-vindo')) {

        // Enqueue intl-tel-input JS for dashboard phone fields
        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/intlTelInput.min.js',
            [],
            '25.10.1',
            true
        );

        $js = ORBITUR_PLUGIN_DIR . 'assets/js/orbitur-dashboard.js';
        if (file_exists($js)) {
            wp_enqueue_script(
                'orbitur-dashboard',
                ORBITUR_PLUGIN_URL . 'assets/js/orbitur-dashboard.js',
                ['jquery', 'intl-tel-input-js'],
                filemtime($js),
                true
            );

            wp_localize_script('orbitur-dashboard', 'orbitur_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('orbitur_dashboard_nonce'),
                'area_client_url' => site_url('/area-cliente/')
            ]);

            // Enqueue phone input initialization for dashboard
            $js_phone = ORBITUR_PLUGIN_DIR . 'assets/js/intl-tel-input-init.js';
            if (file_exists($js_phone)) {
                wp_enqueue_script(
                    'intl-tel-input-init',
                    ORBITUR_PLUGIN_URL . 'assets/js/intl-tel-input-init.js',
                    ['jquery', 'intl-tel-input-js'],
                    filemtime($js_phone),
                    true
                );
            }
        }
    }

});

// --- safe updater init inside plugins_loaded (no direct orbitur_log call before logger exists) ---
add_action('plugins_loaded', function () {
    // Try to include updater (composer or manual)
    $autoloader = ORBITUR_PLUGIN_DIR . '/vendor/autoload.php';
    $manual_loader = ORBITUR_PLUGIN_DIR . '/updater/plugin-update-checker.php';

    if (file_exists($autoloader)) {
        require_once $autoloader;
    } elseif (file_exists($manual_loader)) {
        require_once $manual_loader;
    }

    // initialize update checker if available
    if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
        $factory = 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory';
        try {
            $updateChecker = $factory::buildUpdateChecker('https://github.com/adipanchal/orbitur-moncomp-integration', __FILE__, 'orbitur-moncomp-integration');
            if (method_exists($updateChecker, 'setBranch'))
                $updateChecker->setBranch('main');
        } catch (Throwable $e) {
            // Updater exception suppressed (no logging)
        }
    } elseif (class_exists('Puc_v4_Factory')) {
        try {
            $updateChecker = Puc_v4_Factory::buildUpdateChecker('https://github.com/adipanchal/orbitur-moncomp-integration', __FILE__, 'orbitur-moncomp-integration');
            if (method_exists($updateChecker, 'setBranch'))
                $updateChecker->setBranch('main');
        } catch (Throwable $e) {
            // Updater exception suppressed (no logging)
        }
    }
}, 20);

// ---------- activation ----------
register_activation_hook(__FILE__, function () {
    if (!get_option('orbitur_moncomp_endpoint')) {
        update_option('orbitur_moncomp_endpoint', '');
    }
});

/* --- Redirect rules for secure access --- */
/**
 * - If a guest accesses the protected welcome page (/area-cliente/bem-vindo/) redirect to /area-cliente/
 * - If a logged-in user opens the login page (/area-cliente/) redirect to /area-cliente/bem-vindo/
 */
add_action('template_redirect', function () {
    if (is_admin())
        return;

    // slugs used in this site (adjust if different)
    $login_slug = 'area-cliente'; // page with login form
    $welcome_slug = 'area-cliente/bem-vindo'; // welcome/dashboard

    $requested = trim($_SERVER['REQUEST_URI'], "/");
    // Normalize: sometimes WP adds page base
    $requested_path = untrailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    // Helper to check if current page is the specific WP page slug
    $is_login_page = is_page($login_slug) || (strpos($requested_path, '/' . $login_slug) !== false && is_page());
    $is_welcome_page = is_page('bem-vindo') || (strpos($requested_path, '/area-cliente/bem-vindo') !== false && is_page());

    // If user IS NOT logged in and visiting welcome page -> redirect to login
    if (!is_user_logged_in() && ($is_welcome_page || (strpos($requested_path, '/area-cliente/bem-vindo') !== false))) {
        wp_safe_redirect(site_url('/' . $login_slug . '/'));
        exit;
    }

    // If user IS logged in and is on /area-cliente/ (login page), redirect to welcome
    if (is_user_logged_in() && ($is_login_page && !$is_welcome_page)) {
        wp_safe_redirect(site_url('/area-cliente/bem-vindo/'));
        exit;
    }
});

// ---------- admin settings ----------
add_action('admin_menu', function () {
    add_options_page('Orbitur MonCompte', 'Orbitur MonCompte', 'manage_options', 'orbitur-moncomp', function () {
        if (!current_user_can('manage_options')) {
            return;
        }
        if ($_POST && check_admin_referer('orbitur_settings')) {
            update_option('orbitur_moncomp_endpoint', sanitize_text_field($_POST['endpoint'] ?? ''));
            update_option('orbitur_moncomp_api_key', sanitize_text_field($_POST['api_key'] ?? ''));
            echo '<div class="updated"><p>Saved.</p></div>';
        }
        $endpoint = esc_attr(get_option('orbitur_moncomp_endpoint', ''));
        $api_key = esc_attr(get_option('orbitur_moncomp_api_key', ''));
        ?>
        <div class="wrap">
            <h1>Orbitur MonCompte settings</h1>
            <form method="post">
                <?php wp_nonce_field('orbitur_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th>MonCompte WSDL / endpoint</th>
                        <td><input name="endpoint" value="<?php echo $endpoint; ?>" class="regular-text"
                                placeholder="https://.../MLC_MonCompteServices?wsdl"></td>
                    </tr>
                    <tr>
                        <th>Optional API key / X-API-KEY</th>
                        <td><input name="api_key" value="<?php echo $api_key; ?>" class="regular-text"></td>
                    </tr>
                </table>
                <p><button class="button button-primary">Save</button></p>
            </form>
        </div>
        <?php
    });
});
add_action('admin_menu', function () {
    add_management_page(
        'Fix Orbitur Names',
        'Fix Orbitur Names',
        'manage_options',
        'orbitur-fix-names',
        'orbitur_fix_names_page'
    );
});
