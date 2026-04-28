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
