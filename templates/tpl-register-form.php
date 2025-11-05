<?php
if (!defined('ABSPATH')) exit;
$err = $_GET['err'] ?? '';
$msg = '';
if ($err === 'missing') $msg = 'Por favor preencha todos os campos e aceite os termos.';
elseif ($err === 'createfail') $msg = 'Não foi possível criar a conta.';
elseif ($err === 'soaper') $msg = 'Erro no serviço. Contacte suporte.';
elseif ($err === 'nonce') $msg = 'Erro de segurança (nonce).';
?>
<?php if ($msg): ?>
  <div class="orbitur-alert orbitur-alert-error"><?php echo esc_html($msg); ?></div>
<?php endif; ?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="orbitur-register-form">
  <?php wp_nonce_field('orbitur_register_action','orbitur_register_nonce'); ?>
  <input type="hidden" name="action" value="orbitur_register_submit">
  <p><label>Nome<br><input type="text" name="first_name" required></label></p>
  <p><label>Apelido<br><input type="text" name="last_name" required></label></p>
  <p><label>Email<br><input type="email" name="email" required></label></p>
  <p><label>Palavra-passe<br><input type="password" name="password" required></label></p>
  <p><label><input type="checkbox" name="accept_terms" value="1" required> Aceito os termos</label></p>
  <p><button type="submit" class="orbitur-btn">Registar</button></p>
</form>