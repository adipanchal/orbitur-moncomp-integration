<?php if (!defined('ABSPATH'))
  exit; ?>
<div class="orbitur-register-wrap">
  <form id="orbitur-register-form" class="auth-form auth-form--register" novalidate>
    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('orbitur_form_action')); ?>">
    <div class="auth-form__fields">
      <!-- Name & Surname Row -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label" for="reg_first_name">NOME*</label>
          <input type="text" id="reg_first_name" name="first_name" class="form-group__input" placeholder="Nome"
            required />
        </div>
        <div class="form-group">
          <label class="form-group__label" for="reg_last_name">APELIDO*</label>
          <input type="text" id="reg_last_name" name="last_name" class="form-group__input" placeholder="Apelido"
            required />
        </div>
      </div>

      <!-- Civility Field -->
      <div class="form-group select-group">
        <label class="form-group__label" for="reg_civility">Tratamento*</label>
        <select id="reg_civility" name="civility" class="form-group__select" required>
          <option value="">Selecionar</option>
          <option value="Mr.">Sr.</option>
          <option value="Ms.">Sra.</option>
          <option value="Miss">Srta.</option>
        </select>
      </div>
      <!-- Email Field -->
      <div class="form-group">
        <label class="form-group__label" for="reg_email">E-MAIL*</label>
        <input type="email" id="reg_email" name="email" class="form-group__input" placeholder="E-mail" required />
      </div>

      <!-- Phone Field with Country Code -->
      <div class="form-group">
        <label class="form-group__label">TELEFONE*</label>
        <input type="tel" name="phone" class="form-group__input" placeholder="Telemóvel" required />
      </div>

      <!-- Address Field -->
      <div class="form-group">
        <label class="form-group__label" for="reg_address">MORADA*</label>
        <input type="text" id="reg_address" name="address" class="form-group__input" placeholder="Morada" required />
      </div>

      <!-- Postal Code & City Row -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label" for="reg_postcode">CÓDIGO POSTAL*</label>
          <input type="text" id="reg_postcode" name="postcode" class="form-group__input" placeholder="0000-000"
            required />
        </div>
        <div class="form-group">
          <label class="form-group__label" for="reg_city">LOCALIDADE*</label>
          <input type="text" id="reg_city" name="city" class="form-group__input" placeholder="Localidade" required />
        </div>
      </div>

      <!-- Country Field -->
      <div class="form-group select-group">
        <label class="form-group__label" for="pais-dropdown">PAÍS*</label>
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
          <label class="form-group__label" for="reg_nationality">NACIONALIDADE*</label>
          <select id="reg_nationality" name="nationality" class="form-group__select" required>
            <option value="1" selected>NOT ASSIGNED</option>
            <option value="2">PORTUGAL</option>
            <option value="3">SPAIN</option>
            <option value="4">GERMANY</option>
            <option value="5">AUSTRIA</option>
            <option value="6">BELGIUM</option>
            <option value="7">BOSNIA</option>
            <option value="8">BULGARIA</option>
            <option value="9">CANADA</option>
            <option value="10">CROATIA</option>
            <option value="11">DENMARK</option>
            <option value="12">UNITED STATES</option>
            <option value="13">FINLAND</option>
            <option value="14">FRANCE</option>
            <option value="15">UNITED KINGDOM</option>
            <option value="16">GREECE</option>
            <option value="17">NETHERLANDS</option>
            <option value="18">HUNGARY</option>
            <option value="19">ITALY</option>
            <option value="20">IRELAND</option>
            <option value="21">LUXEMBOURG</option>
            <option value="22">NORWAY</option>
            <option value="23">POLAND</option>
            <option value="24">CZECH REPUBLIC</option>
            <option value="25">UGANDA</option>
            <option value="26">ROMANIA</option>
            <option value="27">SWEDEN</option>
            <option value="28">SWITZERLAND</option>
            <option value="29">MOROCCO</option>
            <option value="30">SLOVAKIA</option>
            <option value="31">ICELAND</option>
            <option value="32">LIECHTENSTEIN</option>
            <option value="33">TÜRKIYE</option>
            <option value="34">USBEQUISTAO</option>
            <option value="35">ARGENTINA</option>
            <option value="36">BRAZIL</option>
            <option value="37">MEXICO</option>
            <option value="38">VENEZUELA</option>
            <option value="39">ZAMBIA</option>
            <option value="40">JAPAN</option>
            <option value="41">ZIMBABWE</option>
            <option value="42">AUSTRALIA</option>
            <option value="43">NEW ZEALAND</option>
            <option value="44">TUVALU</option>
            <option value="45">PERU</option>
            <option value="46">RUSSIA</option>
            <option value="47">ISRAEL</option>
            <option value="48">COLOMBIA</option>
            <option value="49">BOLIVIA</option>
            <option value="50">URUGUAY</option>
            <option value="51">CHINA</option>
            <option value="52">INDIA</option>
            <option value="53">LITHUANIA</option>
            <option value="54">LATVIA</option>
            <option value="55">ALGERIA</option>
            <option value="56">TUNISIA</option>
            <option value="57">EGYPT</option>
            <option value="58">PARAGUAY</option>
            <option value="59">HONDURAS</option>
            <option value="60">EL SALVADOR</option>
            <option value="61">CUBA</option>
            <option value="62">PUERTO RICO</option>
            <option value="63">PHILIPPINES</option>
            <option value="64">AFGHANISTAN</option>
            <option value="65">ALBANIA</option>
            <option value="66">ANDORRA</option>
            <option value="67">ARMENIA</option>
            <option value="68">BELARUS</option>
            <option value="69">CAMEROON</option>
            <option value="70">CYPRUS</option>
            <option value="71">CHILE</option>
            <option value="72">MOZAMBIQUE</option>
            <option value="73">COSTA RICA</option>
            <option value="74">ECUADOR</option>
            <option value="75">SLOVENIA</option>
            <option value="76">ESTONIA</option>
            <option value="77">CAPE VERDE</option>
            <option value="78">SAO TOME</option>
            <option value="79">GUINE</option>
            <option value="80">EQUATORIAL GUINEA</option>
            <option value="81">HAITI</option>
            <option value="82">JAMAICA</option>
            <option value="83">JORDAN</option>
            <option value="84">MACEDONIA</option>
            <option value="85">MALTA</option>
            <option value="86">ANGOLA</option>
            <option value="87">PAKISTAN</option>
            <option value="88">PANAMA</option>
            <option value="89">SYRIA</option>
            <option value="90">SERBIA AND MONTENEGRO</option>
            <option value="91">SOUTH AFRICA</option>
            <option value="92">THAILAND</option>
            <option value="93">TAIWAN</option>
            <option value="94">UKRAINE</option>
            <option value="95">VIETNAM</option>
            <option value="96">SINGAPORE</option>
            <option value="97">MONACO</option>
            <option value="98">SAN MARINO</option>
            <option value="99">FAROE ISLANDS</option>
            <option value="100">GIBRALTAR</option>
            <option value="101">ANTIGUA AND BARBUDA</option>
            <option value="102">DUTCH ANTILLES</option>
            <option value="103">SAUDI ARABIA</option>
            <option value="104">AZERBEIJAO</option>
            <option value="105">BAHAMAS</option>
            <option value="106">BANGLADESH</option>
            <option value="107">BARBADOS</option>
            <option value="108">BAREM</option>
            <option value="109">BELIZE</option>
            <option value="110">BERMUDA</option>
            <option value="111">BOTSWANA</option>
            <option value="112">BURKINA FASO</option>
            <option value="113">BURUNDI</option>
            <option value="114">BUTAA</option>
            <option value="115">CAMBODIA</option>
            <option value="116">QATAR</option>
            <option value="117">KAZAKHSTAN</option>
            <option value="118">CENTRAL AFRICAN REPUBLIC</option>
            <option value="119">CHAD</option>
            <option value="120">CONGO</option>
            <option value="121">CONGO (DEMOCRATIC REPUBLIC)</option>
            <option value="122">KOREA (PEOPLE'S DEMOCRATIC REPUBLIC)</option>
            <option value="123">SOUTH KOREA</option>
            <option value="124">IVORY COAST</option>
            <option value="125">DOMINICA</option>
            <option value="126">UNITED ARAB EMIRATES</option>
            <option value="127">ETHIOPIA</option>
            <option value="128">GABON</option>
            <option value="129">GAMBIA</option>
            <option value="130">GHANA</option>
            <option value="131">GEORGIA</option>
            <option value="132">GRENADE</option>
            <option value="133">GREENLAND</option>
            <option value="134">GUADALUPE</option>
            <option value="135">GUATEMALA</option>
            <option value="136">GUIANA</option>
            <option value="137">FRENCH GUIANA</option>
            <option value="138">GUINEA-BISSAU</option>
            <option value="139">HONG KONG</option>
            <option value="140">YEMEN</option>
            <option value="141">ISLE OF MAN</option>
            <option value="142">NORFOLK ISLAND</option>
            <option value="143">CAYMAN ISLANDS</option>
            <option value="144">FALKLAND ISLANDS (MALVINAS)</option>
            <option value="145">FIJI ISLANDS</option>
            <option value="146">VIRGIN ISLANDS (BRITISH)</option>
            <option value="147">VIRGIN ISLANDS (UNITED STATES)</option>
            <option value="148">INDONESIA</option>
            <option value="149">IRAN (ISLAMIC REPUBLIC)</option>
            <option value="150">IRAQ</option>
            <option value="151">KUWAIT</option>
            <option value="152">LAOS (DEMOCRATIC PEOPLE'S REP)</option>
            <option value="153">LESOTHO</option>
            <option value="154">LEBANON</option>
            <option value="155">LIBERIA</option>
            <option value="156">LIBYA</option>
            <option value="157">MACAO</option>
            <option value="158">MADAGASCAR</option>
            <option value="159">MALAYSIA</option>
            <option value="160">MALAWI</option>
            <option value="161">MALDIVES</option>
            <option value="162">MALI</option>
            <option value="163">MARTINIQUE</option>
            <option value="164">MOLDOVA</option>
            <option value="165">MONGOLIA</option>
            <option value="166">MONTENEGRO</option>
            <option value="167">MYANMAR</option>
            <option value="168">NAMIBIA</option>
            <option value="169">NEPAL</option>
            <option value="170">NICARAGUA</option>
            <option value="171">NIGER</option>
            <option value="172">NIGERIA</option>
            <option value="173">OMAN</option>
            <option value="174">PALAU</option>
            <option value="175">PAPUA NEW GUINEA</option>
            <option value="176">FRENCH POLYNESIA</option>
            <option value="177">KENYA</option>
            <option value="178">KYRGYZSTAN</option>
            <option value="179">DOMINICAN REPUBLIC</option>
            <option value="180">RWANDA</option>
            <option value="181">SAMOA</option>
            <option value="182">SAINT HELENA</option>
            <option value="183">SAO MARTINHO (FRENCH SIDE)</option>
            <option value="184">WESTERN SARA</option>
            <option value="185">SENEGAL</option>
            <option value="186">SIERRA LEONE</option>
            <option value="187">SEYCHELLES</option>
            <option value="188">SOMALIA</option>
            <option value="189">SRI LANKA</option>
            <option value="190">SWAZILAND</option>
            <option value="191">SUDAN</option>
            <option value="192">SURINAME</option>
            <option value="193">TAJIKISTAN</option>
            <option value="194">TANZANIA</option>
            <option value="195">OCCUPIED PALESTINIAN TERRITORY</option>
            <option value="196">EAST TIMOR</option>
            <option value="197">TOGO</option>
            <option value="198">TRINITY AND TOBAGO</option>
            <option value="199">TURKMENISTAN</option>
            <option value="200">MAURICIA</option>
            <option value="201">MAURITANIA</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-group__label" for="reg_dob">DATA DE NASCIMENTO*</label>
          <input type="date" id="reg_dob" name="dob" class="form-group__input" required />
        </div>
      </div>

      <!-- ID Type & ID Number Row -->
      <div class="form-row">
        <div class="form-group select-group">
          <label class="form-group__label" for="reg_id_type">TIPO IDENTIFICAÇÃO*</label>
          <select id="reg_id_type" name="id_type" class="form-group__select" required>
            <option value="" disabled selected>Selecionar</option>
            <option value="cc">Cartão de Cidadão</option>
            <option value="passport">Passaporte</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-group__label" for="reg_id_number">NÚMERO IDENTIFICAÇÃO*</label>
          <input type="text" id="reg_id_number" name="id_number" class="form-group__input"
            placeholder="Número Identificação" required />
        </div>
      </div>

      <!-- Tax ID Field -->
      <div class="form-group">
        <label class="form-group__label" for="reg_tax_number">NÚMERO DE IDENTIFICAÇÃO FISCAL (NIF)</label>
        <input type="text" id="reg_tax_number" name="tax_number" class="form-group__input" placeholder="000000000" />
      </div>
      <!-- Password -->
      <div class="form-group">
        <label class="form-group__label" for="reg_password">PALAVRA-PASSE</label>
        <input type="password" id="reg_password" name="password" class="form-group__input" placeholder="Palavra-passe"
          required />
      </div>
      <!-- Privacy Policy Checkbox -->
      <div class="checkbox-label checkbox-label--with-link">
        <input type="checkbox" id="reg_privacy" name="privacy" class="checkbox-label__input" required />
        <label class="checkbox-label__text" for="reg_privacy">
          Consinto o tratamento dos meus dados pessoais de acordo com a
          <a href="/politica-de-privacidade/" class="checkbox-label__link">Política de Privacidade</a>.
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