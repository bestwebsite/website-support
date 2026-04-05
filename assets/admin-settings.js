(function($){
  function applyTabs(){
    var $wrap = $('.bws-tab-panels');
    if(!$wrap.length){ return; }
    var active = $wrap.attr('data-active-tab') || 'dashboard';
    $('.bws-tab-panel').hide();
    $('.bws-tab-panel[data-bws-panel="'+active+'"]').show();
  }
  $(document).on('click','.bws-settings-tabs a[data-bws-tab]', function(e){
    // Allow normal navigation; but also do quick client-side switch for snappier UX.
    try {
      var tab = $(this).data('bws-tab');
      $('.bws-tab-panels').attr('data-active-tab', tab);
      applyTabs();
    } catch(err){}
  });
  $(applyTabs);
})(jQuery);