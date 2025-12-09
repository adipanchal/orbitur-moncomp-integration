<?php if (!defined('ABSPATH'))
  exit; ?>

<div id="orbitur-dashboard-root">
  <!-- You can paste your full dashboard markup here. For brevity, include minimal placeholders -->
  <?php
  // The full HTML you previously provided can go here. For brevity use a simplified version:
  ?>
  <div class="dashboard" id="dashboard-app">
    <aside class="dashboard__sidebar" aria-label="menu lateral">
      <h3 class="section-heading">A Minha Conta</h3>
      <ul class="nav-menu" id="nav-menu">
        <li class="nav-menu__item nav-menu__item--active" data-tab="perfil">Perfil</li>
        <li class="nav-menu__item" data-tab="estadias">Estadias</li>
        <li class="nav-menu__item" data-tab="cartao">O Meu Cartão OCC</li>
        <li class="nav-menu__item" data-tab="descontos">Códigos Desconto OCC</li>
        <li class="nav-menu__item nav-menu__item--logout" id="logout-btn">Sair / Log out</li>
      </ul>
    </aside>

    <main class="dashboard__main">
      <section class="dashboard__content">
        <div class="content-panel" data-panel="perfil">
          <h3 class="section-heading">PERFIL</h3>
          <div id="profile-view" class="profile">
            <h3 class="profile__greeting">Olá, <span id="profile-name">Utilizador</span></h3>
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
            </div>
            <div class="button-group">
              <button class="btn btn--primary" id="open-edit-btn">Editar Dados de Conta</button>
              <button class="btn btn--primary" id="open-pw-btn">Alterar Palavra-Passe</button>
            </div>
          </div>

          <div id="edit-profile-view" class="profile-edit hidden">
            <div class="subsection-heading">EDITAR OS DADOS DE CONTA</div>
            <form id="profile-edit-form" class="edit-form mt-lg">
              <div class="form-row">
                <div class="form-group"><label>Nome</label><input id="edit-firstname" class="form-group__input" /></div>
                <div class="form-group"><label>E-mail</label><input id="edit-email" class="form-group__input" /></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label>Telefone</label><input id="edit-phone" class="form-group__input" /></div>
                <div class="form-group"><label>Morada</label><input id="edit-address" class="form-group__input" /></div>
              </div>
              <div class="form-actions">
                <button class="btn btn--primary" id="save-profile-btn" type="button">Guardar Dados de Conta</button>
              </div>
            </form>
          </div>

          <div id="password-view" class="password-change hidden">
            <div class="subsection-heading">ALTERAR A SUA PALAVRA-PASSE</div>
            <form id="change-pw-form" class="edit-form mt-lg">
              <div class="form-row">
                <div class="form-group"><label>Palavra-passe atual</label><input type="password" id="old-pw"
                    class="form-group__input" /></div>
                <div class="form-group"><label>Nova palavra-passe</label><input type="password" id="new-pw"
                    class="form-group__input" /></div>
              </div>
              <div class="form-actions">
                <button class="btn btn--primary" id="save-pw-btn" type="button">Guardar Alterações</button>
              </div>
            </form>
          </div>
        </div>

        <div class="content-panel" data-panel="estadias" style="display:none">
          <h3 class="section-heading">ESTADIAS</h3>
          <div class="tabs">
            <button class="tabs__btn tabs__btn--active" data-list="upcoming">PRÓXIMAS ESTADIAS</button>
            <button class="tabs__btn" data-list="past">ESTADIAS ANTERIORES</button>
          </div>
          <div id="bookings-upcoming" class="booking-list"></div>
          <div id="bookings-past" class="booking-list hidden"></div>
        </div>

        <div class="content-panel" data-panel="cartao" style="display:none">
          <h3 class="section-heading">O MEU CARTÃO OCC</h3>
          <div id="occ-card-wrapper" class="occ-card hidden">
            <div class="subsection-heading__value" id="card-member">—</div>
          </div>
          <div id="occ-not-member" class="occ-not-member">Não é cliente OCC? Faça a sua inscrição <a
              href="#occ-register-form" class="occ-not-member__link">aqui.</a></div>
        </div>

        <div class="content-panel" data-panel="descontos" style="display:none">
          <h3 class="section-heading">CÓDIGOS DESCONTO OCC</h3>
          <ul id="discounts-list" class="discount-list"></ul>
        </div>

      </section>
    </main>
  </div>
</div>