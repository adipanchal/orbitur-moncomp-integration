<?php
if (!defined('ABSPATH'))
    exit;

/**
 * template renderer helper
 */
if (!function_exists('orbitur_render_template')) {
    function orbitur_render_template($tpl, $data = [])
    {
        $file = ORBITUR_PLUGIN_DIR . 'templates/' . $tpl;
        if (!file_exists($file))
            return '';
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}

/* Login shortcode - your existing login template file should output form that posts to admin-post.php */
add_shortcode('orbitur_login_form', function ($atts) {
    if (is_user_logged_in()) {
        return '<p>Já está conectado. <a href="' . esc_url(wp_logout_url(site_url('/area-cliente/'))) . '">Sair</a></p>';
    }
    return orbitur_render_template('tpl-login-form.php');
});

/* Register shortcode */
add_shortcode('orbitur_register_form', function ($atts) {
    if (is_user_logged_in()) {
        return '<p>Já tem conta. <a href="' . esc_url(site_url('/area-cliente/bem-vindo/')) . '">Ir para Área Cliente</a></p>';
    }
    return orbitur_render_template('tpl-register-form.php');
});

/* Area main (welcome) shortcode */
add_shortcode('orbitur_area_main', function ($atts) {
    if (!is_user_logged_in()) {
        // if not logged-in, redirect to login page (server side safety)
        wp_safe_redirect(site_url('/area-cliente/'));
        exit;
    }
    return orbitur_render_template('tpl-area-main.php');
});

/**
 * Shortcode: MonCompte Password Reset Form
 * Usage: [orbitur_reset_password]
 */
add_shortcode('orbitur_reset_password', function () {

    $token = sanitize_text_field($_GET['token'] ?? '');

    if (!$token) {
        return '<p class="orbitur-error">Token inválido ou expirado.</p>';
    }

    ob_start();
    ?>
    <form id="orbitur-reset-password-form" class="orbitur-reset-form">
        <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">

        <div class="form-group">
            <label>Nova palavra-passe</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirmar palavra-passe</label>
            <input type="password" name="password_confirm" required>
        </div>

        <button type="submit">Alterar palavra-passe</button>

        <div class="orbitur-reset-message" style="margin-top:10px;"></div>
    </form>

    <script>
        (function ($) {
            $('#orbitur-reset-password-form').on('submit', function (e) {
                e.preventDefault();

                const pw = $('input[name="password"]').val();
                const pw2 = $('input[name="password_confirm"]').val();

                if (pw !== pw2) {
                    $('.orbitur-reset-message').text('As palavras-passe não coincidem.');
                    return;
                }

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'orbitur_reset_password_token',
                    token: $('input[name="token"]').val(),
                    password: pw
                }, function (res) {
                    if (!res.success) {
                        $('.orbitur-reset-message').text(res.data || 'Erro ao alterar palavra-passe.');
                        return;
                    }

                    $('.orbitur-reset-message').text('Palavra-passe alterada com sucesso.');
                    setTimeout(() => {
                        window.location.href = '<?php echo site_url('/area-cliente/'); ?>';
                    }, 1500);
                });
            });
        })(jQuery);
    </script>
    <?php
    return ob_get_clean();
});