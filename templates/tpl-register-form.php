<?php if (!defined('ABSPATH'))
  exit; ?>
<div class="orbitur-register-wrap">
  <form id="orbitur-register-form" class="auth-form auth-form--register" novalidate>
    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('orbitur_form_action')); ?>">
    <div class="auth-form__fields">
      <!-- Name & Surname Row -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label">NOME*</label>
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
          <option value="AD">Andorra</option>
          <option value="AE">Emirados Árabes Unidos</option>
          <option value="AF">Afeganistío</option>
          <option value="AG">Antigua e Barbuda</option>
          <option value="AI">Anguila</option>
          <option value="AL">Albânia</option>
          <option value="AM">Arménia</option>
          <option value="AN">Antilhas Holandesas</option>
          <option value="AO">Angola</option>
          <option value="AQ">Antártico</option>
          <option value="AR">Argentina</option>
          <option value="AS">Samoa Americana</option>
          <option value="AT">Austria</option>
          <option value="AU">Austrália</option>
          <option value="AW">Aruba</option>
          <option value="AZ">Azerbeijío</option>
          <option value="BA">Bósnia-Herzegovina</option>
          <option value="BB">Barbados</option>
          <option value="BD">Bangladesh</option>
          <option value="BE">Bélgica</option>
          <option value="BF">Burkina Faso</option>
          <option value="BG">Bulgária</option>
          <option value="BH">Bahrain</option>
          <option value="BI">Burundi</option>
          <option value="BJ">Benin</option>
          <option value="BM">Bermudas</option>
          <option value="BN">Brunei</option>
          <option value="BO">Bolí­via</option>
          <option value="BR">Brasil</option>
          <option value="BS">Bahamas</option>
          <option value="BT">Butío</option>
          <option value="BV">Ilha Bouvet</option>
          <option value="BW">Botswana</option>
          <option value="BY">Belarus</option>
          <option value="BZ">Belize</option>
          <option value="CA">Canadá</option>
          <option value="CC">Ilhas Coco</option>
          <option value="CD">Républica Democrática do Congo</option>
          <option value="CF">República Centro-Africana</option>
          <option value="CG">Congo</option>
          <option value="CH">Suí­ssa</option>
          <option value="CI">Costa do Marfim</option>
          <option value="CK">Ilhas Cook</option>
          <option value="CL">Chile</option>
          <option value="CM">Camarões</option>
          <option value="CN">China</option>
          <option value="CO">Colombia</option>
          <option value="CR">Costa Rica</option>
          <option value="CS">Sérvia e Montenegro</option>
          <option value="CU">Cuba</option>
          <option value="CV">Cabo Verde</option>
          <option value="CX">Ilha do Natal</option>
          <option value="CY">Chipre</option>
          <option value="CZ">República Checa</option>
          <option value="DE">Alemanha</option>
          <option value="DJ">Jibuti</option>
          <option value="DK">Dinamarca</option>
          <option value="DM">Dominica</option>
          <option value="DO">República Dominicana</option>
          <option value="DZ">Algéria</option>
          <option value="EC">Equador</option>
          <option value="EE">Estónia</option>
          <option value="EG">Egipto</option>
          <option value="EH">Sara Ocidental</option>
          <option value="ER">Eritreia</option>
          <option value="ES">Espanha</option>
          <option value="ET">Etiópia</option>
          <option value="FI">Finlândia</option>
          <option value="FJ">Fiji</option>
          <option value="FK">Ilhas Falkland (Ilhas Malvinas)</option>
          <option value="FM">Estados Federados da Micronésia</option>
          <option value="FO">Ilhas Faroé</option>
          <option value="FR">França</option>
          <option value="GA">Gabío</option>
          <option value="GB">Reino Unido</option>
          <option value="GD">Granada</option>
          <option value="GE">Geórgia</option>
          <option value="GF">Guiana Francesa</option>
          <option value="GH">Gana</option>
          <option value="GI">Gibraltar</option>
          <option value="GL">Gronelândia</option>
          <option value="GM">Gâmbia</option>
          <option value="GN">Guiné</option>
          <option value="GP">Guadalupe</option>
          <option value="GQ">Guiné Equatorial</option>
          <option value="GR">Grécia</option>
          <option value="GS">Ilhas Geórgia do Sul e Sandwich do Sul</option>
          <option value="GT">Guatemala</option>
          <option value="GU">Guam</option>
          <option value="GW">Guiné-Bissau</option>
          <option value="GY">Guiana</option>
          <option value="HK">Hong-Kong</option>
          <option value="HM">Ilhas Heard e McDonald</option>
          <option value="HN">Honduras</option>
          <option value="HR">Croácia</option>
          <option value="HT">Haiti</option>
          <option value="HU">Húngria</option>
          <option value="ID">Indonésia</option>
          <option value="IE">República da Irlanda</option>
          <option value="IL">Israel</option>
          <option value="IN">Índia</option>
          <option value="IO">Território Britânico do Oceano Índico</option>
          <option value="IQ">Iraque</option>
          <option value="IR">Irío</option>
          <option value="IS">Islândia</option>
          <option value="IT">Itália</option>
          <option value="JM">Jamaica</option>
          <option value="JO">Jordânia</option>
          <option value="JP">Japío</option>
          <option value="KE">Quénia</option>
          <option value="KG">Quirguistío</option>
          <option value="KH">Camboja</option>
          <option value="KI">Quiribati</option>
          <option value="KM">Comores</option>
          <option value="KN">Sío Cristóvío e Neves</option>
          <option value="KP">Coreia do Norte</option>
          <option value="KR">Coreia do Sul</option>
          <option value="KW">Kuwait</option>
          <option value="KY">Ilhas Caimío</option>
          <option value="KZ">Cazaquistío</option>
          <option value="LA">Laos</option>
          <option value="LB">Lí­bano</option>
          <option value="LC">Santa Lúcia</option>
          <option value="LI">Liechtenstein</option>
          <option value="LK">Sri Lanka</option>
          <option value="LR">Libéria</option>
          <option value="LS">Lesotho</option>
          <option value="LT">Lituânia</option>
          <option value="LU">Luxemburgo</option>
          <option value="LV">Letónia</option>
          <option value="LY">Lí­bia</option>
          <option value="MA">Marroco</option>
          <option value="MC">Mónaco</option>
          <option value="MD">Moldóvia</option>
          <option value="MG">Madagascar</option>
          <option value="MH">Ilhas Marshall</option>
          <option value="MK">Macedónia</option>
          <option value="ML">Mali</option>
          <option value="MM">Myanmar</option>
          <option value="MN">Mongólia</option>
          <option value="MO">Macau</option>
          <option value="MP">Ilhas Marianas</option>
          <option value="MQ">Martinica</option>
          <option value="MR">Mauritânia</option>
          <option value="MS">Monserrate</option>
          <option value="MT">Malta</option>
          <option value="MU">Maurí­cia</option>
          <option value="MV">Maldivas</option>
          <option value="MW">Malawi</option>
          <option value="MX">México</option>
          <option value="MY">Malásia</option>
          <option value="MZ">Moçambique</option>
          <option value="NA">Namí­bia</option>
          <option value="NC">Nova Caledónia</option>
          <option value="NE">Niger</option>
          <option value="NF">Ilha Norfolk</option>
          <option value="NG">Nigéria</option>
          <option value="NI">Nicarágua</option>
          <option value="NL">Holanda</option>
          <option value="NO">Noruega</option>
          <option value="NP">Nepal</option>
          <option value="NR">Nauru</option>
          <option value="NU">Niuê</option>
          <option value="NZ">Nova Zelândia</option>
          <option value="OM">Omí</option>
          <option value="PA">Panamá</option>
          <option value="PE">Peru</option>
          <option value="PF">Polinésia Francesa</option>
          <option value="PG">Papua-Nova Guiné</option>
          <option value="PH">Filipinas</option>
          <option value="PK">Paquistío</option>
          <option value="PL">Polónia</option>
          <option value="PM">Sío Pedro e Miquelío</option>
          <option value="PN">Ilhas Pitcairn</option>
          <option value="PR">Porto Rico</option>
          <option value="PS">Estado da Palestina</option>
          <option value="PT">Portugal</option>
          <option value="PW">Palau</option>
          <option value="PY">Paraguai</option>
          <option value="QA">Catar</option>
          <option value="RE">Reuniío</option>
          <option value="RO">Roménia</option>
          <option value="RS">Sérvia</option>
          <option value="RU">Rússia</option>
          <option value="RW">Ruanda</option>
          <option value="SA">Arábia Saudita</option>
          <option value="SB">Ilhas Salomío</option>
          <option value="SC">Seicheles</option>
          <option value="SD">Sudío</option>
          <option value="SE">Suécia</option>
          <option value="SG">Singapura</option>
          <option value="SH">Santa Helena</option>
          <option value="SI">Eslovénia</option>
          <option value="SK">Eslováquia</option>
          <option value="SL">Serra Leoa</option>
          <option value="SM">San Marino</option>
          <option value="SN">Senegal</option>
          <option value="SO">Somália</option>
          <option value="SR">Suriname</option>
          <option value="ST">Sío Tomé e Prí­ncipe</option>
          <option value="SV">El Salvador</option>
          <option value="SY">Sí­ria</option>
          <option value="SZ">Suazilândia</option>
          <option value="TC">Turcas e Caicos</option>
          <option value="TD">Chade</option>
          <option value="TF">Terras Austrais e Antárticas Francesas</option>
          <option value="TG">Togo</option>
          <option value="TH">Tailândia</option>
          <option value="TJ">Tajiquistío</option>
          <option value="TK">Toquelau</option>
          <option value="TL">Timor-Leste</option>
          <option value="TM">Turquemenistío</option>
          <option value="TN">Tuní­sia</option>
          <option value="TO">Tonga</option>
          <option value="TR">Turquia</option>
          <option value="TT">Trinidad e Tobago</option>
          <option value="TV">Tuvalu</option>
          <option value="TW">Taiwan</option>
          <option value="TZ">Tanzânia</option>
          <option value="UA">Ucrânia</option>
          <option value="UG">Uganda</option>
          <option value="UM">Ilhas Menores Afastadas dos Estados Unidos</option>
          <option value="US">Estados Unidos</option>
          <option value="UY">Uruguai</option>
          <option value="UZ">Uzebequistío</option>
          <option value="VA">Vaticano</option>
          <option value="VC">Sío Vicente e Granadinas</option>
          <option value="VE">Venezuela</option>
          <option value="VG">Ilhas Virgens Britânicas</option>
          <option value="VI">Ilhas Virgens Americanas</option>
          <option value="VN">Vietname</option>
          <option value="VU">Vanuatu</option>
          <option value="WF">Wallis e Futuna</option>
          <option value="WS">Samoa</option>
          <option value="YE">Iémen</option>
          <option value="YT">Maiote</option>
          <option value="YU">Jugoslávia</option>
          <option value="ZA">Africa do Sul</option>
          <option value="ZM">Zâmbia</option>
          <option value="ZW">Zimbabué</option>
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
        <input type="checkbox" name="privacy" class="checkbox-label__input" required />
        <label class="checkbox-label__text">
          Consinto o tratamento dos meus dados pessoais de acordo com a
          <a href="#" class="checkbox-label__link">Política de Privacidade</a>.
        </label>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn--primary">
        <span class="spinner hidden" aria-hidden="true"></span>
        <span class="btn-text">REGISTAR-ME</span>
      </button>
    </div>
    <div class="orbitur-form-msg" aria-live="polite" style="display:none;"></div>
  </form>
</div>