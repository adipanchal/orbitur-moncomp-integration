/**
 * Orbitur Dashboard JS
 * Loaded ONLY on /area-cliente/bem-vindo
 * Requires localized object: orbitur_ajax
 */
(function ($) {
  ("use strict");

  /* ----------------------------------------------------
   * Guard
   * -------------------------------------------------- */
  if (
    typeof orbitur_ajax === "undefined" ||
    !document.querySelector('[data-panel="perfil"]')
  ) {
    return;
  }

  const AJAX_URL = orbitur_ajax.ajax_url;
  const NONCE = orbitur_ajax.nonce;
  const LOGIN_URL = orbitur_ajax.area_client_url || "/area-cliente/";

  let OCC_UI_LOCK = false; // prevents auto-hide after clicking "aqui"

  const State = {
    bookings: {
      upcoming: [],
      past: [],
    },
  };

  /* ----------------------------------------------------
   * Helpers
   * -------------------------------------------------- */
  function ajaxFail(msg) {
    alert(msg || "Erro de rede.");
  }

  function showPanel(name) {
    $(".nav-menu__item").removeClass("nav-menu__item--active");
    $('.nav-menu__item[data-tab="' + name + '"]').addClass(
      "nav-menu__item--active"
    );

    $("[data-panel]").hide();
    $('[data-panel="' + name + '"]').show();

    $("#manage-reserva-panel").hide();

    if (name === "perfil") {
      $("#profile-view").show();
      $("#edit-profile-view, #password-view").hide();
    }
  }

  /* ----------------------------------------------------
   * PROFILE
   * -------------------------------------------------- */
  function loadProfile() {
    $.post(AJAX_URL, {
      action: "orbitur_get_profile",
      nonce: NONCE,
    })
      .done(function (res) {
        if (!res || !res.success) {
          window.location = LOGIN_URL;
          return;
        }

        const d = res.data;

        $("#profile-name, #p-name").text(d.name || "—");
        $("#p-email").text(d.email || "—");
        $("#p-phone").text(d.phone || "—");
        $("#p-address").text(d.address || "—");
        $("#p-country").text(d.country || "—");
        $("#p-nif").text(d.nif || "—");
        $("#p-member").text(d.memberNumber || "—");

        $("#edit-firstname").val(d.name || "");
        $("#edit-email").val(d.email || "");
        $("#edit-phone").val(d.phone || "");
        $("#edit-address").val(d.address || "");
        $("#edit-zipcode").val(d.zipcode || "");
        $("#edit-country").val(d.country || "");
        $("#edit-nif").val(d.nif || "");
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar perfil.");
      });
  }

  $("#open-edit-btn").on("click", function () {
    $("#profile-view").hide();
    $("#edit-profile-view").show();
  });

  $("#open-pw-btn").on("click", function () {
    $("#profile-view").hide();
    $("#password-view").show();
  });

  $("#save-profile-btn").on("click", function () {
    $.post(AJAX_URL, {
      action: "orbitur_update_profile",
      nonce: NONCE,
      name: $("#edit-firstname").val(),
      email: $("#edit-email").val(),
      phone: $("#edit-phone").val(),
      address: $("#edit-address").val(),
      zipcode: $("#edit-zipcode").val(),
      country: $("#edit-country").val(),
      nif: $("#edit-nif").val(),
    })
      .done(function (res) {
        if (!res.success) {
          alert("Erro ao guardar perfil.");
          return;
        }
        alert("Perfil atualizado.");
        $("#edit-profile-view").hide();
        $("#profile-view").show();
        loadProfile();
      })
      .fail(function () {
        ajaxFail("Erro ao guardar perfil.");
      });
  });

  /* ----------------------------------------------------
   * PASSWORD
   * -------------------------------------------------- */
  $("#save-pw-btn").on("click", function () {
    const oldpw = $("#old-pw").val().trim();
    const newpw = $("#new-pw").val().trim();
    const conf = $("#confirm-pw").val().trim();

    if (!newpw || newpw !== conf) {
      alert("Nova palavra-passe inválida.");
      return;
    }

    $("#save-pw-btn").prop("disabled", true);

    $.post(orbitur_ajax.ajax_url, {
      action: "orbitur_change_password",
      nonce: orbitur_ajax.nonce,
      oldpw: oldpw,
      newpw: newpw,
    })
      .done(function (res) {
        if (!res.success) {
          alert(res.data || "Erro ao alterar palavra-passe");
          $("#save-pw-btn").prop("disabled", false);
          return;
        }

        alert("Palavra-passe alterada com sucesso.");
        window.location.href = res.data.redirect;
      })
      .fail(function () {
        alert("Erro de rede.");
        $("#save-pw-btn").prop("disabled", false);
      });
  });

  /* ----------------------------------------------------
   * BOOKINGS
   * -------------------------------------------------- */
  function renderBookings(list, target, upcoming) {
    const $c = $(target).empty();

    if (!list.length) {
      $c.html(
        `<p class="empty-message">${
          upcoming ? "Não há estadias próximas." : "Não há estadias anteriores."
        }</p>`
      );
      return;
    }

    list.forEach(function (b) {
      const bookingJSON = JSON.stringify(b).replace(/'/g, "&apos;");

      const row = `
        <div class="booking-item">
          <div class="booking-item__card booking-item__card--park">
            <div class="booking-item__site">${b.site || "—"}</div>
          </div>
          <div class="booking-item__card booking-item__card--date">
            <div class="booking-item__date">${
              (b.begin || "").split("T")[0]
            }</div>
          </div>
          ${
            upcoming
              ? `<div class="booking-item__actions">
                   <button
                     type="button"
                     class="btn btn--primary btn--manage"
                     data-booking='${bookingJSON}'>
                     GERIR RESERVA
                   </button>
                 </div>`
              : ""
          }
        </div>
      `;
      $c.append(row);
    });
  }

  function loadBookings() {
    $.post(AJAX_URL, {
      action: "orbitur_get_bookings",
      nonce: NONCE,
    })
      .done(function (res) {
        if (!res.success) {
          ajaxFail("Erro ao carregar reservas.");
          return;
        }

        State.bookings.upcoming = res.data.upcoming || [];
        State.bookings.past = res.data.past || [];

        renderBookings(State.bookings.upcoming, "#bookings-upcoming", true);
        renderBookings(State.bookings.past, "#bookings-past", false);

        $(".tabs__btn[data-list='upcoming']").click();
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar reservas.");
      });
  }

  $(document).on("click", ".tabs__btn", function () {
    $(".tabs__btn").removeClass("tabs__btn--active");
    $(this).addClass("tabs__btn--active");

    const list = $(this).data("list");
    $("#bookings-upcoming").toggle(list === "upcoming");
    $("#bookings-past").toggle(list === "past");
  });

  /* ----------------------------------------------------
   * MANAGE RESERVA
   * -------------------------------------------------- */
  $(document).on("click", ".btn--manage", function () {
    const b = $(this).data("booking");
    if (!b) return;

    $(".estadias_bookings_archive").hide();
    $(".tabs").hide();

    $("#m-site").text(b.site || "—");
    $("#m-lodging").text(b.lodging || "—");
    $("#m-checkin").text(b.begin || "—");
    $("#m-checkout").text(b.end || "—");
    $("#m-persons").text(b.nbPers || "—");
    $("#m-reserva-id").text(b.idOrder || "—");
    $("#m-price").text(b.priceCustomer || "—");

    $("#alt-site").val(b.site || "");
    $("#alt-lodging").val(b.lodging || "");
    $("#alt-date-in").val((b.begin || "").split("T")[0]);
    $("#alt-date-out").val((b.end || "").split("T")[0]);
    $("#alt-persons").val(b.nbPers || "");

    $("#manage-reserva-panel")
      .show()
      .removeClass("hidden")[0]
      .scrollIntoView({ behavior: "smooth", block: "start" });
  });

  /* ============================
   * OCC MEMBERSHIP
   * ============================ */

  function updateOccUI(state) {
    $(".occ-card").addClass("hidden");
    $("#occ-not-member").addClass("hidden");
    $("#occ-register-wrapper").addClass("hidden");
    $("#occ-pending").addClass("hidden");

    if (state === "active") {
      $(".occ-card").removeClass("hidden");
    } else if (state === "pending") {
      $("#occ-pending").removeClass("hidden");
    } else {
      $("#occ-not-member").removeClass("hidden");
    }
  }

  /* click "aqui" */
  $(document).on("click", ".occ-not-member__link", function (e) {
    e.preventDefault();
    $("#occ-not-member").addClass("hidden");
    $("#occ-register-wrapper").removeClass("hidden");
  });

  /* submit registration */
  $("#occ-register-form").on("submit", function (e) {
    e.preventDefault();

    const data = $(this).serializeArray();
    data.push({ name: "action", value: "orbitur_occ_register" });
    data.push({ name: "nonce", value: orbitur_ajax.nonce });

    $.post(orbitur_ajax.ajax_url, data, function (r) {
      if (r.success) {
        alert("Pedido enviado. Em análise.");
        updateOccUI("pending");
      } else {
        alert("Erro ao enviar pedido.");
      }
    });
  });

  /* ----------------------------------------------------
   * LOGOUT
   * -------------------------------------------------- */
  $("#logout-btn").on("click", function () {
    if (!confirm("Sair da conta?")) return;

    $.post(AJAX_URL, {
      action: "orbitur_logout",
      nonce: NONCE,
    }).always(function () {
      window.location = LOGIN_URL;
    });
  });

  /* ----------------------------------------------------
   * MENU
   * -------------------------------------------------- */
  $(".nav-menu__item").on("click", function () {
    const tab = $(this).data("tab");
    if (!tab) return;

    showPanel(tab);

    if (tab === "perfil") loadProfile();
    if (tab === "estadias") {
      $(".estadias_bookings_archive").show();
      $(".tabs").show();
      loadBookings();
    }
    if (tab === "cartao") {
      loadOccStatus();
    }
  });

  /* ----------------------------------------------------
   * INIT
   * -------------------------------------------------- */
  $(function () {
    showPanel("perfil");
    loadProfile();
  });
})(jQuery);
