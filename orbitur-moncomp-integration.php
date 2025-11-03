<?php
/**
 * Plugin Name: Orbitur MonCompte Integration
 * Plugin URI: https://github.com/adipanchal/orbitur-moncomp-integration/
 * Description: Integrates WordPress with Orbitur MonCompte (SOAP) and WebCamp widgets. Login/Register + bookings skeleton.
 * Version: 1.0 Beta
 * Author: Blendd
 */

if (!defined('ABSPATH')) exit;

// Updater
require '/updater/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/adipanchal/orbitur-moncomp-integration',
	__FILE__, //Full path to the main plugin file or functions.php.
	'orbitur-moncomp-integration'
);

$updateChecker->setBranch('main');
// End Updater

define('ORBITUR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ORBITUR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load core files
require_once ORBITUR_PLUGIN_DIR . 'inc/api.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/parser.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/user-provision.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/shortcodes.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/ajax-handlers.php';

// Activation: create any defaults
register_activation_hook(__FILE__, function(){
    if (!get_option('orbitur_moncomp_endpoint')) {
        // leave empty, encourage using WP config constant or admin to set.
        update_option('orbitur_moncomp_endpoint', '');
    }
});

// Deactivation cleanup (non-destructive)
register_deactivation_hook(__FILE__, function(){
    // Do not delete usermeta or options automatically.
});