<?php if (!defined('ABSPATH'))
  exit; ?>
<div class="orbitur-register-wrap">
  <form id="orbitur-register-form" class="auth-form auth-form--register">
    <div class="auth-form__fields">
      <!-- Name & Surname Row -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label">NAME*</label>
          <input type="text" name="first_name" class="form-group__input" placeholder="Nome" required />
        </div>
        <div class="form-group">
          <label class="form-group__label">APELIDO*</label>
          <input type="text" name="last_name" class="form-group__input" placeholder="Apelido" required />
        </div>
      </div>

      <!-- Email Field -->
      <div class="form-group">
        <label class="form-group__label">E-MAIL*</label>
        <input type="email" name="email" class="form-group__input" placeholder="E-mail" required />
      </div>

      <!-- Phone Field with Country Code -->
      <div class="form-group">
        <label class="form-group__label">TELEFONE*</label>
        <div class="phone-input">
          <input type="tel" name="phone" class="form-group__input phone-input__field" placeholder="Telemóvel"
            required />
        </div>
      </div>

      <!-- Address Field -->
      <div class="form-group">
        <label class="form-group__label">MORADA*</label>
        <input type="text" name="address" class="form-group__input" placeholder="Morada" required />
      </div>

      <!-- Postal Code & City Row -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label">CÓDIGO POSTAL*</label>
          <input type="text" name="postcode" class="form-group__input" placeholder="0000-000" required />
        </div>
        <div class="form-group">
          <label class="form-group__label">LOCALIDADE*</label>
          <input type="text" name="city" class="form-group__input" placeholder="Localidade" required />
        </div>
      </div>

      <!-- Country Field -->
      <div class="form-group select-group">
        <label class="form-group__label">PAÍS*</label>
        <select id="pais-dropdown" name="country" class="form-group__select" required>
          <option value="" disabled selected>Selecionar</option>
          <option value="PT">Portugal</option>
          <option value="ES">Espanha (Spain)</option>
          <option value="BR">Brasil (Brazil)</option>
          <option value="US">Estados Unidos (United States)</option>
          <option value="AF">Afghanistan</option>
          <option value="AL">Albania</option>
          <option value="DZ">Algeria</option>
          <option value="AO">Angola</option>
          <option value="AR">Argentina</option>
        </select>
      </div>

      <!-- Nationality & Date of Birth Row -->
      <div class="form-row">
        <div class="form-group select-group">
          <label class="form-group__label">NACIONALIDADE*</label>
          <select name="nationality" class="form-group__select" required>
            <option value="" disabled selected>Selecionar</option>
            <option value="Portuguese">Portuguese</option>
            <option value="Spanish">Spanish</option>
            <option value="French">French</option>
            <option value="British">British</option>
            <!-- Add more nationalities as needed -->
          </select>
        </div>
        <div class="form-group">
          <label class="form-group__label">DATA DE NASCIMENTO*</label>
          <input type="date" name="dob" class="form-group__input" required />
        </div>
      </div>

      <!-- ID Type & ID Number Row -->
      <div class="form-row">
        <div class="form-group select-group">
          <label class="form-group__label">TIPO IDENTIFICAÇÃO*</label>
          <select name="id_type" class="form-group__select" required>
            <option value="" disabled selected>Selecionar</option>
            <option value="cc">Cartão de Cidadão</option>
            <option value="passport">Passaporte</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-group__label">NÚMERO IDENTIFICAÇÃO*</label>
          <input type="text" name="id_number" class="form-group__input" placeholder="Número Identificação" required />
        </div>
      </div>

      <!-- Tax ID Field -->
      <div class="form-group">
        <label class="form-group__label">NÚMERO DE IDENTIFICAÇÃO FISCAL (NIF)</label>
        <input type="text" name="nif" class="form-group__input" placeholder="000000000" />
      </div>

      <!-- Privacy Policy Checkbox -->
      <div class="checkbox-label checkbox-label--with-link">
        <input type="checkbox" class="checkbox-label__input" required />
        <label class="checkbox-label__text">
          Consinto o tratamento dos meus dados pessoais de acordo com a
          <a href="#" class="checkbox-label__link">Política de Privacidade</a>.
        </label>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn--primary btn--full-width">
        REGISTAR-ME
      </button>
    </div>
  </form>
</div>