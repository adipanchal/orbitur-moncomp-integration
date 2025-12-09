(function () {
  if (!window.jQuery) return;
  var $ = window.jQuery;

  // Helpers
  function esc(s) {
    return s ? String(s) : "";
  }
  function showPanel(name) {
    $(".nav-menu__item")
      .removeClass("nav-menu__item--active")
      .filter('[data-tab="' + name + '"]')
      .addClass("nav-menu__item--active");
    $("[data-panel]")
      .hide()
      .filter('[data-panel="' + name + '"]')
      .show();
  }

  // Wire menu
  $(document).on("click", ".nav-menu__item", function (e) {
    var tab = $(this).data("tab");
    if (!tab) return;
    showPanel(tab);
    if (tab === "perfil") loadProfile();
    if (tab === "estadias") loadBookings();
  });

  // logout
  $(document).on("click", "#logout-btn", function (e) {
    e.preventDefault();
    if (!confirm("Confirma o logout?")) return;
    $.post(
      orbitur_ajax.ajax_url,
      { action: "orbitur_logout", nonce: orbitur_ajax.nonce },
      function (r) {
        if (r.success && r.data.redirect) {
          window.location = r.data.redirect;
        } else {
          alert("Erro ao sair");
        }
      }
    );
  });

  // profile controls
  $(document).on("click", "#open-edit-btn", function () {
    $("#profile-view").hide();
    $("#edit-profile-view").show();
  });
  $(document).on("click", "#open-pw-btn", function () {
    $("#profile-view").hide();
    $("#password-view").show();
  });

  // save profile
  $(document).on("click", "#save-profile-btn", function () {
    var payload = {
      action: "orbitur_update_profile",
      nonce: orbitur_ajax.nonce,
      name: $("#edit-firstname").val(),
      email: $("#edit-email").val(),
      phone: $("#edit-phone").val(),
      address: $("#edit-address").val(),
      zipcode: $("#edit-zipcode").val(),
      city: $("#edit-city").val(),
      country: $("#edit-country").val(),
      nif: $("#edit-nif").val(),
    };
    $.post(orbitur_ajax.ajax_url, payload, function (r) {
      if (r.success) {
        alert("Perfil guardado");
        loadProfile();
        $("#edit-profile-view").hide();
        $("#profile-view").show();
      } else {
        alert("Erro: " + (r.data || r.responseText || "unknown"));
      }
    });
  });

  // change password
  $(document).on("click", "#save-pw-btn", function () {
    var oldpw = $("#old-pw").val(),
      newpw = $("#new-pw").val(),
      conf = $("#confirm-pw").val();
    if (!newpw || newpw !== conf) {
      alert("Nova palavra-passe inválida ou não coincide.");
      return;
    }
    $.post(
      orbitur_ajax.ajax_url,
      {
        action: "orbitur_change_password",
        nonce: orbitur_ajax.nonce,
        oldpw: oldpw,
        newpw: newpw,
      },
      function (r) {
        if (r.success) {
          alert("Palavra-passe alterada. Faça login novamente.");
          window.location = site_url || "/area-cliente/";
        } else alert("Erro: " + (r.data || r.responseText || "unknown"));
      }
    );
  });

  // load profile
  function loadProfile() {
    $.post(
      orbitur_ajax.ajax_url,
      { action: "orbitur_get_profile", nonce: orbitur_ajax.nonce },
      function (r) {
        if (!r.success) {
          console.error(r);
          return;
        }
        var d = r.data;
        $("#profile-name").text(d.name || "Utilizador");
        $("#p-name").text(d.name || "-");
        $("#p-email").text(d.email || "-");
        $("#p-phone").text(d.phone || "-");
        $("#edit-firstname").val(d.name || "");
        $("#edit-email").val(d.email || "");
        $("#edit-phone").val(d.phone || "");
        $("#edit-address").val(d.address || "");
        $("#edit-zipcode").val(d.zipcode || "");
        $("#edit-country").val(d.country || "");
        // OCC visibility
        if (d.memberNumber) {
          $("#occ-not-member").hide();
          $("#occ-card-wrapper").removeClass("hidden").show();
          $("#card-member").text(d.memberNumber);
        } else {
          $("#occ-not-member").show();
          $("#occ-card-wrapper").addClass("hidden").hide();
        }
      }
    );
  }

  // load bookings
  function loadBookings() {
    $("#bookings-upcoming").html("<p>Carregando...</p>");
    $("#bookings-past").html("");
    $.post(
      orbitur_ajax.ajax_url,
      { action: "orbitur_get_bookings", nonce: orbitur_ajax.nonce },
      function (r) {
        if (!r.success) {
          $("#bookings-upcoming").html(
            '<p class="empty-message">Erro a carregar reservas: ' +
              (r.data && r.data.message ? r.data.message : "Sem sessão") +
              "</p>"
          );
          return;
        }
        var upcoming = r.data.upcoming || [],
          past = r.data.past || [];
        renderBookings(upcoming, past);
      }
    );
  }

  function renderBookings(up, past) {
    var upC = $("#bookings-upcoming"),
      pastC = $("#bookings-past");
    upC.empty();
    pastC.empty();
    if (!up.length)
      upC.html('<p class="empty-message">Não há estadias próximas.</p>');
    up.forEach(function (b) {
      var site = b.site || (b.idSite ? "Parque " + b.idSite : "—");
      var begin = (b.begin || "").split("T")[0];
      var url = b.url || "#";
      var item = $('<div class="booking-item"></div>');
      var html =
        '<div class="booking-item__card"><div class="booking-item__site">' +
        esc(site) +
        '</div></div><div class="booking-item__card"><div class="booking-item__date">' +
        (begin || "-") +
        '</div></div><div class="booking-item__actions">' +
        (url
          ? '<a class="btn btn--primary" target="_blank" href="' +
            url +
            '">GERIR RESERVA</a>'
          : "") +
        "</div>";
      item.html(html);
      upC.append(item);
    });
    if (!past.length)
      pastC.html('<p class="empty-message">Não há estadias anteriores.</p>');
    past.forEach(function (b) {
      var site = b.site || (b.idSite ? "Parque " + b.idSite : "—");
      var begin = (b.begin || "").split("T")[0];
      var item = $('<div class="booking-item"></div>');
      item.html(
        '<div class="booking-item__card"><div class="booking-item__site">' +
          esc(site) +
          '</div></div><div class="booking-item__card"><div class="booking-item__date">' +
          (begin || "-") +
          "</div></div>"
      );
      pastC.append(item);
    });
  }

  // init on DOM ready
  $(function () {
    // display first panel
    showPanel("perfil");
    loadProfile();
  });
})();
