(function ($) {
  "use strict";

  /* ----------------------------------------------------
   * Helpers
   * -------------------------------------------------- */
  function getErrorMessage(res, defaultMsg) {
    if (!res) return defaultMsg;
    // Handle string response (malformed JSON)
    if (typeof res === "string") {
      try {
        res = JSON.parse(res);
      } catch (e) {
        return defaultMsg;
      }
    }
    if (res.data) {
      if (typeof res.data === "string") return res.data;
      if (res.data.message) return res.data.message;
      if (res.data.error) return res.data.error;
    }
    return defaultMsg;
  }

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
    function setLoading($form, loading) {
      var $btn = $form.find('button[type="submit"]');
      if (!$btn.length) return;
      if (loading) {
        $btn.addClass("loading").prop("disabled", true);
        $btn.find(".spinner").removeClass("hidden");
      } else {
        $btn.removeClass("loading").prop("disabled", false);
        $btn.find(".spinner").addClass("hidden");
      }
    }

    /* =========================
     * LOGIN (AJAX ONLY)
     * ========================= */
    $(document).on("submit", "#orbitur-login-form", function (e) {
      e.preventDefault();

      const $form = $(this);
      $form.find(".orbitur-form-msg").hide(); // Clear previous messages

      // Client-side validation
      if (!$form[0].checkValidity()) {
        showMessage(
          $form,
          "Por favor, preencha todos os campos obrigatórios.",
          "error"
        );
        return;
      }

      setLoading($form, true);

      // STEP 1: get fresh nonce
      $.post(orbitur_ajax.ajax_url, {
        action: "orbitur_get_form_nonce",
      }).done(function (n) {
        if (!n.success) {
          showMessage($form, "Erro de segurança.", "error");
          setLoading($form, false);
          return;
        }

        // STEP 2: submit login with fresh nonce
        var payload = {
          action: "orbitur_login_ajax",
          nonce: n.data.nonce,
          email: $form.find('[name="email"]').val(),
          pw: $form.find('[name="pw"]').val(),
          remember: $form.find('[name="remember"]').is(":checked") ? 1 : 0,
        };
        // debug: log presence of fields (never log password value in production)
        // Login logic
        $.post(orbitur_ajax.ajax_url, payload).done(function (res) {
          if (res.success) {
            window.location.href = res.data.redirect;
          } else {
            showMessage($form, getErrorMessage(res, "Login falhou"), "error");
            setLoading($form, false);
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
      $form.find(".orbitur-form-msg").hide();

      // Client-side validation
      if (!$form[0].checkValidity()) {
        showMessage($form, "Por favor, preencha o email.", "error");
        return;
      }

      setLoading($form, true);

      $.post(orbitur_ajax.ajax_url, {
        action: "orbitur_forgot_password",
        nonce: orbitur_ajax.nonce,
        email: $("#forgot-email").val(),
      })
        .done(function (res) {
          if (!res.success) {
            showMessage(
              $form,
              getErrorMessage(res, "Erro ao enviar email."),
              "error"
            );
            setLoading($form, false);
            return;
          }
          showMessage(
            $form,
            res.data.message || "Email enviado com sucesso.",
            "success"
          );
          setLoading($form, false);
        })
        .fail(function () {
          showMessage($form, "Erro de rede.", "error");
          setLoading($form, false);
        });
    });

    /* =========================
     * REGISTER (AJAX ONLY)
     * ========================= */
    $(document).on("submit", "#orbitur-register-form", function (e) {
      e.preventDefault();

      var $form = $(this);

      // Client-side validation
      if (!$form[0].checkValidity()) {
        showMessage(
          $form,
          "Por favor, preencha todos os campos obrigatórios.",
          "error"
        );
        return;
      }

      // Explicit Privacy Policy Check
      var $privacy = $form.find('input[name="privacy"]');
      if ($privacy.length && !$privacy.is(":checked")) {
        showMessage(
          $form,
          "É obrigatório aceitar a Política de Privacidade.",
          "error"
        );
        return;
      }

      setLoading($form, true);

      // STEP 1: get fresh nonce (cache-safe)
      $.post(orbitur_ajax.ajax_url, {
        action: "orbitur_get_form_nonce",
      }).done(function (n) {
        try {
          if (!n || !n.success) {
            showMessage($form, "Erro de segurança.", "error");
            setLoading($form, false);
            return;
          }

          // STEP 2: build payload with fresh nonce
          const data = {
            action: "orbitur_register_ajax",
            nonce: n.data.nonce,
          };

          // Ensure phone is submitted (Raw)
          $form.serializeArray().forEach(function (field) {
            data[field.name] = field.value;
          });
          // We removed the helper, so data['phone'] is already set by serializeArray
          // Just ensuring logic remains clean if we need specific handling later.
          data["phone"] = $form.find('[name="phone"]').val();

          // STEP 3: submit register
          $.post(orbitur_ajax.ajax_url, data)
            .done(function (res) {
              if (!res || !res.success) {
                showMessage(
                  $form,
                  getErrorMessage(res, "Erro ao criar conta"),
                  "error"
                );
                setLoading($form, false);
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
              setLoading($form, false);
            });
        } catch (e) {
          console.error("Registration error:", e);
          showMessage($form, "Erro interno ao processar registo.", "error");
          setLoading($form, false);
        }
      });
    });

    /* =========================
     * PROFILE UPDATE (MonCompte = source of truth)
     * ========================= */
    $(document).on("submit", "#orbitur-profile-form", function (e) {
      e.preventDefault();

      var $form = $(this);
      setLoading($form, true);

      const payload = {
        action: "orbitur_update_profile",
        nonce: orbitur_dashboard.nonce,
        phone: $form.find('[name="phone"]').val(),
        address: $form.find('[name="address"]').val(),
        zipcode: $form.find('[name="zipcode"]').val(),
        city: $form.find('[name="city"]').val(),
        country: $form.find('[name="country"]').val(),
      };

      $.post(orbitur_ajax.ajax_url, payload)
        .done(function (res) {
          if (!res || !res.success) {
            showMessage(
              $form,
              getErrorMessage(res, "Erro ao guardar perfil."),
              "error"
            );
            setLoading($form, false);
            return;
          }

          // If MonCompte reports no effective change
          if (res.data && res.data.status === "unchanged") {
            showMessage($form, res.data.message, "success");
            setLoading($form, false);
            return;
          }

          // Success → reload profile from MonCompte
          showMessage(
            $form,
            res.data?.message || "Perfil atualizado com sucesso.",
            "success"
          );

          // Reload profile fields from API
          if (typeof reloadProfileFromAPI === "function") {
            reloadProfileFromAPI();
          }

          setLoading($form, false);
        })
        .fail(function () {
          showMessage($form, "Erro de rede.", "error");
          setLoading($form, false);
        });
    });
  });
})(jQuery);
