<?php
if (!defined('ABSPATH')) exit;

/* Shortcode: login form (page) */
add_shortcode('orbitur_login_form', function($atts){
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-login-form.php';
    return ob_get_clean();
});

/* Shortcode: register form (page) */
add_shortcode('orbitur_register_form', function($atts){
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-register-form.php';
    return ob_get_clean();
});

/* Shortcode: area main (logged-in landing) */
add_shortcode('orbitur_area_main', function($atts){
    if (!is_user_logged_in()) {
        wp_safe_redirect(site_url('/area-cliente/login'));
        exit;
    }
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-area-main.php';
    return ob_get_clean();
});

/* Shortcode: bookings (light) - uses cached transient or admin creds */
add_shortcode('orbitur_bookings', function($atts){
    if (!is_user_logged_in()) return '<p>Faça login para ver as suas reservas.</p>';
    $user_id = get_current_user_id();
    $tkey = 'orbitur_bookings_'.$user_id;
    if (false !== ($cached = get_transient($tkey))) {
        $lists = $cached;
    } else {
        $idSession = get_user_meta($user_id,'moncomp_idSession', true);
        if (!$idSession) {
            // fallback: use admin test creds to fetch (staging only)
            $cfg = orbitur_get_config();
            if (empty($cfg['email']) || empty($cfg['password'])) return '<p>Sem sessão MonCompte. Por favor inicie sessão.</p>';
            $login = orbitur_login($cfg['email'], $cfg['password']);
            if (is_wp_error($login)) return '<p>Erro ao autenticar com MonCompte.</p>';
            $idSession = $login;
        }
        $raw = orbitur_getBookingList_raw($idSession);
        if (is_wp_error($raw)) return '<p>Erro ao obter reservas.</p>';
        $all = orbitur_parse_booking_xml_string($raw);
        $lists = orbitur_split_bookings_list($all);
        set_transient($tkey, $lists, 10 * MINUTE_IN_SECONDS);
    }
    // render simple list
    ob_start();
    ?>
    <div class="orbitur-bookings">
      <h3>Próximas</h3>
      <?php if (empty($lists['upcoming'])): ?><p>Não há estadias próximas.</p><?php else: ?>
        <ul>
        <?php foreach($lists['upcoming'] as $b): ?>
          <li><?php echo esc_html($b['site']); ?> — <?php echo date('d/m/Y', strtotime($b['begin'])); ?> → <?php echo date('d/m/Y', strtotime($b['end'])); ?> — <a href="<?php echo esc_url($b['url']); ?>" target="_blank">Gerir</a></li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <h3>Anteriores</h3>
      <?php if (empty($lists['past'])): ?><p>Não há estadias anteriores.</p><?php else: ?>
        <ul>
        <?php foreach($lists['past'] as $b): ?>
          <li><?php echo esc_html($b['site']); ?> — <?php echo date('d/m/Y', strtotime($b['begin'])); ?> → <?php echo date('d/m/Y', strtotime($b['end'])); ?> — <a href="<?php echo esc_url($b['url']); ?>" target="_blank">Detalhes</a></li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});