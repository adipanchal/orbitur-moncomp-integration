jQuery(function($){
  $(document).on('submit', '#orbitur-login-form', function(e){
    e.preventDefault();
    var form = $(this);
    var data = { action: 'orbitur_login', nonce: orbitur_ajax.nonce };
    form.find(':input').each(function(){ data[this.name]= $(this).val(); });
    form.find('button').prop('disabled', true);
    $.post(orbitur_ajax.ajax_url, data, function(resp){
      form.find('button').prop('disabled', false);
      if (resp.success) {
        $('.orbitur-login-result').html('<div class="ok">Login bem sucedido. A redirecionar…</div>');
        window.location = resp.data.redirect || window.location.href;
      } else {
        $('.orbitur-login-result').html('<div class="error">'+ (resp.data||'Erro') +'</div>');
      }
    }, 'json').fail(function(){ form.find('button').prop('disabled', false); $('.orbitur-login-result').html('<div class="error">Erro de ligação.</div>'); });
  });

  $(document).on('submit', '#orbitur-register-form', function(e){
    e.preventDefault();
    var form = $(this);
    var data = { action: 'orbitur_register', nonce: orbitur_ajax.nonce };
    form.find(':input').each(function(){ if(this.name) data[this.name]= $(this).val(); });
    form.find('button').prop('disabled', true);
    $.post(orbitur_ajax.ajax_url, data, function(resp){
      form.find('button').prop('disabled', false);
      if (resp.success) {
        $('.orbitur-register-result').html('<div class="ok">Registo OK — a redirecionar…</div>');
        window.location = resp.data.redirect || window.location.href;
      } else {
        $('.orbitur-register-result').html('<div class="error">'+ (resp.data||'Erro') +'</div>');
      }
    }, 'json').fail(function(){ form.find('button').prop('disabled', false); $('.orbitur-register-result').html('<div class="error">Erro de ligação.</div>'); });
  });
});