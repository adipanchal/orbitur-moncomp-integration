/**
 * Initialize intl-tel-input library on phone input fields
 */
(function ($) {
  "use strict";

  $(function () {
    // Initialize on phone input fields
    const phoneInputIds = [
      "#edit-phone", // Dashboard profile edit
      "[name='phone']", // Registration/Login forms
    ];

    phoneInputIds.forEach(function (selector) {
      const phoneInputs = $(selector);

      phoneInputs.each(function () {
        const input = this;
        const iti = window.intlTelInput(input, {
          initialCountry: "PT", // Default to Portugal
          separateDialCode: true,
          preferredCountries: ["pt", "es", "fr"],
          utilsScript:
            "https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/utils.min.js",
        });

        // Store instance on the element for later use
        $(input).data("iti", iti);

        // Optional: Format number on blur
        $(input).on("blur", function () {
          if (iti.isValidNumber()) {
            // Optionally format the number
            // $(input).val(iti.getNumber());
          }
        });
      });
    });

    // Helper function to get full international number
    window.getInternationalPhoneNumber = function (inputSelector) {
      const $input = $(inputSelector);
      const iti = $input.data("iti");
      if (iti && iti.isValidNumber()) {
        return iti.getNumber(intlTelInputUtils.numberFormat.E164);
      }
      return $input.val();
    };

    // Helper function to validate phone number
    window.isValidPhoneNumber = function (inputSelector) {
      const $input = $(inputSelector);
      const iti = $input.data("iti");
      if (iti) {
        return iti.isValidNumber();
      }
      return false;
    };
  });
})(jQuery);
