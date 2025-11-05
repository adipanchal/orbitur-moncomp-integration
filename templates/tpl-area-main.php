<?php if (!defined('ABSPATH')) exit;
$user = wp_get_current_user();
?>
<div class="orbitur-area-main" style="max-width:900px;margin:0 auto;">
  <h1>Bem-vindo, <?php echo esc_html($user->display_name ?: $user->user_email); ?></h1>
  <p>Na sua área cliente poderá ver as reservas e gerir a sua conta.</p>

  <p>
    <a href="<?php echo esc_url(site_url('/area-cliente/')); ?>">Perfil</a>
    <form style="display:inline" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="orbitur_logout">
      <button type="submit">Sair</button>
    </form>
  </p>

  <h2>Minhas Reservas</h2>
  <?php echo do_shortcode('[orbitur_bookings]'); ?>
</div>