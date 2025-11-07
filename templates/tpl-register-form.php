<?php if (!defined('ABSPATH')) exit; ?>
<div class="orbitur-register-wrap">
  <h2>FAÇA O SEU REGISTO DE CONTA</h2>
  <form id="orbitur-register-form">
    <div class="row">
      <div><label>NOME*<input name="first_name" required></label></div>
      <div><label>APELIDO*<input name="last_name" required></label></div>
    </div>

    <p><label>E-MAIL*<input type="email" name="email" required></label></p>
    <p><label>TELEFONE*<input name="phone" placeholder="+351..."></label></p>
    <p><label>MORADA*<input name="address"></label></p>

    <div class="row">
      <div><label>CÓDIGO POSTAL*<input name="postcode"></label></div>
      <div><label>LOCALIDADE*<input name="city"></label></div>
    </div>

    <p><label>PAÍS*<input name="country" placeholder="Portugal"></label></p>

    <div class="row">
      <div><label>NACIONALIDADE*<input name="nationality"></label></div>
      <div><label>DATA DE NASCIMENTO*<input name="dob" type="date"></label></div>
    </div>

    <div class="row">
      <div><label>TIPO IDENTIFICAÇÃO*<input name="id_type"></label></div>
      <div><label>NÚMERO IDENTIFICAÇÃO*<input name="id_number"></label></div>
    </div>

    <p><label>NÚMERO FISCAL (NIF)<input name="nif"></label></p>

    <p><label><input type="checkbox" required> Consinto o tratamento dos meus dados pessoais</label></p>

    <p><button type="submit" class="button">REGISTAR-ME</button></p>
    <div class="orbitur-register-result" aria-live="polite"></div>
  </form>
</div>