<?php
// templates/tpl-login-form.php
if (!defined('ABSPATH'))
  exit;
$action_url = admin_url('admin-post.php');

// Prevent the page being cached by WP/hosting caches so the nonce is always fresh
if (!defined('DONOTCACHEPAGE')) {
  define('DONOTCACHEPAGE', true);
}
// send no-cache headers for good measure
if (function_exists('nocache_headers')) {
  nocache_headers();
}
?>
<form id="orbitur-login-form" class="auth-form auth-form--login" action="<?php echo esc_url($action_url); ?>"
  method="post" autocomplete="on" novalidate>
  <?php wp_nonce_field('orbitur_login_action', 'orbitur_login_nonce'); ?>
  <input type="hidden" name="action" value="orbitur_login" />

  <!-- Form Fields Container -->
  <div class="auth-form__fields">
    <!-- Email Field -->
    <div class="form-group">
      <label for="orbitur_email" class="form-group__label">E-MAIL*</label>
      <input id="orbitur_email" name="email" type="email" class="form-group__input" placeholder="email@gmail.com"
        required autocomplete="email" />
    </div>

    <!-- Password Field -->
    <div class="form-group">
      <label for="orbitur_pw" class="form-group__label">PALAVRA-PASSE*</label>
      <input id="orbitur_pw" name="pw" type="password" class="form-group__input"
        placeholder="Escreva aqui a sua palavra-passe" required autocomplete="current-password" />
    </div>
  </div>

  <!-- Login Options (Remember & Forgot Password) -->
  <div class="auth-form__options">
    <label class="checkbox-label">
      <input type="checkbox" name="remember" value="1" class="checkbox-label__input" />
      <span class="checkbox-label__text">Lembrar-me</span>
    </label>
    <a class="auth-form__link" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Esqueceu-se da palavra-passe?</a>
  </div>

  <!-- Submit Button -->
  <button type="submit" class="btn btn--primary btn--full-width">
    LOGIN
  </button>
</form>