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
<form id="orbitur-login-form" class="auth-form auth-form--login" action="#" method="post" autocomplete="on" novalidate>
  <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('orbitur_form_action')); ?>">
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
    <label class="checkbox-label" for="orbitur_remember">
      <input type="checkbox" id="orbitur_remember" name="remember" value="1" class="checkbox-label__input" />
      <span class="checkbox-label__text">Lembrar-me</span>
    </label>
    <a href="#" class="auth-form__link" id="orbitur-show-forgot">Esqueceu-se da palavra-passe?</a>
  </div>

  <!-- Submit Button -->
  <button type="submit" class="btn btn--primary btn--full-width">
    <span class="spinner hidden" aria-hidden="true"></span>
    <span class="btn-text">LOGIN</span>
  </button>
  <div class="orbitur-form-msg" aria-live="polite" style="display:none;"></div>

</form>

<form id="orbitur-forgot-form" class="auth-form auth-form--forgot hidden">
  <label for="forgot-email" class="form-group__label">Email</label>
  <input id="forgot-email" type="email" name="email" class="form-group__input" placeholder="email" required />
  <div style="margin-top:.5rem;display:flex;gap:.5rem;">
    <button type="submit" class="btn btn--primary">
      <span class="spinner hidden" aria-hidden="true"></span>
      <span class="btn-text">Enviar</span>
    </button>
    <a href="#" id="orbitur-hide-forgot" class="btn">Voltar</a>
  </div>
  <div class="orbitur-form-msg" aria-live="polite" style="display:none;"></div>
</form>