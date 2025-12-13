/**
 * Orbitur Dashboard JS
 * Loaded ONLY on /bem-vindo
 * Requires localized object: orbitur_ajax
 */
(function ($) {
  ("use strict");

  if (
    typeof orbitur_ajax === "undefined" ||
    !document.querySelector('[data-panel="perfil"]')
  ) {
    return; // not dashboard page
  }

  const AJAX_URL = orbitur_ajax.ajax_url;
  const NONCE = orbitur_ajax.nonce;
  const LOGIN_URL = orbitur_ajax.area_client_url || "/area-cliente/";

  var OrbiturState = {
    bookings: {
      upcoming: [],
      past: [],
    },
    activeBooking: null,
  };

  /* ----------------------------------------------------
   * Helpers
   * -------------------------------------------------- */
  function esc(s) {
    return s ? String(s) : "";
  }

  function ajaxFail(msg) {
    alert(msg || "Erro de rede.");
  }

  function resetEstadiasView() {
    $(".tabs").show();
    $("#bookings-upcoming").show();
    $("#bookings-past").addClass("hidden").hide();
    $("#manage-reserva-panel").hide();
  }
  /* ----------------------------------------------------
   * Booking tabs (upcoming / past)
   * -------------------------------------------------- */
  $(document).on("click", ".tabs__btn", function () {
    $(".tabs__btn").removeClass("tabs__btn--active");
    $(this).addClass("tabs__btn--active");

    const list = $(this).data("list");

    if (list === "upcoming") {
      $("#bookings-upcoming").show();
      $("#bookings-past").hide();
    } else {
      $("#bookings-upcoming").hide();
      $("#bookings-past").show();
    }
  });
  /* ----------------------------------------------------
   * Panels / Tabs
   * -------------------------------------------------- */
  function resetPerfilSubviews() {
    $("#profile-view").show();
    $("#edit-profile-view, #password-view").addClass("hidden").hide();
  }

  function hideManageReserva() {
    $("#manage-reserva-panel").hide();
  }

  function showPanel(name) {
    $(".nav-menu__item").removeClass("nav-menu__item--active");
    $('.nav-menu__item[data-tab="' + name + '"]').addClass(
      "nav-menu__item--active"
    );

    $("[data-panel]").hide();
    $('[data-panel="' + name + '"]').show();

    // resets
    hideManageReserva();
    if (name === "perfil") resetPerfilSubviews();
  }

  /* ----------------------------------------------------
   * Profile
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

        // ===== OCC CARD VISIBILITY =====
        if (d.memberNumber) {
          // User IS OCC member
          $(".occ-card").removeClass("hidden").show();
          $("#occ-not-member").hide();
          $("#occ-register-wrapper").addClass("hidden").hide();

          $("#card-member").text(d.memberNumber);
          $("#card-status").text("Ativo");
        } else {
          // User IS NOT OCC member
          $(".occ-card").addClass("hidden").hide();
          $("#occ-not-member").show();
          $("#occ-register-wrapper").removeClass("hidden").show();
        }
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar perfil.");
      });
  }

  /* ----------------------------------------------------
   * Profile edit
   * -------------------------------------------------- */
  $("#open-edit-btn").on("click", function () {
    $("#profile-view").hide();
    $("#edit-profile-view").removeClass("hidden").show();
  });

  $("#open-pw-btn").on("click", function () {
    $("#profile-view").hide();
    $("#password-view").removeClass("hidden").show();
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
        resetPerfilSubviews();
        loadProfile();
        alert("Perfil atualizado.");
      })
      .fail(function () {
        ajaxFail("Erro ao guardar perfil.");
      });
  });

  /* ----------------------------------------------------
   * Password
   * -------------------------------------------------- */
  $("#save-pw-btn").on("click", function () {
    const oldpw = $("#old-pw").val();
    const newpw = $("#new-pw").val();
    const conf = $("#confirm-pw").val();

    if (!newpw || newpw !== conf) {
      alert("Palavra-passe inválida.");
      return;
    }

    $.post(AJAX_URL, {
      action: "orbitur_change_password",
      nonce: NONCE,
      oldpw: oldpw,
      newpw: newpw,
    })
      .done(function (res) {
        if (!res.success) {
          alert("Erro ao alterar palavra-passe.");
          return;
        }
        alert("Palavra-passe alterada. Faça login novamente.");
        window.location = LOGIN_URL;
      })
      .fail(function () {
        ajaxFail("Erro ao alterar palavra-passe.");
      });
  });

  /* ----------------------------------------------------
   * Bookings
   * -------------------------------------------------- */
  function renderBookings(list, isUpcoming) {
    const $c = isUpcoming ? $("#bookings-upcoming") : $("#bookings-past");
    $c.empty();

    if (!list.length) {
      $c.html(
        `<p class="empty-message">${
          isUpcoming
            ? "Não há estadias próximas."
            : "Não há estadias anteriores."
        }</p>`
      );
      return;
    }

    list.forEach(function (b) {
      const bookingJSON = JSON.stringify(b).replace(/'/g, "&apos;");

      const row = $(`
      <div class="booking-item">
        <div class="booking-item__card booking-item__card--park">
          <div class="booking-item__site">${b.site || "—"}</div>
        </div>

        <div class="booking-item__card booking-item__card--date">
          <div class="booking-item__date">${(b.begin || "").split("T")[0]}</div>
        </div>

        ${
          isUpcoming
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
    `);

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

        OrbiturState.bookings.upcoming = res.data.upcoming || [];
        OrbiturState.bookings.past = res.data.past || [];

        renderBookings(OrbiturState.bookings.upcoming, true);
        renderBookings(OrbiturState.bookings.past, false);

        // default tab
        $(".tabs__btn[data-list='upcoming']").click();
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar reservas.");
      });
  }

  /* ----------------------------------------------------
   * Gerir Reserva
   * -------------------------------------------------- */
  $(document).on("click", ".btn--manage", function () {
    const b = $(this).data("booking");
    if (!b) return;

    // 🔹 HIDE booking lists + tabs
    $(".estadias_bookings_archive").hide();
    $("#bookings-upcoming, #bookings-past").hide();
    $(".tabs").hide();

    // 🔹 FILL manage-reserva panel
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

    // 🔹 SHOW manage panel
    $("#manage-reserva-panel")
      .show()
      .removeClass("hidden")[0]
      .scrollIntoView({ behavior: "smooth", block: "start" });
  });

  /* ----------------------------------------------------
   * Logout
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
   * Menu
   * -------------------------------------------------- */
  $(".nav-menu__item").on("click", function () {
    const tab = $(this).data("tab");
    if (!tab) return;

    showPanel(tab);

    if (tab === "perfil") {
      resetPerfilSubviews();
      $("#manage-reserva-panel").hide();
      loadProfile();
    }

    if (tab === "estadias") {
      $("#manage-reserva-panel").hide();
      $(".estadias_bookings_archive").show();
      $(".tabs").show();
      $("#bookings-upcoming").show();
      loadBookings();
    }
  });

  $("#occ-register-form").on("submit", function (e) {
    e.preventDefault();

    const form = $(this);

    $.post(orbitur_ajax.ajax_url, {
      action: "orbitur_occ_register",
      nonce: orbitur_ajax.nonce,
      firstname: form.find('[name="firstname"]').val(),
      lastname: form.find('[name="lastname"]').val(),
      email: form.find('[name="email"]').val(),
      phone: form.find('[name="phone"]').val(),
      address: form.find('[name="address"]').val(),
      zipcode: form.find('[name="zipcode"]').val(),
      city: form.find('[name="city"]').val(),
      country: form.find('[name="country"]').val(),
      nationality: form.find('[name="nationality"]').val(),
      birthdate: form.find('[name="birthdate"]').val(),
      id_type: form.find('[name="id_type"]').val(),
      id_number: form.find('[name="id_number"]').val(),
      tax_number: form.find('[name="tax_number"]').val(),
    })
      .done(function (res) {
        if (!res.success) {
          alert("Erro ao criar membro OCC.");
          return;
        }

        alert("Inscrição OCC criada com sucesso!");
        loadProfile(); // refresh → card appears
      })
      .fail(function () {
        alert("Erro de rede.");
      });
  });
  /* ----------------------------------------------------
   * Init
   * -------------------------------------------------- */
  $(function () {
    showPanel("perfil");
    $("#manage-reserva-panel").hide();
    loadProfile();
  });
})(jQuery);
