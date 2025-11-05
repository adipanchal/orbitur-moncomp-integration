<?php
if (!defined('ABSPATH')) exit;
$err = $_GET['err'] ?? '';
$msg = '';
if ($err === 'invalid') $msg = 'E-mail ou palavra-passe incorretos.';
elseif ($err === 'missing') $msg = 'Por favor preencha todos os campos.';
elseif ($err === 'nonce') $msg = 'Erro de segurança (nonce).';
elseif ($err === 'exists') $msg = 'Já existe uma conta com este e-mail. Inicie sessão.';
?>
<?php if ($msg): ?>
  <div class="orbitur-alert orbitur-alert-error"><?php echo esc_html($msg); ?></div>
<?php endif; ?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="orbitur-login-form">
  <?php wp_nonce_field('orbitur_login_action','orbitur_login_nonce'); ?>
  <input type="hidden" name="action" value="orbitur_login_submit">
  <p><label>Email<br><input type="email" name="email" required></label></p>
  <p><label>Palavra-passe<br><input type="password" name="password" required></label></p>
  <p><button type="submit" class="orbitur-btn">Entrar</button></p>
</form>