<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-register-form" style="max-width:720px;margin:0 auto;">
  <h2 style="text-align:center;color:#33aeda;font-weight:700;">FAÇA O SEU REGISTO DE CONTA</h2>

  <?php if (!empty($_GET['err'])): ?>
    <div class="orbitur-error" style="color:#c00;padding:8px;border:1px solid #f2dede;background:#fff6f6;margin-bottom:12px;">
      Erro: <?php echo esc_html($_GET['err']); ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="orbitur-register-form">
    <input type="hidden" name="action" value="orbitur_register">
    <input type="hidden" name="redirect" value="<?php echo esc_url(site_url('/area-cliente/bem-vindo')); ?>">

    <div style="display:flex;gap:12px;">
      <p style="flex:1;">
        <label>NOME*<br>
          <input type="text" name="first_name" required style="width:100%;padding:10px;border:1px solid #ddd;">
        </label>
      </p>
      <p style="flex:1;">
        <label>APELIDO*<br>
          <input type="text" name="last_name" required style="width:100%;padding:10px;border:1px solid #ddd;">
        </label>
      </p>
    </div>

    <p>
      <label>E-MAIL*<br>
        <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #ddd;">
      </label>
    </p>

    <p>
      <label>TELEFONE*<br>
        <input type="text" name="phone" placeholder="+351 912345678" required style="width:100%;padding:10px;border:1px solid #ddd;">
      </label>
    </p>

    <p>
      <label>MORADA*<br>
        <input type="text" name="address" required style="width:100%;padding:10px;border:1px solid #ddd;">
      </label>
    </p>

    <div style="display:flex;gap:12px;">
      <p style="flex:1;">
        <label>CÓDIGO POSTAL*<br>
          <input type="text" name="postal_code" required style="width:100%;padding:10px;border:1px solid #ddd;">
        </label>
      </p>
      <p style="flex:1;">
        <label>LOCALIDADE*<br>
          <input type="text" name="city" required style="width:100%;padding:10px;border:1px solid #ddd;">
        </label>
      </p>
    </div>

    <p>
      <label>PAÍS*<br>
        <select name="country" required style="width:100%;padding:10px;border:1px solid #ddd;">
          <option value="">Selecionar</option>
          <option value="PT">Portugal</option>
          <option value="ES">Espanha</option>
          <option value="FR">França</option>
          <!-- add more as needed -->
        </select>
      </label>
    </p>

    <div style="display:flex;gap:12px;">
      <p style="flex:1;">
        <label>NACIONALIDADE*<br>
          <select name="nationality" required style="width:100%;padding:10px;border:1px solid #ddd;">
            <option value="">Selecionar</option>
            <option value="PT">Portugal</option>
            <option value="ES">Espanha</option>
          </select>
        </label>
      </p>
      <p style="flex:1;">
        <label>DATA DE NASCIMENTO*<br>
          <input type="date" name="birthdate" required style="width:100%;padding:10px;border:1px solid #ddd;">
        </label>
      </p>
    </div>

    <div style="display:flex;gap:12px;">
      <p style="flex:1;">
        <label>TIPO IDENTIFICAÇÃO*<br>
          <select name="id_type" required style="width:100%;padding:10px;border:1px solid #ddd;">
            <option value="">Selecionar</option>
            <option value="cc">Cartão de Cidadão</option>
            <option value="pass">Passaporte</option>
          </select>
        </label>
      </p>
      <p style="flex:1;">
        <label>NÚMERO IDENTIFICAÇÃO*<br>
          <input type="text" name="id_number" required style="width:100%;padding:10px;border:1px solid #ddd;">
        </label>
      </p>
    </div>

    <p>
      <label>NÚMERO DE IDENTIFICAÇÃO FISCAL (NIF)<br>
        <input type="text" name="nif" style="width:100%;padding:10px;border:1px solid #ddd;">
      </label>
    </p>

    <p>
      <label>PALAVRA-PASSE* (mín. 6 caracteres)<br>
        <input type="password" name="password" required minlength="6" style="width:100%;padding:10px;border:1px solid #ddd;">
      </label>
    </p>

    <p>
      <label style="display:flex;gap:8px;align-items:center;">
        <input type="checkbox" name="privacy" value="1" required>
        <span>Consinto o tratamento dos meus dados pessoais de acordo com a <a href="/politica-de-privacidade" target="_blank">Política de Privacidade</a>.</span>
      </label>
    </p>

    <p style="text-align:center;margin-top:18px;">
      <button type="submit" style="background:#39aeda;color:#fff;padding:14px 28px;border:none;border-radius:3px;font-weight:700;">REGISTAR-ME</button>
    </p>
  </form>
</div>

<!-- Small script: optional client validation / nicer UX -->
<script>
(function(){
  var f = document.getElementById('orbitur-register-form');
  if (!f) return;
  f.addEventListener('submit', function(e){
    // simple: ensure privacy checked
    if (!f.privacy.checked) {
      e.preventDefault();
      alert('Por favor aceite a Política de Privacidade.');
      return false;
    }
  });
})();
</script>