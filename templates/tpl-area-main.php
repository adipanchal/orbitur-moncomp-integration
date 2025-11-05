<?php
if (!defined('ABSPATH')) exit;
$user = wp_get_current_user();
?>
<div class="orbitur-area-main">
  <h1>Bem-vindo, <?php echo esc_html($user->display_name ?: $user->user_email); ?></h1>
  <p>Na sua área cliente poderá ver as reservas, perfil e OCC.</p>
  <p><a class="orbitur-btn" href="<?php echo esc_url( wp_logout_url(site_url('/area-cliente/login')) ); ?>">Sair</a></p>

  <h2>Minhas Reservas</h2>
  <?php echo do_shortcode('[orbitur_bookings]'); ?>
</div>