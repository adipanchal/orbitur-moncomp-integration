<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-login-form" style="max-width:700px;margin:0 auto;">
  <?php if (!empty($_GET['err'])): ?>
    <div style="color:#c00">Erro: <?php echo esc_html($_GET['err']); ?></div>
  <?php endif; ?>
  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="orbitur_login">
    <p><label>E-mail<br><input type="email" name="email" required style="width:100%"></label></p>
    <p><label>Palavra-Passe<br><input type="password" name="password" required style="width:100%"></label></p>
    <p><button type="submit">LOGIN</button> &nbsp; <a href="<?php echo esc_url(site_url('/area-cliente/registo-de-conta')); ?>">Registar</a></p>
  </form>
</div>