<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-register-form">
  <?php if (!empty($_GET['err'])): ?>
    <div class="orbitur-error">Erro: <?php echo esc_html($_GET['err']); ?></div>
  <?php endif; ?>
  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="orbitur_register">
    <p><label>Nome Completo<br><input type="text" name="name" required></label></p>
    <p><label>Email<br><input type="email" name="email" required></label></p>
    <p><label>Password<br><input type="password" name="password" required></label></p>
    <p><button type="submit">Criar Conta</button></p>
  </form>
</div>