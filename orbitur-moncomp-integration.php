<?php
/**
 * Plugin Name: Orbitur MonCompte Integration
 * Plugin URI: https://github.com/adipanchal/orbitur-moncomp-integration/
 * Description: Integrates WordPress with Orbitur MonCompte (SOAP) and WebCamp widgets. Login/Register + bookings skeleton.
 * Version: 1.2.1
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

// --- safe updater init inside plugins_loaded (no direct orbitur_log call before logger exists) ---
add_action('plugins_loaded', function () {
    // Try to include updater (composer or manual)
    $autoloader = ORBITUR_PLUGIN_DIR . '/vendor/autoload.php';
    $manual_loader = ORBITUR_PLUGIN_DIR . '/updater/plugin-update-checker.php';

    if (file_exists($autoloader)) {
        require_once $autoloader;
        if (function_exists('orbitur_log'))
            orbitur_log('Updater loaded via vendor/autoload.php');
    } elseif (file_exists($manual_loader)) {
        require_once $manual_loader;
        if (function_exists('orbitur_log'))
            orbitur_log('Updater loaded via updater/plugin-update-checker.php');
    } else {
        if (function_exists('orbitur_log'))
            orbitur_log('Updater not found (expected vendor or updater folder).');
    }

    // initialize update checker if available
    if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
        $factory = 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory';
        try {
            $updateChecker = $factory::buildUpdateChecker('https://github.com/adipanchal/orbitur-moncomp-integration', __FILE__, 'orbitur-moncomp-integration');
            if (method_exists($updateChecker, 'setBranch'))
                $updateChecker->setBranch('main');
            if (function_exists('orbitur_log'))
                orbitur_log('Updater initialized (PucFactory v5).');
        } catch (Throwable $e) {
            if (function_exists('orbitur_log'))
                orbitur_log('Updater exception: ' . $e->getMessage());
        }
    } elseif (class_exists('Puc_v4_Factory')) {
        try {
            $updateChecker = Puc_v4_Factory::buildUpdateChecker('https://github.com/adipanchal/orbitur-moncomp-integration', __FILE__, 'orbitur-moncomp-integration');
            if (method_exists($updateChecker, 'setBranch'))
                $updateChecker->setBranch('main');
            if (function_exists('orbitur_log'))
                orbitur_log('Updater initialized (Puc_v4_Factory).');
        } catch (Throwable $e) {
            if (function_exists('orbitur_log'))
                orbitur_log('Updater exception v4: ' . $e->getMessage());
        }
    }
}, 20);
// ---------- activation ----------
register_activation_hook(__FILE__, function () {
    if (!get_option('orbitur_moncomp_endpoint')) {
        update_option('orbitur_moncomp_endpoint', '');
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


