// Script para criar botão de voltar ao topo
backToTop = () => {
    const btn = document.createElement('button');
    btn.id = 'voltar-topo-btn';
    btn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
    btn.className = 'voltar-topo-btn';
    btn.style.display = 'none';
    btn.onclick = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    document.body.appendChild(btn);

    window.addEventListener('scroll', function() {
        btn.style.display = (window.scrollY > 200) ? 'block' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', backToTop);

// Menu toggle: mostrar / esconder menu móvel
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

// Abrir/fechar submenus no mobile ao clicar no botão do menu
function enableMobileDropdowns() {
  function isMobileMenuOpen() {
    return window.innerWidth <= 768 && document.getElementById('menu').classList.contains('open');
  }
  var dropdowns = document.querySelectorAll('#menu .dropdown');
  dropdowns.forEach(function(drop){
    var btn = drop.querySelector('.dropbtn');
    if(btn && !btn.classList.contains('dropdown-listener')) {
      btn.classList.add('dropdown-listener');
      btn.addEventListener('click', function(e){
        if(isMobileMenuOpen()) {
          e.preventDefault();
          e.stopPropagation();
          // Fecha outros abertos
          dropdowns.forEach(function(other){
            if(other !== drop) other.classList.remove('open');
          });
          drop.classList.toggle('open');
        }
      });
    }
  });
}

document.addEventListener('DOMContentLoaded', enableMobileDropdowns);
// Garante que ao abrir o menu mobile, os listeners estão ativos
var menuToggle = document.getElementById('menu-toggle');
if(menuToggle){
  menuToggle.addEventListener('click', function(){
    setTimeout(enableMobileDropdowns, 10);
  });
}

// Modal Socio
function abrirModalAssociado(id) {
  document.getElementById('iframe-socio').src = '../views/detalhes_socio.php?id=' + id;
  document.getElementById('modal-socio').style.display = 'flex';
}
function fecharModal() {
  document.getElementById('modal-socio').style.display = 'none';
  document.getElementById('iframe-socio').src = '';
}

// Função para definir o menu activo com base no nome do ficheiro (ignorando parâmetros)
function setActiveMenu() {
  var menuLinks = document.querySelectorAll('#menu .navbar a, #menu .dropdown-content a');
  // Só o nome do ficheiro, sem parâmetros
  var currentPage = window.location.pathname.split('/').pop().split('?')[0];

  menuLinks.forEach(function(link) {
    link.classList.remove('active', 'menu-active', 'menu-item-active');
    var linkPage = link.pathname.split('/').pop().split('?')[0];
    if (linkPage === currentPage) {
      link.classList.add('active', 'menu-active', 'menu-item-active');
    }
  });
}

document.addEventListener('DOMContentLoaded', setActiveMenu);