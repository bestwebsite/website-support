(function(){
  function ready(fn){ if(document.readyState !== 'loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }
  ready(function(){
    var tabs = document.querySelectorAll('.bws-tab[data-bws-tab]');
    var panels = document.querySelectorAll('.bws-panel[data-bws-panel]');
    if(!tabs.length || !panels.length){ return; }

    function activate(slug){
      tabs.forEach(function(t){
        t.classList.toggle('is-active', t.getAttribute('data-bws-tab') === slug);
      });
      panels.forEach(function(p){
        p.classList.toggle('is-active', p.getAttribute('data-bws-panel') === slug);
      });
      // Keep hash updated for convenience
      if (slug) {
        try { history.replaceState(null, '', '#'+slug); } catch(e){}
      }
    }

    tabs.forEach(function(t){
      t.addEventListener('click', function(e){
        e.preventDefault();
        activate(t.getAttribute('data-bws-tab'));
      });
    });

    var initial = (location.hash || '').replace('#','');
    if(!initial || !document.querySelector('.bws-panel[data-bws-panel="'+initial+'"]')){
      initial = tabs[0].getAttribute('data-bws-tab');
    }
    activate(initial);
  });
})();