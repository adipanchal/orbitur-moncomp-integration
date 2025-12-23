<?php if (!defined('ABSPATH'))
  exit; ?>

<div id="orbitur-dashboard-root">

  <div class="dashboard" id="dashboard-app">

    <!-- ====================== SIDEBAR ====================== -->
    <aside class="dashboard__sidebar" aria-label="Menu lateral">
      <h3 class="section-heading">A Minha Conta</h3>

      <ul class="nav-menu" id="nav-menu">
        <li class="nav-menu__item nav-menu__item--active" data-tab="perfil">Perfil</li>
        <li class="nav-menu__item" data-tab="estadias">Estadias</li>
        <li class="nav-menu__item" data-tab="cartao">O Meu Cartão OCC</li>
        <li class="nav-menu__item" data-tab="descontos">Códigos Desconto OCC</li>

        <li class="nav-menu__item nav-menu__item--logout" id="logout-btn">Sair / Log out</li>
      </ul>
    </aside>

    <!-- ====================== MAIN CONTENT ====================== -->
    <main class="dashboard__main">
      <section class="dashboard__content">

        <!-- ====================== PERFIL TAB ====================== -->
        <div class="content-panel" data-panel="perfil">

          <h3 class="section-heading">PERFIL</h3>

          <!-- VIEW PROFILE -->
          <div id="profile-view" class="profile">

            <h3 class="profile__greeting">
              Olá, <span id="profile-name">Utilizador</span>
            </h3>
            <p class="profile__subtext">
              Este não é o seu perfil? Saia e faça login na sua conta
              <a href="/area-cliente/">aqui</a>.
            </p>

            <div class="info-grid">

              <div class="info-grid__item">
                <div class="info-grid__label">Nome</div>
                <div class="info-grid__value" id="p-name">—</div>
              </div>

              <div class="info-grid__item">
                <div class="info-grid__label">E-mail</div>
                <div class="info-grid__value" id="p-email">—</div>
              </div>

              <div class="info-grid__item">
                <div class="info-grid__label">Telefone</div>
                <div class="info-grid__value" id="p-phone">—</div>
              </div>

              <div class="info-grid__item">
                <div class="info-grid__label">Morada</div>
                <div class="info-grid__value" id="p-address">—</div>
              </div>

              <div class="info-grid__item">
                <div class="info-grid__label">País</div>
                <div class="info-grid__value" id="p-country">—</div>
              </div>
            </div>
            <div class="subsection-heading">
              NÚMERO MEMBRO OCC
              <div class="subsection-heading__value" id="p-member">—</div>
            </div>
            <div class="button-group">
              <button class="btn btn--primary" id="open-edit-btn">Editar Dados de Conta</button>
              <button class="btn btn--primary" id="open-pw-btn">Alterar Palavra-Passe</button>
            </div>

          </div>

          <!-- EDIT PROFILE -->
          <div id="edit-profile-view" class="profile-edit hidden">

            <div class="subsection-heading">EDITAR OS DADOS DE CONTA</div>

            <form id="profile-edit-form" class="edit-form mt-lg">

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Nome</label>
                  <input type="text" id="edit-firstname" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Apelido</label>
                  <input type="text" id="edit-lastname" class="form-group__input" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">E-mail</label>
                  <input type="email" id="edit-email" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Telefone</label>
                  <input type="text" id="edit-phone" class="form-group__input" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Morada</label>
                  <input type="text" id="edit-address" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Codigo Postal</label>
                  <input type="text" id="edit-zipcode" class="form-group__input" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Localidade</label>
                  <input type="text" id="edit-city" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">País</label>
                  <select id="edit-country" class="form-group__select">
                    <option value="">Selecionar país</option>
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
              </div>
              <div class="form-actions">
                <button type="button" class="btn btn--primary" id="save-profile-btn">
                  Guardar Dados de Conta
                </button>
              </div>

            </form>
          </div>

          <!-- CHANGE PASSWORD -->
          <div id="password-view" class="password-change hidden">

            <div class="subsection-heading">ALTERAR A SUA PALAVRA-PASSE</div>

            <form id="change-pw-form" class="edit-form mt-lg">

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Palavra-passe atual</label>
                  <input type="password" id="old-pw" class="form-group__input" placeholder="Palavra-passe atual" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Nova palavra-passe</label>
                  <input type="password" id="new-pw" class="form-group__input" placeholder="Nova Palavra-passe" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Confirmar nova palavra-passe</label>
                  <input type="password" id="confirm-pw" class="form-group__input"
                    placeholder="Confirmar nova palavra-passe" />
                </div>
              </div>

              <div class="form-actions">
                <button type="button" class="btn btn--primary" id="save-pw-btn">
                  Guardar Alterações
                </button>
              </div>

            </form>
          </div>

        </div>

        <!-- ====================== ESTADIAS TAB ====================== -->
        <div class="content-panel hidden" data-panel="estadias">

          <h3 class="section-heading">ESTADIAS</h3>

          <div id="estadias-list-view">
            <div class="tabs">
              <button class="tabs__btn tabs__btn--active" data-list="upcoming">
                PRÓXIMAS ESTADIAS
              </button>
              <button class="tabs__btn" data-list="past">
                ESTADIAS ANTERIORES
              </button>
            </div>
            <div id="bookings-past" class="hidden"></div>
            <div id="bookings-upcoming" class="hidden"></div>
          </div>
          <div id="manage-reserva-panel" class="manage-reserva">
            <div class="subsection-heading">GERIR RESERVA</div>

            <div class="manage-reserva__grid mt-lg">
              <div>
                <div class="info-item__label">PARQUE</div>
                <div class="info-item__value" id="m-site">—</div>
              </div>

              <div>
                <div class="info-item__label">CATEGORIA DO ALOJAMENTO</div>
                <div class="info-item__value" id="m-lodging">—</div>
              </div>

              <div>
                <div class="info-item__label">DATA DE ENTRADA</div>
                <div class="info-item__value" id="m-checkin">—</div>
              </div>

              <div>
                <div class="info-item__label">DATA DE SAÍDA</div>
                <div class="info-item__value" id="m-checkout">—</div>
              </div>

              <div>
                <div class="info-item__label">NÚMERO DE PESSOAS</div>
                <div class="info-item__value" id="m-persons">—</div>
              </div>

              <div>
                <div class="info-item__label">ID DE RESERVA</div>
                <div class="info-item__value" id="m-reserva-id">00000000</div>
              </div>

              <div>
                <div class="info-item__label">VALOR DA RESERVA</div>
                <div class="info-item__value" id="m-price">—</div>
              </div>
            </div>

            <div class="subsection-heading">
              PEDIDO DE ALTERAÇÃO DE RESERVA
            </div>

            <p class="disclaimer">
              Mudar para outro parque na cadeia: Custo adicional de 10€
            </p>

            <!-- ALTERAÇÃO FORM -->
            <form id="alter-reserva-form" class="manage-reserva__form">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Parque</label>
                  <input type="text" id="alt-site" class="form-group__input" disabled />
                </div>

                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Categoria do alojamento desejado</label>
                  <input type="text" id="alt-lodging" class="form-group__input" disabled />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Data de Entrada</label>
                  <input type="date" id="alt-date-in" class="form-group__input" />
                </div>

                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Data de Saída</label>
                  <input type="date" id="alt-date-out" class="form-group__input" />
                </div>
              </div>

              <div class="form-row form-row--single">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Número de pessoas</label>
                  <input type="number" id="alt-persons" class="form-group__input" />
                </div>
              </div>
              <span class="form-info-note">
                Após a submissão, o seu pedido irá ser analisado e respondido
                logo que possível.
              </span>
              <label class="checkbox-label mt-lg mb-lg">
                <input type="checkbox" id="alt-copy" class="checkbox-label__input" />
                <span class="checkbox-label__text">
                  Quero receber cópia deste pedido de contacto no e-mail.
                </span>
              </label>

              <button class="btn btn--primary" type="submit">
                ENVIAR PEDIDO DE ALTERAÇÃO
              </button>
            </form>
          </div>
        </div>

        <!-- ====================== OCC CARD TAB ====================== -->
        <div class="content-panel hidden" data-panel="cartao">
          <h3 class="section-heading">O MEU CARTÃO OCC</h3>
          <!-- OCC CARD -->
          <div class="occ-card hidden">
            <img src="https://orbitur.staging-blendd.com/wp-content/uploads/2025/10/cartao-orbitur@2x.png"
              alt="cartão OCC" class="occ-card__image" />
            <div class="occ-card__content">
              <div class="subsection-heading">NÚMERO MEMBRO OCC</div>
              <div class="subsection-heading__value" id="card-member">
                -
              </div>

              <!-- Member Details -->
              <div class="member-details" aria-label="Informações do membro">
                <div class="member-details__col">
                  <div class="info-item">
                    <div class="info-item__label">Estado</div>
                    <div class="info-item__value" id="card-status">-</div>
                  </div>
                  <div class="info-item">
                    <div class="info-item__label">E-mail</div>
                    <div class="info-item__value" id="card-email">-</div>
                  </div>
                </div>

                <div class="member-details__col">
                  <div class="info-item">
                    <div class="info-item__label">Data de inscrição</div>
                    <div class="info-item__value info-item__value--small">-</div>
                  </div>
                  <div class="info-item">
                    <div class="info-item__label">Data de validade</div>
                    <div class="info-item__value info-item__value--small" id="card-valid">-</div>
                  </div>
                </div>
              </div>

              <!-- Card Actions -->
              <div class="button-group">
                <button class="btn btn--primary">
                  FAZER DOWNLOAD DO CARTÃO
                </button>
                <button class="btn btn--primary btn--secondary">
                  RENOVAR O MEU CARTÃO OCC
                </button>
              </div>
            </div>
          </div>
          <!-- NOT MEMBER BANNER -->
          <div id="occ-not-member" class="occ-not-member">
            Não é cliente OCC? Faça a sua inscrição
            <a href="#occ-register-form" class="occ-not-member__link">aqui.</a>
          </div>
          <!-- OCC REGISTER FORM -->
          <div class="occ-register hidden" id="occ-register-wrapper">
            <div class="subsection-heading">INSCRIÇÃO MEMBRO OCC</div>
            <form class="occ-register__form mt-lg" id="occ-register-form">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Nome*</label>
                  <input type="text" name="firstname" class="form-group__input" placeholder="Rui" required />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Apelido*</label>
                  <input type="text" name="lastname" class="form-group__input" placeholder="Faria" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">E-mail*</label>
                  <input type="email" name="email" class="form-group__input" placeholder="ruifariasantos@gmail.com"
                    required />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">TELEFONE*</label>
                  <div class="phone-input">
                    <input type="tel" name="phone" class="form-group__input phone-input__field" placeholder="Telemóvel"
                      required />
                  </div>
                </div>
              </div>

              <div class="form-row form-row--single">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Morada*</label>
                  <input type="text" name="address" class="form-group__input" placeholder="Rua de Camões, 27"
                    required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Código Postal*</label>
                  <input type="text" name="zipcode" class="form-group__input" placeholder="1300-200" required />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Localidade*</label>
                  <input type="text" name="city" class="form-group__input" placeholder="Lisboa" required />
                </div>
              </div>

              <div class="form-row form-row--single">
                <div class="form-group select-group">
                  <label class="form-group__label form-group__label--small">País*</label>
                  <select name="country" class="form-group__select" required>
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
              </div>

              <div class="form-row">
                <div class="form-group select-group">
                  <label class="form-group__label form-group__label--small">Nacionalidade*</label>
                  <select name="nationality" class="form-group__select" required>
                    <option value="" disabled selected>Selecionar</option>
                    <option selected>Portugal</option>
                    <option>Spain</option>
                    <option>France</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Data de Nascimento*</label>
                  <input type="date" name="birthdate" class="form-group__input" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group select-group">
                  <label class="form-group__label form-group__label--small">Tipo Identificação*</label>
                  <select name="id_type" class="form-group__select" required>
                    <option value="" disabled selected>Selecionar</option>
                    <option selected>Cartão de Cidadão</option>
                    <option>Passaporte</option>
                    <option>Outro Documento</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Número Identificação*</label>
                  <input type="text" name="id_number" class="form-group__input" placeholder="07984401" required />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small">Número Contribuinte</label>
                  <input type="text" name="tax_number" class="form-group__input" placeholder="" />
                </div>
              </div>

              <div class="occ-register__checkboxes">
                <label class="checkbox-label">
                  <input type="checkbox" name="terms" class="checkbox-label__input" required />
                  <span class="checkbox-label__text">
                    * Aceito os
                    <a href="#" class="checkbox-label__link">Termos e condições</a>
                  </span>
                </label>

                <label class="checkbox-label">
                  <input type="checkbox" name="newsletter" class="checkbox-label__input" />
                  <span class="checkbox-label__text">
                    *É minha vontade expressa e livre continuar a receber
                    comunicações por parte do OCC por referência a todas as
                    vantagens e promoções associadas ao programa de
                    fidelização (newsletter)
                  </span>
                </label>
                <p class="form-info-text">
                  Os dados pessoais recolhidos neste formulário de subscrição,
                  destinam-se exclusivamente a tratamento interno e serão
                  utilizados exclusivamente para fins de informação e
                  divulgação de ações dentro do âmbito de atividade do OCC.
                  Pode consultar a nossa
                  <a href="#" class="checkbox-label__link">Política de Privacidade</a>
                  e de Proteção de Dados Pessoais em Política & Privacidade e
                  exercer os seus direitos enquanto titular de dados pessoais
                  usando o formulário que a Orbitur disponibiliza para o
                  efeito.
                </p>
              </div>

              <!-- Submit -->
              <div class="occ-register__actions">
                <button type="submit" class="btn btn--primary">
                  ENVIAR PEDIDO DE INSCRIÇÃO
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- ====================== DESCONTOS TAB ====================== -->
        <div class="content-panel hidden" data-panel="descontos">
          <h3 class="section-heading">CÓDIGOS DESCONTO OCC</h3>
          <p class="content-panel__subheading">
            Para além dos benefícios OCC, verifique aqui os seus códigos de
            desconto extra!
          </p>
          <div class="subsection-heading">DESCONTOS ATUAIS</div>
          <ul id="discounts-list" class="discount-list"></ul>
        </div>

      </section>
    </main>

  </div>
</div>