(function ($) {
  "use strict";

  $(document).ready(function () {
    function showMessage($form, msg, type) {
      var $box = $form.find(".orbitur-form-msg");
      if (!$box.length) {
        $box = $('<div class="orbitur-form-msg" aria-live="polite"></div>');
        $form.prepend($box);
      }
      $box
        .removeClass("error success")
        .addClass(type || "error")
        .text(msg)
        .show();
      if (type === "success") {
        setTimeout(function () {
          $box.fadeOut(400);
        }, 5000);
      }
    }
    /* =========================
     * LOGIN (AJAX ONLY)
     * ========================= */
    $(document).on("submit", "#orbitur-login-form", function (e) {
      e.preventDefault();

      const $form = $(this);

      // STEP 1: get fresh nonce
      $.post(orbitur_ajax.ajax_url, {
        action: "orbitur_get_form_nonce",
      }).done(function (n) {
        if (!n.success) {
          showMessage($form, "Erro de segurança.", "error");
          return;
        }

        // STEP 2: submit login with fresh nonce
        $.post(orbitur_ajax.ajax_url, {
          action: "orbitur_login_ajax",
          nonce: n.data.nonce,
          email: $form.find('[name="email"]').val(),
          pw: $form.find('[name="pw"]').val(),
          remember: $form.find('[name="remember"]').is(":checked") ? 1 : 0,
        }).done(function (res) {
          if (res.success) {
            window.location.href = res.data.redirect;
          } else {
            showMessage($form, res.data || "Login falhou", "error");
          }
        });
      });
    });

    /* =========================
     * FORGOT PASSWORD (MonCompte)
     * ========================= */
    // Toggle forgot form visibility
    $(document).on("click", "#orbitur-show-forgot", function (e) {
      e.preventDefault();
      $("#orbitur-login-form").addClass("hidden");
      $("#orbitur-forgot-form").removeClass("hidden");
    });

    $(document).on("click", "#orbitur-hide-forgot", function (e) {
      e.preventDefault();
      $("#orbitur-forgot-form").addClass("hidden");
      $("#orbitur-login-form").removeClass("hidden");
    });

    $(document).on("submit", "#orbitur-forgot-form", function (e) {
      e.preventDefault();
      var $form = $(this);

      const email = $form.find('[name="email"]').val();
      if (!email) {
        showMessage($form, "Introduza o email.", "error");
        return;
      }

      $.post(orbitur_ajax.ajax_url, {
        action: "orbitur_forgot_password",
        nonce: orbitur_ajax.nonce,
        email: email,
      })
        .done(function (res) {
          if (res.success) {
            showMessage($form, res.data.message || "Email enviado", "success");
          } else {
            showMessage($form, res.data || "Erro ao enviar email", "error");
          }
        })
        .fail(function () {
          showMessage($form, "Erro de rede.", "error");
        });
    });

    /* =========================
     * REGISTER (AJAX ONLY)
     * TEMP DEV WORKAROUND (NO SMTP)
     * ========================= */
    $(document).on("submit", "#orbitur-register-form", function (e) {
      e.preventDefault();

      var $form = $(this);

      // STEP 1: get fresh nonce (cache-safe)
      $.post(orbitur_ajax.ajax_url, {
        action: "orbitur_get_form_nonce",
      }).done(function (n) {
        if (!n || !n.success) {
          showMessage($form, "Erro de segurança.", "error");
          return;
        }

        // STEP 2: build payload with fresh nonce
        const data = {
          action: "orbitur_register_ajax",
          nonce: n.data.nonce,
        };

        $form.serializeArray().forEach(function (field) {
          data[field.name] = field.value;
        });

        // STEP 3: submit register
        $.post(orbitur_ajax.ajax_url, data)
          .done(function (res) {
            if (!res || !res.success) {
              showMessage($form, res?.data || "Erro ao criar conta", "error");
              return;
            }

            // TEMP DEV FLOW: show generated password inline
            if (res.data && res.data.password) {
              showMessage(
                $form,
                "Conta criada com sucesso. EMAIL: " +
                  $form.find('[name="email"]').val() +
                  " PASSWORD: " +
                  res.data.password,
                "success"
              );
            } else {
              showMessage($form, "Conta criada com sucesso.", "success");
            }

            // STEP 4: redirect to login page (after short delay)
            setTimeout(function () {
              window.location.href = res.data.redirect || "/area-cliente/";
            }, 1200);
          })
          .fail(function () {
            showMessage($form, "Erro de rede ao criar conta.", "error");
          });
      });
    });
  });
})(jQuery);
