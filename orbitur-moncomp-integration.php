<?php
/**
 * Plugin Name: Orbitur MonCompte Integration
 * Plugin URI: https://github.com/adipanchal/orbitur-moncomp-integration/
 * Description: Integrates WordPress with Orbitur MonCompte (SOAP) and WebCamp widgets. Login/Register + bookings skeleton.
 * Version: 1.2 Beta
 * Author: Blendd
 */

if (!defined('ABSPATH'))
    exit;

// ---------- Updater (GitHub) - safe include ----------
add_action('plugins_loaded', function (): void {
    // avoid double-init
    if (defined('ORBITUR_UPDATER_LOADED'))
        return;
    define('ORBITUR_UPDATER_LOADED', true);

    // Try composer autoload first, then manual include
    $autoloader = __DIR__ . '/vendor/autoload.php';
    $manual_loader = __DIR__ . '/updater/plugin-update-checker.php';

    try {
        if (file_exists($autoloader)) {
            require_once $autoloader;
            orbitur_log('Loaded updater via vendor/autoload.php');
        } elseif (file_exists($manual_loader)) {
            require_once $manual_loader;
            orbitur_log('Loaded updater via updater/plugin-update-checker.php');
        } else {
            orbitur_log('Updater not found: vendor/autoload.php or updater/plugin-update-checker.php missing.');
            return;
        }

        // v5 namespaced factory?
        if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
            $factory = 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory';
            $updateChecker = $factory::buildUpdateChecker(
                'https://github.com/adipanchal/orbitur-moncomp-integration', // repo URL
                __FILE__, // full path to main plugin file
                'orbitur-moncomp-integration' // plugin slug (folder name)
            );
            orbitur_log('Updater: initialized PucFactory v5.');
        }
        // fallback for older library (v4)
        elseif (class_exists('Puc_v4_Factory')) {
            $updateChecker = Puc_v4_Factory::buildUpdateChecker(
                'https://github.com/adipanchal/orbitur-moncomp-integration',
                __FILE__,
                'orbitur-moncomp-integration'
            );
            orbitur_log('Updater: initialized Puc_v4_Factory.');
        } else {
            orbitur_log('Updater: no PucFactory class available after include.');
            return;
        }

        // set branch (change to 'main' or other branch/tag)
        if (isset($updateChecker) && is_object($updateChecker)) {
            if (method_exists($updateChecker, 'setBranch')) {
                $updateChecker->setBranch('main');
            }
            // If repo is private, set a GitHub token constant in wp-config.php:
            // define('ORBITUR_GITHUB_TOKEN','ghp_...');
            if (defined('ORBITUR_GITHUB_TOKEN') && !empty(ORBITUR_GITHUB_TOKEN)) {
                if (method_exists($updateChecker, 'setAuthentication')) {
                    $updateChecker->setAuthentication(ORBITUR_GITHUB_TOKEN);
                    orbitur_log('Updater: authentication token set.');
                }
            }

            orbitur_log('Updater: configured for repo https://github.com/adipanchal/orbitur-moncomp-integration (branch main).');
        }

    } catch (Throwable $e) {
        orbitur_log('Updater exception: ' . $e->getMessage());
        // do not throw — updater failure should not kill plugin
    }
}, 20);
// ---------- end Updater ----------

// constants
define('ORBITUR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ORBITUR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ORBITUR_LOG', WP_CONTENT_DIR . '/uploads/orbitur.log');
/**
 * Enqueue Orbitur CSS + JS Only on Frontend Pages Needed
 */
add_action('wp_enqueue_scripts', function () {
    if (!is_page(['area-cliente', 'registo-de-conta', 'bem-vindo'])) {
        return; // do not load on other pages
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
    }
});
// autoload includes
require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/shortcodes.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/ajax-handlers.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/api.php';

// activation
register_activation_hook(__FILE__, function () {
    if (!get_option('orbitur_moncomp_endpoint')) {
        update_option('orbitur_moncomp_endpoint', '');
    }
});

// add small admin settings link
add_action('admin_menu', function () {
    add_options_page('Orbitur MonCompte', 'Orbitur MonCompte', 'manage_options', 'orbitur-moncomp', function () {
        if (!current_user_can('manage_options'))
            return;
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