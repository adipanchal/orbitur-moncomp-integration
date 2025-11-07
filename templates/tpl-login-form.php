<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-login-wrap">
  <h2>FAÇA O SEU LOGIN</h2>
  <form id="orbitur-login-form">
    <p><label>E-MAIL*<br><input type="email" name="email" required></label></p>
    <p><label>PALAVRA-PASSE*<br><input type="password" name="password" required></label></p>
    <p><label><input type="checkbox" name="remember"> Lembrar-me</label></p>
    <p><button type="submit" class="button">LOGIN</button></p>
    <div class="orbitur-login-result" aria-live="polite"></div>
  </form>
</div>