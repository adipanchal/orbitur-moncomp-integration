<?php
if (!defined('ABSPATH')) exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/api.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/parser.php';
require_once ORBITUR_PLUGIN_DIR . 'inc/user-provision.php';

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
        wp_safe_redirect(site_url('/area-cliente/'));
        exit;
    }
    ob_start();
    include ORBITUR_PLUGIN_DIR . 'templates/tpl-area-main.php';
    return ob_get_clean();
});

/* Shortcode: bookings (robust) */
add_shortcode('orbitur_bookings', function($atts){
    if (!is_user_logged_in()) return '<p>Faça login para ver as suas reservas.</p>';

    $user_id = get_current_user_id();
    $tkey = 'orbitur_bookings_'.$user_id;

    if (false !== ($cached = get_transient($tkey))) {
        $lists = $cached;
    } else {
        $idSession = get_user_meta($user_id, 'moncomp_idSession', true);

        if (empty($idSession)) {
            return '<p>Não existe sessão MonCompte associada à sua conta. Por favor <a href="'.esc_url(site_url('/area-cliente/')).'">inicie sessão</a>.</p>';
        }

        $raw = orbitur_getBookingList_raw($idSession);
        if (is_wp_error($raw)) {
            $msg = $raw->get_error_message();
            return '<div class="orbitur-alert orbitur-alert-error">Não foi possível obter as reservas: '.esc_html($msg).'. Por favor <a href="'.esc_url(site_url('/area-cliente/')).'">inicie sessão</a> novamente.</div>';
        }

        $parsed = orbitur_parse_booking_xml_string($raw);
        if (is_wp_error($parsed)) {
            return '<div class="orbitur-alert orbitur-alert-error">Erro ao interpretar os dados de reservas.</div>';
        }

        $lists = orbitur_split_bookings_list($parsed);
        set_transient($tkey, $lists, 10 * MINUTE_IN_SECONDS);
    }

    ob_start();
    ?>
    <div class="orbitur-bookings">
      <h3>Próximas</h3>
      <?php if (empty($lists['upcoming'])): ?>
        <p>Não há estadias próximas.</p>
      <?php else: ?>
        <ul class="orbitur-bookings-upcoming">
        <?php foreach($lists['upcoming'] as $b): ?>
          <li class="orbitur-booking-item">
            <strong><?php echo esc_html($b['site'] ?? ''); ?></strong>
            <div><?php echo esc_html($b['lodging'] ?? 'Alojamento'); ?></div>
            <div><?php echo esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))); ?> → <?php echo esc_html(date_i18n('d/m/Y', strtotime($b['end'] ?? ''))); ?></div>
            <div>Preço: <?php echo esc_html($b['price'] ?? ''); ?> &nbsp; Pessoas: <?php echo esc_html($b['nbPers'] ?? ''); ?></div>
            <?php if (!empty($b['url'])): ?>
              <div><a class="orbitur-btn" href="<?php echo esc_url($b['url']); ?>" target="_blank" rel="noopener">Gerir / Pagar</a></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <h3>Anteriores</h3>
      <?php if (empty($lists['past'])): ?>
        <p>Não há estadias anteriores.</p>
      <?php else: ?>
        <ul class="orbitur-bookings-past">
        <?php foreach($lists['past'] as $b): ?>
          <li class="orbitur-booking-item">
            <strong><?php echo esc_html($b['site'] ?? ''); ?></strong>
            <div><?php echo esc_html($b['lodging'] ?? 'Alojamento'); ?></div>
            <div><?php echo esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))); ?> → <?php echo esc_html(date_i18n('d/m/Y', strtotime($b['end'] ?? ''))); ?></div>
            <div>Preço: <?php echo esc_html($b['price'] ?? ''); ?></div>
            <?php if (!empty($b['url'])): ?>
              <div><a class="orbitur-btn" href="<?php echo esc_url($b['url']); ?>" target="_blank" rel="noopener">Detalhes</a></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});