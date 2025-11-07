<?php if (!defined('ABSPATH')) exit;
$current = wp_get_current_user();
?>
<div class="orbitur-area">
  <h1>BEM-VINDO, <?php echo esc_html($current->display_name ?: $current->user_login); ?></h1>
  <p>Na sua área cliente poderá ver as reservas, perfil e OCC.</p>

  <h2>MINHAS RESERVAS</h2>
  <?php echo do_shortcode('[orbitur_bookings]'); ?>
</div>