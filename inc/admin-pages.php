<?php
if (!defined('ABSPATH'))
    exit;

add_action('admin_menu', function () {
    add_options_page('Orbitur MonCompte', 'Orbitur MonCompte', 'manage_options', 'orbitur-moncomp', 'orbitur_moncomp_settings_page');
});

function orbitur_moncomp_settings_page()
{
    if (!current_user_can('manage_options'))
        return;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orbitur_moncomp_nonce'])) {
        if (!wp_verify_nonce($_POST['orbitur_moncomp_nonce'], 'orbitur_moncomp_save')) {
            echo '<div class="notice notice-error"><p>Nonce failed.</p></div>';
        } else {
            update_option('orbitur_moncomp_endpoint', sanitize_text_field($_POST['endpoint'] ?? ''));
            update_option('orbitur_moncomp_email', sanitize_email($_POST['email'] ?? ''));
            update_option('orbitur_moncomp_password', sanitize_text_field($_POST['password'] ?? ''));
            echo '<div class="notice notice-success"><p>Saved.</p></div>';
        }
    }
    $endpoint = esc_attr(get_option('orbitur_moncomp_endpoint', ''));
    $email = esc_attr(get_option('orbitur_moncomp_email', ''));
    $pwd = esc_attr(get_option('orbitur_moncomp_password', ''));
    ?>
    <div class="wrap">
        <h1>Orbitur MonCompte settings</h1>
        <form method="post">
            <?php wp_nonce_field('orbitur_moncomp_save', 'orbitur_moncomp_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Endpoint (no ?wsdl)</th>
                    <td><input name="endpoint" value="<?php echo $endpoint; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Test account email</th>
                    <td><input name="email" value="<?php echo $email; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Test account password</th>
                    <td><input name="password" value="<?php echo $pwd; ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Save'); ?>
        </form>

        <h2>AJAX test URL</h2>
        <p>Use this URL to test login+bookings (server must be whitelisted):</p>
        <code><?php echo admin_url('admin-ajax.php?action=orbitur_wsdl_test'); ?></code>
    </div>
    <?php
}

/* Admin AJAX test for debugging */
add_action('wp_ajax_orbitur_wsdl_test', 'orbitur_ajax_wsdl_test');
function orbitur_ajax_wsdl_test()
{
    if (!current_user_can('manage_options'))
        wp_die('no');
    $cfg = orbitur_get_config();
    header('Content-Type: text/plain');
    echo "Testing login for " . $cfg['email'] . "\n\n";
    $res = orbitur_login($cfg['email'], $cfg['password']);
    if (is_wp_error($res)) {
        echo "ERROR: " . $res->get_error_message() . "\n";
        if ($res->get_error_data())
            print_r($res->get_error_data());
        wp_die();
    }
    $idSession = $res;
    echo "Login successful, idSession = {$idSession}\n\n";
    echo "Fetching booking list...\n\n";
    $raw = orbitur_getBookingList_raw($idSession);
    if (is_wp_error($raw)) {
        echo "ERROR fetching bookings: " . $raw->get_error_message() . "\n";
        if ($raw->get_error_data())
            print_r($raw->get_error_data());
        wp_die();
    }
    echo "HTTP code: 200\n";
    echo "Response snippet:\n" . substr($raw, 0, 4000) . "\n";
    wp_die();
}