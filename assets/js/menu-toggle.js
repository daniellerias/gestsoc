// Menu toggle: shows/hides mobile menu
document.addEventListener('DOMContentLoaded', function(){
  var toggle = document.getElementById('menu-toggle');
  var menu = document.getElementById('menu');
  if(toggle && menu){
    toggle.addEventListener('click', function(e){
      e.stopPropagation();
      var open = menu.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function(ev){
      if(!menu.contains(ev.target) && menu.classList.contains('open')){
        menu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }
});
