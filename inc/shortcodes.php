<?php
if (!defined('ABSPATH')) exit;

add_shortcode('orbitur_login_form', function($atts){
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-login-form.php';
    return ob_get_clean();
});

add_shortcode('orbitur_register_form', function($atts){
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-register-form.php';
    return ob_get_clean();
});

add_shortcode('orbitur_area_main', function($atts){
    if (!is_user_logged_in()) {
        wp_safe_redirect(site_url('/area-cliente/'));
        exit;
    }
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-area-main.php';
    return ob_get_clean();
});

add_shortcode('orbitur_bookings', function($atts){
    if (!is_user_logged_in()) return '<p>Faça login para ver as suas reservas.</p>';
    $uid = get_current_user_id();
    $tkey = 'orbitur_bookings_'.$uid;
    if (false !== ($cached = get_transient($tkey))) {
        $lists = $cached;
    } else {
        $idSession = get_user_meta($uid,'moncomp_idSession',true);
        if (empty($idSession)) return '<p>Sem sessão MonCompte. Por favor inicie sessão.</p>';
        $raw = orbitur_getBookingList_raw($idSession);
        if (is_wp_error($raw)) return '<p>Erro ao obter reservas: '.esc_html($raw->get_error_message()).'</p>';
        $parsed = orbitur_parse_booking_xml_string($raw);
        if (is_wp_error($parsed)) return '<p>Erro ao interpretar reservas.</p>';
        $lists = orbitur_split_bookings_list($parsed);
        set_transient($tkey,$lists,10*MINUTE_IN_SECONDS);
    }

    ob_start();
    ?>
    <div class="orbitur-bookings">
      <h3>Próximas</h3>
      <?php if (empty($lists['upcoming'])): ?>
        <p>Não há estadias próximas.</p>
      <?php else: ?>
        <ul>
        <?php foreach($lists['upcoming'] as $b): ?>
          <li>
            <strong><?php echo esc_html($b['site'] ?? ''); ?></strong>
            — <?php echo esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))); ?>
            <?php if (!empty($b['url'])): ?>
              — <a href="<?php echo esc_url($b['url']); ?>" target="_blank" rel="noopener">Gerir</a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <h3>Anteriores</h3>
      <?php if (empty($lists['past'])): ?>
        <p>Não há estadias anteriores.</p>
      <?php else: ?>
        <ul>
        <?php foreach($lists['past'] as $b): ?>
          <li>
            <strong><?php echo esc_html($b['site'] ?? ''); ?></strong>
            — <?php echo esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))); ?>
            <?php if (!empty($b['url'])): ?>
              — <a href="<?php echo esc_url($b['url']); ?>" target="_blank" rel="noopener">Detalhes</a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});