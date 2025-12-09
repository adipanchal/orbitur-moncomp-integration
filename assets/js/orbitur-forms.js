(function ($) {
  $(function () {
    // Ajax login
    $(document).on("submit", "#orbitur-login-form", function (e) {
      // If form action points to admin-post.php, allow normal submit (server-side).
      // To use AJAX, prevent default and send to admin-ajax.php.
      e.preventDefault();
      var $f = $(this);
      var data = {
        action: "orbitur_login_ajax",
        nonce: orbitur_ajax ? orbitur_ajax.nonce : "",
        email: $f.find('[name="email"]').val(),
        pw: $f.find('[name="pw"]').val(),
        remember: $f.find('[name="remember"]').is(":checked") ? 1 : 0,
      };
      $.post(orbitur_ajax.ajax_url, data, function (resp) {
        if (resp && resp.success) {
          window.location = orbitur_ajax.redirect || "/";
        } else {
          alert(resp && resp.data ? resp.data : "Login failed");
        }
      });
    });

    // Ajax register
    $(document).on("submit", "#orbitur-register-form", function (e) {
      e.preventDefault();
      var $f = $(this);
      var formData = $f.serializeArray();
      var data = {
        action: "orbitur_register_ajax",
        nonce: orbitur_ajax ? orbitur_ajax.nonce : "",
      };
      $.each(formData, function (i, kv) {
        data[kv.name] = kv.value;
      });
      $.post(orbitur_ajax.ajax_url, data, function (resp) {
        if (resp && resp.success) {
          window.location = orbitur_ajax.redirect || "/";
        } else {
          alert(resp && resp.data ? resp.data : "Register failed");
        }
      });
    });
  });
})(jQuery);
