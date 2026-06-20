<script>
(function($){

  function setLoading($form, on){
    var $btn = $form.find('[type="submit"]');
    $btn.prop('disabled', !!on);
    
    if(on) {
        if(!$btn.data('original-text')) {
            $btn.data('original-text', $btn.html());
        }
        $btn.html('<span class="kmb-spinner"></span> Envoi en cours...');
    } else {
        if($btn.data('original-text')) {
            $btn.html($btn.data('original-text'));
        }
    }
  }

  function setMsg($form, type, text){
    var $box = $form.find('.wpcf7-response-output');
    if(!$box.length){
      $box = $('<div class="wpcf7-response-output"></div>').appendTo($form);
    }
    $box.show().attr('aria-hidden','false');

    $box.removeClass('is-success is-error is-info');
    if(type === 'success') $box.addClass('is-success');
    else if(type === 'error') $box.addClass('is-error');
    else $box.addClass('is-info');

    $box.text(text || '');
  }

  // ✅ Interception soumission Formulaire Contact
  $(document).on('submit', '#kmb-contact-form', function(e){
    e.preventDefault();
    e.stopPropagation();

    var $form = $(this);
    var action = '/publication/ajax/contact_submit.php'; // Endpoint de traitement

    // Serialize
    var payload = $form.serializeArray();

    setLoading($form, true);
    setMsg($form, 'info', 'Envoi en cours...');

    $.ajax({
      url: action,
      method: 'POST',
      data: $.param(payload),
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .done(function(resp){
      if(resp && resp.ok){
        setMsg($form, 'success', resp.message || 'Votre message a été envoyé avec succès !');
        toastDark('success', resp.message || 'Message envoyé', 'Contact');
        $form[0].reset();
        // Reset le champ sujet si "Autre" était sélectionné
        $('#other-subject-group').hide();
      } else {
        setMsg($form, 'error', (resp && resp.message) ? resp.message : 'Une erreur est survenue.');
        toastDark('error', resp.message, 'Erreur');
      }
    })
    .fail(function(xhr){
      setMsg($form, 'error', 'Erreur serveur (' + xhr.status + '). Réessayez.');
    })
    .always(function(){
      setLoading($form, false);
    });

    return false;
  });

  // ✅ Interception submit (même si CF7/Elementor est présent)
  $(document).on('submit', 'form.js-nl-form', function(e){
    e.preventDefault();
    e.stopPropagation();

    var $form = $(this);
    var action = $form.data('action') || '/publication/ajax/newsletter-subscribe';

    // Serialize + source
    var payload = $form.serializeArray();
    if($form.data('source')){
      payload.push({ name:'source', value:String($form.data('source')) });
    }

    setLoading($form, true);
    setMsg($form, 'info', 'Envoi en cours...');

    $.ajax({
      url: action,
      method: 'POST',
      data: $.param(payload),
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .done(function(resp){
      if(resp && resp.ok){
        setMsg($form, 'success', resp.message || 'Abonnement confirmé. Merci !');
        toastDark('success', resp.message, 'Newsletter');
        $form[0].reset();
      } else {
        setMsg($form, 'error', (resp && resp.message) ? resp.message : 'Vérifiez l’e-mail et le consentement.');
        toastDark('error', resp.message, 'Newsletter');
      }
    })
    .fail(function(xhr){
      setMsg($form, 'error', 'Erreur serveur (' + xhr.status + '). Réessayez.');
    })
    .always(function(){
      setLoading($form, false);
    });

    return false;
  });

})(jQuery);
</script>
