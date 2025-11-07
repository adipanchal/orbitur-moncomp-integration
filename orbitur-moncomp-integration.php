<?php
/**
 * Plugin Name: Orbitur MonCompte Integration
 * Plugin URI: https://github.com/adipanchal/orbitur-moncomp-integration/
 * Description: Integrates WordPress with Orbitur MonCompte (SOAP) and WebCamp widgets. Login/Register + bookings skeleton.
 * Version: 1.0
 * Author: Blendd
 */

if (!defined('ABSPATH')) {
    exit;
}
define('ORBITUR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ORBITUR_PLUGIN_URL', plugin_dir_url(__FILE__));
// force include shortcodes early (helps anonymous rendering)
$shortcodes_file = ORBITUR_PLUGIN_DIR . 'inc/shortcodes.php';
if ( file_exists( $shortcodes_file ) ) {
    require_once $shortcodes_file;
} else {
    error_log('Orbitur: shortcodes.php missing.');
}

/**
 * Safe logger - fall back to error_log if plugin logger missing
 */
function orbitur_main_log($msg, $context = null) {
    // plugin logger file
    $file = ORBITUR_PLUGIN_DIR . 'orbitur.log';
    $txt = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if (!empty($context)) $txt .= ' ' . print_r($context, true);
    $txt .= PHP_EOL;
    @file_put_contents($file, $txt, FILE_APPEND | LOCK_EX);

    // WP error log too
    if ( defined('WP_DEBUG') && WP_DEBUG ) {
        error_log('[Orbitur] ' . $msg . (is_array($context) ? ' ' . print_r($context, true) : ''));
    }
}

/**
 * Updater — try to load composer autoload first, fallback to bundled file,
 * but do not fatal if not present.
 */
try {
    $autoloader = ORBITUR_PLUGIN_DIR . 'vendor/autoload.php';
    $manual_loader = ORBITUR_PLUGIN_DIR . 'updater/plugin-update-checker.php';
    if ( file_exists( $autoloader ) ) {
        require_once $autoloader;
        orbitur_main_log('Loaded updater from vendor/autoload.php');
    } elseif ( file_exists( $manual_loader ) ) {
        require_once $manual_loader;
        orbitur_main_log('Loaded updater from updater/plugin-update-checker.php');
    } else {
        orbitur_main_log('Updater not found; continuing without auto-updates.');
    }

    // Try to initialize update checker only if factory class exists
    if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
        try {
            $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                'https://github.com/adipanchal/orbitur-moncomp-integration',
                __FILE__,
                'orbitur-moncomp-integration'
            );
            $updateChecker->setBranch('main');
            if ( defined('GITHUB_UPDATER_TOKEN') && ! empty( GITHUB_UPDATER_TOKEN ) ) {
                $updateChecker->setAuthentication( GITHUB_UPDATER_TOKEN );
            }
            orbitur_main_log('Update checker (v5) initialized.');
        } catch (Throwable $e) {
            orbitur_main_log('Update checker init error: ' . $e->getMessage());
        }
    } elseif ( class_exists('Puc_v4_Factory') ) {
        try {
            $updateChecker = Puc_v4_Factory::buildUpdateChecker(
                'https://github.com/adipanchal/orbitur-moncomp-integration',
                __FILE__,
                'orbitur-moncomp-integration'
            );
            $updateChecker->setBranch('main');
            orbitur_main_log('Update checker (v4) initialized.');
        } catch (Throwable $e) {
            orbitur_main_log('Update checker v4 init error: ' . $e->getMessage());
        }
    } else {
        // no update checker classes present — not a fatal issue
    }
} catch (Throwable $e) {
    orbitur_main_log('Updater require exception: ' . $e->getMessage());
}

/**
 * Load core includes safely
 */
$includes = [
    'inc/logger.php',
    'inc/api.php',
    'inc/parser.php',
    'inc/user-provision.php',
    'inc/shortcodes.php',
    'inc/ajax-handlers.php',
];

foreach ($includes as $inc) {
    $path = ORBITUR_PLUGIN_DIR . $inc;
    if ( file_exists($path) ) {
        try {
            require_once $path;
            orbitur_main_log("Included: {$inc}");
        } catch (Throwable $e) {
            orbitur_main_log("Error including {$inc}: " . $e->getMessage());
        }
    } else {
        orbitur_main_log("Missing include (skipped): {$inc}");
    }
}

/**
 * Activation / Deactivation hooks — safe defaults
 */
register_activation_hook(__FILE__, function(){
    if (! get_option('orbitur_moncomp_endpoint') ) {
        update_option('orbitur_moncomp_endpoint', '');
    }
    orbitur_main_log('Plugin activated.');
});

register_deactivation_hook(__FILE__, function(){
    orbitur_main_log('Plugin deactivated.');
});