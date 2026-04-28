// Mapa de associado para quota_id
var mapaAssociadoQuota = {};
var quotasInfo = {};

function preencherQuotaPorAssociado() {
    var selectAssociado = document.getElementById('associado_id');
    var associadoId = selectAssociado.value;
    var quotaIdInput = document.getElementById('quota_id');
    var quotaNomeInput = document.getElementById('quota_nome');
    var quotaId = mapaAssociadoQuota[associadoId] || '';
    quotaIdInput.value = quotaId;
    var quotaNome = '';
    if (quotaId && quotasInfo[quotaId]) {
        quotaNome = quotasInfo[quotaId].nome;
    }
    quotaNomeInput.value = quotaNome;
    // Desmarcar todos os meses ao trocar associado
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]');
    checkboxes.forEach(function(cb) { cb.checked = false; });
    calcularMontante();
}

function calcularMontante() {
    var quotaId = document.getElementById('quota_id').value;
    var valorQuota = '';
    if (quotaId && quotasInfo[quotaId]) {
        valorQuota = quotasInfo[quotaId].valor;
    }
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]:checked');
    var mesesSelecionados = checkboxes.length;
    if (valorQuota && mesesSelecionados > 0) {
        var total = parseFloat(valorQuota.replace(',', '.')) * mesesSelecionados;
        document.getElementById('montante').value = total.toFixed(2).replace('.', ',');
    } else {
        document.getElementById('montante').value = '';
    }
}

function marcarTodosMeses(marcar) {
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]');
    checkboxes.forEach(function(cb) { cb.checked = marcar; });
    calcularMontante();
}

function filtrarAssociados() {
    var input = document.getElementById('pesquisa_associado');
    var filtro = input.value.toLowerCase();
    var select = document.getElementById('associado_id');
    var options = select.options;

    for (var i = 0; i < options.length; i++) {
        var nome = options[i].getAttribute('data-nome') || '';
        var numero = options[i].getAttribute('data-numero') || '';
        if (nome.toLowerCase().includes(filtro) || numero.includes(filtro)) {
            options[i].style.display = '';
        } else {
            options[i].style.display = 'none';
        }
    }
}

window.onload = function() {
    preencherQuotaPorAssociado();
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]');
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', calcularMontante);
    });
    document.getElementById('associado_id').addEventListener('change', preencherQuotaPorAssociado);
};
