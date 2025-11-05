<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-register-form" style="max-width:720px;margin:0 auto;">
  <h2>Registo de Conta</h2>
  <?php if (!empty($_GET['err'])): ?>
    <div style="color:#c00">Erro: <?php echo esc_html($_GET['err']); ?></div>
  <?php endif; ?>
  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="orbitur_register">
    <input type="hidden" name="redirect" value="<?php echo esc_url(site_url('/area-cliente/bem-vindo')); ?>">

    <p><label>Nome<br><input type="text" name="first_name" required style="width:100%"></label></p>
    <p><label>Apelido<br><input type="text" name="last_name" required style="width:100%"></label></p>
    <p><label>E-mail<br><input type="email" name="email" required style="width:100%"></label></p>
    <p><label>Palavra-Passe<br><input type="password" name="password" required minlength="6" style="width:100%"></label></p>
    <p><button type="submit">REGISTAR</button></p>
  </form>
</div>