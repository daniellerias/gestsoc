<?php
require_once '../config.php';

// Buscar socios e quotas para o formulário
$socios = $pdo->query('SELECT id, nome_completo, quota_id FROM socios ORDER BY CAST(id AS UNSIGNED) ASC')->fetchAll(PDO::FETCH_ASSOC);
$quotas = $pdo->query('SELECT id, nome, valor FROM tipos_quotas ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

// Mapear quota_id por associado para uso em JS
$mapa_associado_quota = [];
foreach ($socios as $a) {
    $mapa_associado_quota[$a['id']] = $a['quota_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $associado_id = (int)($_POST['associado_id'] ?? 0);
    // Preencher quota_id automaticamente a partir do associado
    $quota_id = 0;
    if ($associado_id && isset($mapa_associado_quota[$associado_id])) {
        $quota_id = (int)$mapa_associado_quota[$associado_id];
    }
    $data_pagamento = $_POST['data_pagamento'] ?? '';
    $montante = str_replace(',', '.', $_POST['montante'] ?? '');
    $metodo_pagamento = trim($_POST['metodo_pagamento'] ?? '');
    $referente_ano = isset($_POST['referente_ano']) ? (int)$_POST['referente_ano'] : date('Y');
    $mesesMarcados = isset($_POST['meses']) ? $_POST['meses'] : [];
    $mesesStr = is_array($mesesMarcados) ? implode(',', $mesesMarcados) : '';
    $erros = [];
    if (!$associado_id) $erros[] = 'Associado obrigatório.';
    if (!$quota_id) $erros[] = 'Quota obrigatória para o associado selecionado.';
    if (!$data_pagamento) $erros[] = 'Data obrigatória.';
    if (!is_numeric($montante) || $montante <= 0) $erros[] = 'Montante inválido.';
    if (!$metodo_pagamento) $erros[] = 'Método de pagamento obrigatório.';
    if (empty($erros)) {
        $stmt = $pdo->prepare('INSERT INTO pagamentos (associado_id, quota_id, data_pagamento, montante, metodo_pagamento, referente_ano, meses, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$associado_id, $quota_id, $data_pagamento, $montante, $metodo_pagamento, $referente_ano, $mesesStr]);
        header('Location: gerir_pagamentos.php?msg=criado');
        exit;
    }
}
?>
<?php require_once '../templates/header.php'; ?>
<div class="container">
    <h1>Novo Pagamento</h1>
    <?php if (!empty($erros)): ?>
        <div style="color:red; font-weight:bold;">
            <?= implode('<br>', $erros) ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div style="display: flex; flex-wrap: wrap; gap: 2em;">
            <div style="flex: 1; min-width: 250px;">
                <label>Sócio:</label>
                <select name="associado_id" id="associado_id" required onchange="preencherQuotaPorAssociado()" style="width:100%;">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($socios as $a): ?>
                        <option value="<?= $a['id'] ?>" data-quota="<?= $a['quota_id'] ?>" <?= (isset($associado_id) && $associado_id == $a['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nome_completo']) ?> (<?= htmlspecialchars($a['id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <label>Quota:</label>
                <?php
                $quotaNome = '';
                ?>
                <input type="text" name="quota_nome" id="quota_nome" value="" readonly style="background:#eee; width:100%;">
                <input type="hidden" name="quota_id" id="quota_id" value="">
                <br><br>
                <label>Data de Pagamento:</label>
                <input type="date" name="data_pagamento" value="<?= htmlspecialchars($data_pagamento ?? date('Y-m-d')) ?>" required style="width:100%;">
            </div>
            <div style="flex: 1; min-width: 250px;">
                <label>Montante total (€):</label>
                <input type="text" name="montante" id="montante" value="<?= htmlspecialchars($montante ?? '') ?>" required readonly style="background:#eee; width:100%;">
                <br><br>
                <label>Método de Pagamento:</label>
                <select name="metodo_pagamento" required style="width:100%;">
                    <option value="">-- Selecione --</option>
                    <?php
                    foreach ($metodos as $m) {
                        $sel = (isset($metodo_pagamento) && $metodo_pagamento === $m) ? 'selected' : '';
                        echo "<option value=\"$m\" $sel>$m</option>";
                    }
                    ?>
                </select>
                <br><br>
                <label>Referente ao ano:</label>
                <select name="referente_ano" required style="width:100%;">
                    <?php
                    $anoAtual = date('Y');
                    $anoSelecionado = isset($_POST['referente_ano']) ? (int)$_POST['referente_ano'] : $anoAtual;
                    foreach ($anos_disponiveis as $a) {
                        $sel = ($a == $anoSelecionado) ? 'selected' : '';
                        echo "<option value='$a' $sel>$a</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <label>Meses:</label>
        <br>
        <div class="checkbox-buttons">
            <button type="button" onclick="marcarTodosMeses(true)" style="margin-right:0.5em;">Marcar Todos</button>
            <button type="button" onclick="marcarTodosMeses(false)">Desmarcar Todos</button>
        </div>
        <br>
    <div id="meses-checkboxes" style="display:flex; flex-wrap:wrap; gap:1em; margin-bottom:1.5em;">
            <?php
            $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
            $mesesMarcados = isset($_POST['meses']) ? $_POST['meses'] : [];
            foreach ($meses as $num => $nome) {
                $checked = in_array($num, $mesesMarcados) ? 'checked' : '';
                echo "<label style='font-size:1.2em; min-width:120px; padding:0.5em 0;'><input type='checkbox' name='meses[]' value='$num' $checked style='width:22px; height:22px; vertical-align:middle; margin-right:0.5em;'> $nome</label>";
            }
            ?>
        </div>
        <button type="submit" class="section_button">
            <i class="fa-solid fa-floppy-disk"></i> Guardar
        </button>
        <button type="button" class="section_button" onclick="window.location.href='gerir_pagamentos.php'">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </button>
    </form>
    <div class="spacer"></div>
</div>
<script>
// Mapa de associado para quota_id
var mapaAssociadoQuota = <?php echo json_encode($mapa_associado_quota); ?>;
var quotasInfo = {};
<?php foreach ($quotas as $q) {
    echo "quotasInfo['{$q['id']}'] = {nome: '" . addslashes($q['nome']) . "', valor: '" . str_replace('.', ',', $q['valor']) . "'};\n";
} ?>
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
window.onload = function() {
    preencherQuotaPorAssociado();
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]');
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', calcularMontante);
    });
    document.getElementById('associado_id').addEventListener('change', preencherQuotaPorAssociado);
};
</script>
<?php require_once '../templates/footer.php'; ?>
