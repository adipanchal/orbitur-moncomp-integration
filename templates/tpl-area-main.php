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
              <a href="#" data-logout-profile>aqui</a>
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
                  <label class="form-group__label form-group__label--small" for="edit-firstname">Nome</label>
                  <input type="text" id="edit-firstname" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="edit-lastname">Apelido</label>
                  <input type="text" id="edit-lastname" class="form-group__input" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="edit-email">E-mail</label>
                  <input type="email" id="edit-email" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="edit-phone">Telefone</label>
                  <input type="text" id="edit-phone" class="form-group__input" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="edit-address">Morada</label>
                  <input type="text" id="edit-address" class="form-group__input" />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="edit-zipcode">Codigo Postal</label>
                  <input type="text" id="edit-zipcode" class="form-group__input" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="edit-city">Localidade</label>
                  <input type="text" id="edit-city" class="form-group__input" />
                </div>
                <div class="form-group select-group">
                  <label class="form-group__label form-group__label--small" for="edit-country">País</label>
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
                  <span class="spinner hidden" aria-hidden="true"></span>
                  <span class="btn-text">Guardar Dados de Conta</span>
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
                  <label class="form-group__label form-group__label--small" for="old-pw">Palavra-passe atual</label>
                  <input type="password" id="old-pw" class="form-group__input" placeholder="Palavra-passe atual" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="new-pw">Nova palavra-passe</label>
                  <input type="password" id="new-pw" class="form-group__input" placeholder="Nova Palavra-passe" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="confirm-pw">Confirmar nova
                    palavra-passe</label>
                  <input type="password" id="confirm-pw" class="form-group__input"
                    placeholder="Confirmar nova palavra-passe" />
                </div>
              </div>

              <div class="form-actions">
                <button type="button" class="btn btn--primary" id="save-pw-btn">
                  <span class="spinner hidden" aria-hidden="true"></span>
                  <span class="btn-text">Guardar Alterações</span>
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
                  <label class="form-group__label form-group__label--small" for="alt-site">Parque</label>
                  <input type="text" id="alt-site" class="form-group__input" disabled />
                </div>

                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="alt-lodging">Categoria do alojamento
                    desejado</label>
                  <input type="text" id="alt-lodging" class="form-group__input" disabled />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="alt-date-in">Data de Entrada</label>
                  <input type="date" id="alt-date-in" class="form-group__input" />
                </div>

                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="alt-date-out">Data de Saída</label>
                  <input type="date" id="alt-date-out" class="form-group__input" />
                </div>
              </div>

              <div class="form-row form-row--single">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="alt-persons">Número de pessoas</label>
                  <input type="number" id="alt-persons" class="form-group__input" />
                </div>
              </div>
              <span class="form-info-note">
                Após a submissão, o seu pedido irá ser analisado e respondido
                logo que possível.
              </span>
              <label class="checkbox-label mt-lg mb-lg" for="alt-copy">
                <input type="checkbox" id="alt-copy" class="checkbox-label__input" />
                <span class="checkbox-label__text">
                  Quero receber cópia deste pedido de contacto no e-mail.
                </span>
              </label>

              <button class="btn btn--primary" type="submit">
                <span class="spinner hidden" aria-hidden="true"></span>
                <span class="btn-text">ENVIAR PEDIDO DE ALTERAÇÃO</span>
              </button>
            </form>
          </div>
        </div>

        <!-- ====================== OCC CARD TAB ====================== -->
        <div class="content-panel hidden" data-panel="cartao">
          <h3 class="section-heading">O MEU CARTÃO OCC</h3>
          <!-- OCC CARD -->
          <div class="occ-card hidden">
            <img src="/wp-content/uploads/2025/12/occ-card.webp" alt="cartão OCC" class="occ-card__image" />
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
                    <div class="info-item__value" id="card-start">-</div>
                  </div>
                  <div class="info-item">
                    <div class="info-item__label">Data de validade</div>
                    <div class="info-item__value" id="card-valid">-</div>
                  </div>
                </div>
              </div>

              <!-- Card Actions -->
              <div class="button-group">
                <button class="btn btn--primary">
                  FAZER DOWNLOAD DO CARTÃO
                </button>
                <button class="btn btn--primary" id="renew-card-btn">
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
                  <label class="form-group__label form-group__label--small" for="occ-firstname">Nome*</label>
                  <input type="text" id="occ-firstname" name="firstname" class="form-group__input" placeholder="Rui"
                    required />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-lastname">Apelido*</label>
                  <input type="text" id="occ-lastname" name="lastname" class="form-group__input" placeholder="Faria"
                    required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-email">E-mail*</label>
                  <input type="email" id="occ-email" name="email" class="form-group__input"
                    placeholder="ruifariasantos@gmail.com" required />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-phone">TELEFONE*</label>
                  <input type="tel" id="occ-phone" name="phone" class="form-group__input" placeholder="Telemóvel"
                    required />
                </div>
              </div>

              <div class="form-row form-row--single">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-address">Morada*</label>
                  <input type="text" id="occ-address" name="address" class="form-group__input"
                    placeholder="Rua de Camões, 27" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-zipcode">Código Postal*</label>
                  <input type="text" id="occ-zipcode" name="zipcode" class="form-group__input" placeholder="1300-200"
                    required />
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-city">Localidade*</label>
                  <input type="text" id="occ-city" name="city" class="form-group__input" placeholder="Lisboa"
                    required />
                </div>
              </div>

              <div class="form-row form-row--single">
                <div class="form-group select-group">
                  <label class="form-group__label form-group__label--small" for="occ-country">País*</label>
                  <select id="occ-country" name="country" class="form-group__select" required>
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
                  <label class="form-group__label form-group__label--small" for="occ-nationality">Nacionalidade*</label>
                  <select id="occ-nationality" name="nationality" class="form-group__select" required>
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
                  <label class="form-group__label form-group__label--small" for="occ-birthdate">Data de
                    Nascimento*</label>
                  <input type="date" id="occ-birthdate" name="birthdate" class="form-group__input" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group select-group">
                  <label class="form-group__label form-group__label--small" for="occ-id-type">Tipo
                    Identificação*</label>
                  <select id="occ-id-type" name="id_type" class="form-group__select" required>
                    <option value="" disabled selected>Selecionar</option>
                    <option value="cc">Cartão de Cidadão</option>
                    <option value="passport">Passaporte</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-id-number">Número
                    Identificação*</label>
                  <input type="text" id="occ-id-number" name="id_number" class="form-group__input"
                    placeholder="07984401" required />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-group__label form-group__label--small" for="occ-tax-number">Número
                    Contribuinte</label>
                  <input type="text" id="occ-tax-number" name="tax_number" class="form-group__input" placeholder="" />
                </div>
              </div>

              <div class="occ-register__checkboxes">
                <label class="checkbox-label" for="occ-terms">
                  <input type="checkbox" id="occ-terms" name="terms" class="checkbox-label__input" required />
                  <span class="checkbox-label__text">
                    * Aceito os
                    <a href="#" class="checkbox-label__link">Termos e condições</a>
                  </span>
                </label>

                <label class="checkbox-label" for="occ-newsletter">
                  <input type="checkbox" id="occ-newsletter" name="newsletter" class="checkbox-label__input" />
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
                  <a href="/politica-de-privacidade/" class="checkbox-label__link">Política de Privacidade</a>
                  e de Proteção de Dados Pessoais em Política & Privacidade e
                  exercer os seus direitos enquanto titular de dados pessoais
                  usando o formulário que a Orbitur disponibiliza para o
                  efeito.
                </p>
              </div>

              <!-- Submit -->
              <div class="occ-register__actions">
                <button type="submit" class="btn btn--primary">
                  <span class="spinner hidden" aria-hidden="true"></span>
                  <span class="btn-text">ENVIAR PEDIDO DE INSCRIÇÃO</span>
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