<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-login-form">
  <h2>Área Cliente — Iniciar Sessão</h2>
  <?php if (!empty($_GET['err'])): ?>
    <div class="orbitur-error">Erro: <?php echo esc_html($_GET['err']); ?></div>
  <?php endif; ?>
  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="orbitur_login">
    <p><label>Email<br><input type="email" name="email" required></label></p>
    <p><label>Password<br><input type="password" name="password" required></label></p>
    <p><button type="submit">Entrar</button> &nbsp; <a href="<?php echo esc_url(site_url('/area-cliente/registo-de-conta')); ?>">Registar</a></p>
  </form>
</div>