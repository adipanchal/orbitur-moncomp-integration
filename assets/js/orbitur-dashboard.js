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
  const State = {
    bookings: {
      upcoming: [],
      past: [],
    },
  };

  /* ----------------------------------------------------
   * Modal Dialog
   * -------------------------------------------------- */
  const Modal = {
    show({ title = "", message = "", type = "info", confirm = false }) {
      $("#orbitur-modal-title").text(title);
      $("#orbitur-modal-message").html(message);

      // Explicitly control button visibility using .invisible class
      if (confirm) {
        $("#orbitur-modal-cancel").removeClass("invisible");
        $("#orbitur-modal-ok").text("Confirmar");
      } else {
        $("#orbitur-modal-cancel").addClass("invisible");
        $("#orbitur-modal-ok").text("OK");
      }

      // Apply type styling
      $("#orbitur-modal")
        .removeClass("modal-info modal-error modal-success")
        .addClass(`modal-${type}`);
      $("#orbitur-modal").removeClass("hidden");

      return new Promise((resolve) => {
        $("#orbitur-modal-ok")
          .off()
          .on("click", () => {
            Modal.hide();
            resolve(true);
          });

        $("#orbitur-modal-cancel")
          .off()
          .on("click", () => {
            Modal.hide();
            resolve(false);
          });

        $(".orbitur-modal__close, .orbitur-modal__overlay")
          .off()
          .on("click", () => {
            Modal.hide();
            resolve(false);
          });
      });
    },

    hide() {
      $("#orbitur-modal").addClass("hidden");
    },

    success(msg) {
      return this.show({ title: "Sucesso", message: msg, type: "success" });
    },

    error(msg) {
      return this.show({ title: "Erro", message: msg, type: "error" });
    },

    info(msg) {
      return this.show({ title: "Informação", message: msg, type: "info" });
    },

    confirm(msg) {
      return this.show({
        title: "Confirmação",
        message: msg,
        type: "info",
        confirm: true,
      });
    },
  };

  /* ----------------------------------------------------
   * Loading State Helper
   * -------------------------------------------------- */
  function setLoading($button, isLoading) {
    const $spinner = $button.find(".spinner");
    const $text = $button.find(".btn-text");

    if (isLoading) {
      $spinner.removeClass("hidden");
      $button.prop("disabled", true);
    } else {
      $spinner.addClass("hidden");
      $button.prop("disabled", false);
    }
  }

  /* ----------------------------------------------------
   * Helpers
   * -------------------------------------------------- */
  function ajaxFail(msg) {
    Modal.error(msg || "Erro de rede.");
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

  /* ----------------------------------------------------  /* Batch Load All Data (on init & refresh)              */
  /* ---------------------------------------------------- */
  function loadAllDashboardData() {
    // Load all data in parallel using Promise.all
    return Promise.all([
      // Fetch Profile
      $.post(AJAX_URL, {
        action: "orbitur_get_profile",
        nonce: NONCE,
      }),
      // Fetch Bookings
      $.post(AJAX_URL, {
        action: "orbitur_get_bookings",
        nonce: NONCE,
      }),
      // Fetch OCC Card Status
      $.post(AJAX_URL, {
        action: "orbitur_get_occ_status",
        nonce: NONCE,
      }),
    ])
      .then(function (results) {
        // results[0] = profile, results[1] = bookings, results[2] = occ_status
        const profileRes = results[0];
        const bookingsRes = results[1];
        const occRes = results[2];

        // Handle Profile
        if (profileRes && profileRes.success) {
          const d = profileRes.data;
          $("#profile-name, #p-name").text(d.name || "—");
          $("#p-email").text(d.email || "—");
          $("#p-phone").text(d.phone || "—");
          $("#p-address").text(d.morada_display || "—"); // Use morada_display for address
          $("#p-country").text(d.country || "—");
          $("#p-member").text(d.memberNumber || "—");

          $("#edit-firstname").val(d.first || "");
          $("#edit-lastname").val(d.last || "");
          $("#edit-email").val(d.email || "");
          const phoneVal = d.phone || "";
          const $phoneInput = $("#edit-phone");
          $phoneInput.val(phoneVal);
          try {
            const iti = $phoneInput.data("iti");
            if (iti) {
              if (phoneVal && phoneVal.indexOf("+") === 0) {
                iti.setNumber(phoneVal);
              } else if (d.country) {
                iti.setCountry((d.country || "PT").toLowerCase());
                // If phone doesn't have +, leave as is (iti handles it or user provided local)
              }
            }
          } catch (e) { }
          $("#edit-address").val(d.address || "");
          $("#edit-zipcode").val(d.zipcode || "");
          $("#edit-city").val(d.city || "");
          $("#edit-country").val(d.country || "");
        } else if (profileRes && !profileRes.success) {
          window.location = LOGIN_URL;
          return;
        }

        // Handle Bookings
        if (bookingsRes && bookingsRes.success) {
          State.bookings.upcoming = bookingsRes.data.upcoming || [];
          State.bookings.past = bookingsRes.data.past || [];
          renderBookings(State.bookings.upcoming, "#bookings-upcoming", true);
          renderBookings(State.bookings.past, "#bookings-past", false);
          $(".tabs__btn[data-list='upcoming']").click();
        } else if (bookingsRes && !bookingsRes.success) {
          // Bookings failed silently, don't block
        }

        // Handle OCC Card
        if (occRes && occRes.success && occRes.data.has_membership) {
          const data = occRes.data;

          // Check if member_number exists
          if (!data || !data.member_number) {
            $(".occ-card").addClass("hidden");
            $("#occ-not-member").removeClass("hidden");
            return;
          }

          $(".occ-card").removeClass("hidden");
          $("#occ-not-member").addClass("hidden");

          // Identification
          $("#card-member").text(data.member_number || "—");
          $("#card-status").text(
            data.status === "active" && data.valid_until ? "Ativo" : "Inativo"
          );
          $("#card-email").text(data.email || "—");

          // Date formatting helper
          function formatDatePT(dateStr) {
            if (!dateStr) return "—";
            const d = new Date(dateStr);
            if (isNaN(d)) return "—";
            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = String(d.getFullYear()).slice(-2);
            return `${day}/${month}/${year}`;
          }

          $("#card-valid").text(formatDatePT(data.valid_until));
          $("#card-start").text(formatDatePT(data.start_date));
        } else {
          $(".occ-card").addClass("hidden");
          $("#occ-not-member").removeClass("hidden");
        }
      })
      .catch(function (error) {
        console.error("Error loading dashboard data:", error);
        Modal.error("Erro ao carregar dados do dashboard.");
      });
  }

  /* ---------------------------------------------------- */
  /* Selective Refresh Functions                          */
  /* ---------------------------------------------------- */
  function refreshProfile() {
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
        $("#p-address").text(d.morada_display || "—");
        $("#p-country").text(d.country || "—");
        $("#p-member").text(d.memberNumber || "—");

        $("#edit-firstname").val(d.first || "");
        $("#edit-lastname").val(d.last || "");
        $("#edit-email").val(d.email || "");
        const phoneVal = d.phone || "";
        const $phoneInput = $("#edit-phone");
        $phoneInput.val(phoneVal);
        try {
          const iti = $phoneInput.data("iti");
          if (iti) {
            if (phoneVal && phoneVal.indexOf("+") === 0) {
              iti.setNumber(phoneVal);
            } else if (d.country) {
              iti.setCountry((d.country || "PT").toLowerCase());
            }
          }
        } catch (e) { }
        $("#edit-address").val(d.address || "");
        $("#edit-zipcode").val(d.zipcode || "");
        $("#edit-city").val(d.city || "");
        $("#edit-country").val(d.country || "");
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar perfil.");
      });
  }

  function refreshBookings() {
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
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar reservas.");
      });
  }

  function refreshOccCard() {
    $.post(AJAX_URL, {
      action: "orbitur_get_occ_status",
      nonce: NONCE,
    })
      .done(function (res) {
        if (!res.success || !res.data.has_membership) {
          $(".occ-card").addClass("hidden");
          $("#occ-not-member").removeClass("hidden");
          return;
        }

        const data = res.data;

        if (!res.data || !res.data.member_number) {
          $(".occ-card").addClass("hidden");
          $("#occ-not-member").removeClass("hidden");
          return;
        }

        $(".occ-card").removeClass("hidden");
        $("#occ-not-member").addClass("hidden");

        // Identification
        $("#card-member").text(data.member_number || "—");
        $("#card-status").text(
          data.status === "active" && data.valid_until ? "Ativo" : "Inativo"
        );
        $("#card-email").text(data.email || "—");

        // Date formatting helper
        function formatDatePT(dateStr) {
          if (!dateStr) return "—";
          const d = new Date(dateStr);
          if (isNaN(d)) return "—";
          const day = String(d.getDate()).padStart(2, "0");
          const month = String(d.getMonth() + 1).padStart(2, "0");
          const year = String(d.getFullYear()).slice(-2);
          return `${day}/${month}/${year}`;
        }

        $("#card-valid").text(formatDatePT(data.valid_until));
        $("#card-start").text(formatDatePT(data.start_date));
      })
      .fail(function () {
        console.error("Failed to load OCC card");
      });
  }

  /* ---------- PROFILE
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

        // Just display raw phone
        $("#p-phone").text(d.phone || "—");

        $("#p-address").text(d.morada_display || "—");
        $("#p-country").text(d.country || "—");
        $("#p-member").text(d.memberNumber || "—");

        $("#edit-firstname").val(d.first || "");
        $("#edit-lastname").val(d.last || "");
        $("#edit-email").val(d.email || "");

        // Populate EDIT Phone Field (Raw)
        $("#edit-phone").val(d.phone || "");

        $("#edit-address").val(d.address || "");
        $("#edit-zipcode").val(d.zipcode || "");
        $("#edit-city").val(d.city || "");
        $("#edit-country").val(d.country || "");
      })
      .fail(function () {
        ajaxFail("Erro de rede ao carregar perfil.");
      });
  }

  $("#open-edit-btn").on("click", function () {
    $("#profile-view").hide();
    $("#edit-profile-view").show();
    $("#edit-firstname, #edit-lastname").prop("readonly", true);
  });

  $("#open-pw-btn").on("click", function () {
    $("#profile-view").hide();
    $("#password-view").show();
  });

  $("#save-profile-btn").on("click", function () {
    const $btn = $(this);
    setLoading($btn, true);

    $.post(AJAX_URL, {
      action: "orbitur_update_profile",
      nonce: NONCE,
      email: $("#edit-email").val(),
      phone: $("#edit-phone").val(), // Send raw input
      address: $("#edit-address").val(),
      zipcode: $("#edit-zipcode").val(),
      city: $("#edit-city").val(),
      country: $("#edit-country").val(),
    })
      .done(function (res) {
        if (!res.success) {
          Modal.error("Erro ao guardar perfil.");
          setLoading($btn, false);
          return;
        }
        Modal.success("Perfil atualizado.");
        $("#edit-profile-view").hide();
        $("#profile-view").show();
        refreshProfile(); // Refresh only profile data
        setLoading($btn, false);
      })
      .fail(function () {
        ajaxFail("Erro ao guardar perfil.");
        setLoading($btn, false);
      });
  });

  /* ----------------------------------------------------
   * PASSWORD
   * -------------------------------------------------- */
  $("#save-pw-btn").on("click", function () {
    const $btn = $(this);
    const oldpw = $("#old-pw").val().trim();
    const newpw = $("#new-pw").val().trim();
    const conf = $("#confirm-pw").val().trim();

    if (!newpw || newpw !== conf) {
      Modal.error("As palavras-passe não coincidem.");
      return;
    }
    setLoading($btn, true);

    $.post(orbitur_ajax.ajax_url, {
      action: "orbitur_change_password",
      nonce: orbitur_ajax.nonce,
      oldpw: oldpw,
      newpw: newpw,
    })
      .done(function (res) {
        if (!res.success) {
          Modal.error(res.data || "Erro ao alterar palavra-passe");
          setLoading($btn, false);
          return;
        }

        Modal.success("Palavra-passe alterada com sucesso.");
        window.location.href = res.data.redirect;
      })
      .fail(function () {
        Modal.error("Erro de rede.");
        setLoading($btn, false);
      });
  });

  /* ----------------------------------------------------
   * BOOKINGS
   * -------------------------------------------------- */
  function renderBookings(list, target, upcoming) {
    const $container = $(target).empty();

    if (!list.length) {
      $container.html(
        `<p class="empty-message">${upcoming ? "Não há estadias próximas." : "Não há estadias anteriores."
        }</p>`
      );
      return;
    }

    const $wrapper = $('<div class="booking-list__inner"></div>');

    // Header (once)
    const header = `
    <div class="list-header" aria-hidden="true" style="grid-template-columns: 2fr 1fr;">
      <div class="list-header__label">PARQUE</div>
      <div class="list-header__label">DATA</div>
    </div>
  `;
    $wrapper.append(header);

    // Rows
    list.forEach(function (b) {
      const bookingJSON = JSON.stringify(b).replace(/'/g, "&apos;");

      const row = `
      <div class="booking-item">
        <div class="booking-item__card booking-item__card--park">
          <div class="booking-item__site">${b.site || "—"}</div>
        </div>
        <div class="booking-item__card booking-item__card--date">
          <div class="booking-item__date">${(b.begin || "").split("T")[0]}</div>
        </div>
        ${upcoming
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
      $wrapper.append(row);
    });

    $container.append($wrapper);
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

  function loadOccCard() {
    $.post(AJAX_URL, {
      action: "orbitur_get_occ_status",
      nonce: NONCE,
    })
      .done(function (res) {
        if (!res.success || !res.data.has_membership) {
          $(".occ-card").addClass("hidden");
          $("#occ-not-member").removeClass("hidden");
          return;
        }

        const data = res.data;

        if (!res.data || !res.data.member_number) {
          $(".occ-card").addClass("hidden");
          $("#occ-not-member").removeClass("hidden");
          return;
        }

        $(".occ-card").removeClass("hidden");
        $("#occ-not-member").addClass("hidden");

        // Identification
        $("#card-member").text(data.member_number || "—");
        $("#card-status").text(
          data.status === "active" && data.valid_until ? "Ativo" : "Inativo"
        );
        $("#card-email").text(data.email || "—");

        // EXPIRATION DATE (The "Perfect" December Date)
        // This is the 'fidelityDate' from Person object [cite: 531, 541]
        function formatDatePT(dateStr) {
          if (!dateStr) return "—";
          const d = new Date(dateStr);
          if (isNaN(d)) return "—";

          const day = String(d.getDate()).padStart(2, "0");
          const month = String(d.getMonth() + 1).padStart(2, "0");
          const year = String(d.getFullYear()).slice(-2);

          return `${day}/${month}/${year}`;
        }
        $("#card-valid").text(formatDatePT(data.valid_until));
        $("#card-start").text(formatDatePT(data.start_date));
      })
      .fail(function () {
        console.error("Failed to load OCC card");
      });
  }
  /* click "aqui" */
  $(document).on("click", ".occ-not-member__link", function (e) {
    e.preventDefault();
    // Prefill OCC registration form with MonCompte profile data when available
    $.post(AJAX_URL, { action: "orbitur_get_profile", nonce: NONCE })
      .done(function (res) {
        if (res && res.success && res.data) {
          prefillOccForm(res.data);
        }
      })
      .always(function () {
        $("#occ-not-member").addClass("hidden");
        $("#occ-register-wrapper").removeClass("hidden");
      });
  });

  // Prefill OCC registration form fields from profile object
  function prefillOccForm(d) {
    if (!d) return;
    const $form = $("#occ-register-form");
    $form.find('[name="firstname"]').val(d.first || "");
    $form.find('[name="lastname"]').val(d.last || "");
    $form.find('[name="email"]').val(d.email || "");

    // Consistent phone loading
    const phoneVal = d.mobile || d.phone || "";
    $form.find('[name="phone"]').val(phoneVal);

    $form.find('[name="address"]').val(d.address || "");
    $form.find('[name="zipcode"]').val(d.zipcode || "");
    $form.find('[name="city"]').val(d.city || "");
    $form.find('[name="country"]').val(d.country || "");
    // Additional identity fields
    if (d.civility) $form.find('[name="civility"]').val(d.civility);
    if (d.id_type) $form.find('[name="id_type"]').val(d.id_type);
    if (d.nationality) $form.find('[name="nationality"]').val(d.nationality);
    // id_number / tax_number / birthdate may not be available from profile
    // leave them blank if not present on MonCompte
    if (d.id_number) $form.find('[name="id_number"]').val(d.id_number);
    if (d.tax_number) $form.find('[name="tax_number"]').val(d.tax_number);
    if (d.birthdate) $form.find('[name="birthdate"]').val(d.birthdate);
  }

  /* submit registration */
  $("#occ-register-form").on("submit", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find("button[type='submit']");
    setLoading($btn, true);

    const data = $form.serializeArray();
    data.push({ name: "action", value: "orbitur_occ_register" });
    data.push({ name: "nonce", value: orbitur_ajax.nonce });

    $.post(orbitur_ajax.ajax_url, data, function (r) {
      if (r.success) {
        Modal.success("Pedido enviado. Em análise.");
        $form.trigger("reset");
        // Removed call to updateOccUI as per instructions
      } else {
        Modal.error("Erro ao enviar pedido.");
      }
      setLoading($btn, false);
    });
  });

  /* ----------------------------------------------------
  /* Logout Handler (reusable)                            */
  /* ---------------------------------------------------- */
  function performLogout() {
    Modal.confirm("Sair da conta?").then((ok) => {
      if (!ok) return;

      $.post(AJAX_URL, {
        action: "orbitur_logout",
        nonce: NONCE,
      }).always(function () {
        window.location = LOGIN_URL;
      });
    });
  }

  /* ---------------------------------------------------- */
  /* LOGOUT BUTTON                                        */
  /* ---------------------------------------------------- */
  $("#logout-btn").on("click", function () {
    performLogout();
  });

  /* ---------------------------------------------------- */
  /* PROFILE LOGOUT LINK (aqui link in profile greeting) */
  /* ---------------------------------------------------- */
  $(document).on("click", "[data-logout-profile]", function (e) {
    e.preventDefault();
    performLogout();
  });

  /* ----------------------------------------------------
   * MENU
   * -------------------------------------------------- */
  $(".nav-menu__item").on("click", function () {
    const tab = $(this).data("tab");
    if (!tab) return;

    showPanel(tab);

    // Data is already preloaded on init, just show UI
    if (tab === "estadias") {
      $(".estadias_bookings_archive").show();
      $(".tabs").show();
    }
  });

  /* ----------------------------------------------------
   * INIT
   * -------------------------------------------------- */
  $(function () {
    showPanel("perfil");
    loadAllDashboardData(); // Load all data once on init
  });
})(jQuery);
