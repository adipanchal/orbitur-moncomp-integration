<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-register-form" style="max-width:720px;margin:0 auto;font-family:Arial,Helvetica,sans-serif">
  <h2 style="text-align:center;color:#39aeda;font-weight:700;margin-bottom:28px;">FAÇA O SEU REGISTO DE CONTA</h2>

  <?php if (!empty($_GET['err'])): ?>
    <div style="color:#c00;padding:8px;border:1px solid #f2dede;background:#fff6f6;margin-bottom:12px;">
      Erro: <?php echo esc_html($_GET['err']); ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="orbitur-register-form" style="gap:12px">
    <input type="hidden" name="action" value="orbitur_register">
    <input type="hidden" name="redirect" value="<?php echo esc_url(site_url('/area-cliente/bem-vindo')); ?>">

    <div style="display:flex;gap:12px;margin-bottom:12px;">
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">NOME*</label><br>
        <input type="text" name="first_name" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
      </p>
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">APELIDO*</label><br>
        <input type="text" name="last_name" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
      </p>
    </div>

    <p style="margin-bottom:12px;">
      <label style="font-size:13px;color:#6aa8bd;">E-MAIL*</label><br>
      <input type="email" name="email" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
    </p>

    <p style="margin-bottom:12px;">
      <label style="font-size:13px;color:#6aa8bd;">TELEFONE*</label><br>
      <input type="text" name="phone" placeholder="+351 912345678" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
    </p>

    <p style="margin-bottom:12px;">
      <label style="font-size:13px;color:#6aa8bd;">MORADA*</label><br>
      <input type="text" name="address" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
    </p>

    <div style="display:flex;gap:12px;margin-bottom:12px;">
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">CÓDIGO POSTAL*</label><br>
        <input type="text" name="postal_code" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
      </p>
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">LOCALIDADE*</label><br>
        <input type="text" name="city" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
      </p>
    </div>

    <p style="margin-bottom:12px;">
      <label style="font-size:13px;color:#6aa8bd;">PAÍS*</label><br>
      <select name="country" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
        <option value="">Selecionar</option>
        <option value="PT">Portugal</option>
        <option value="ES">Espanha</option>
        <option value="FR">França</option>
        <!-- add more countries as needed -->
      </select>
    </p>

    <div style="display:flex;gap:12px;margin-bottom:12px;">
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">NACIONALIDADE*</label><br>
        <select name="nationality" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
          <option value="">Selecionar</option>
          <option value="PT">Portugal</option>
          <option value="ES">Espanha</option>
        </select>
      </p>
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">DATA DE NASCIMENTO*</label><br>
        <input type="date" name="birthdate" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
      </p>
    </div>

    <div style="display:flex;gap:12px;margin-bottom:12px;">
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">TIPO IDENTIFICAÇÃO*</label><br>
        <select name="id_type" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
          <option value="">Selecionar</option>
          <option value="cc">Cartão de Cidadão</option>
          <option value="pass">Passaporte</option>
          <option value="bi">Bilhete Identidade</option>
        </select>
      </p>
      <p style="flex:1;margin:0;">
        <label style="font-size:13px;color:#6aa8bd;">NÚMERO IDENTIFICAÇÃO*</label><br>
        <input type="text" name="id_number" required style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
      </p>
    </div>

    <p style="margin-bottom:12px;">
      <label style="font-size:13px;color:#6aa8bd;">NÚMERO DE IDENTIFICAÇÃO FISCAL (NIF)</label><br>
      <input type="text" name="nif" placeholder="000000000" style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
    </p>

    <p style="margin-bottom:12px;">
      <label style="font-size:13px;color:#6aa8bd;">PALAVRA-PASSE*</label><br>
      <input type="password" name="password" required minlength="6" style="width:100%;padding:12px;border:1px solid #e6e6e6;border-radius:2px;">
    </p>

    <p style="margin-bottom:18px;">
      <label style="display:flex;gap:8px;align-items:center;">
        <input type="checkbox" name="privacy" value="1" required style="margin-right:8px;">
        <span>Consinto o tratamento dos meus dados pessoais de acordo com a <a href="/politica-de-privacidade" target="_blank">Política de Privacidade</a>.</span>
      </label>
    </p>

    <p style="text-align:center;margin-top:8px;">
      <button type="submit" style="background:#39aeda;color:#fff;padding:14px 28px;border:none;border-radius:3px;font-weight:700;width:100%;">REGISTAR-ME</button>
    </p>
  </form>
</div>