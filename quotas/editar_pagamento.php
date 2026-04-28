<?php
require_once '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: gerir_pagamentos.php');
    exit;
}

// Buscar dados do pagamento
$stmt = $pdo->prepare('SELECT * FROM pagamentos WHERE id = ?');
$stmt->execute([$id]);
$pagamento = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pagamento) {
    echo '<p style="color:red;">Pagamento não encontrado.</p>';
    require_once '../templates/footer.php';
    exit;
}

$erros = [];
// Garantir que as variáveis estão sempre definidas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_pagamento = $_POST['data_pagamento'] ?? '';
    $montante = str_replace(',', '.', $_POST['montante'] ?? '');
    $metodo_pagamento = trim($_POST['metodo_pagamento'] ?? '');
    $referente_ano = isset($_POST['referente_ano']) ? (int)$_POST['referente_ano'] : ($pagamento['referente_ano'] ?? date('Y'));
    $mesesMarcados = isset($_POST['meses']) ? $_POST['meses'] : (isset($pagamento['meses']) ? explode(',', $pagamento['meses']) : []);
    if (!$data_pagamento) $erros[] = 'Data obrigatória.';
    if (!is_numeric($montante) || $montante <= 0) $erros[] = 'Montante inválido.';
    if (!$metodo_pagamento) $erros[] = 'Método de pagamento obrigatório.';
    if (empty($erros)) {
    $stmt = $pdo->prepare('UPDATE pagamentos SET data_pagamento=?, montante=?, metodo_pagamento=?, referente_ano=?, meses=? WHERE id=?');
    $mesesStr = is_array($mesesMarcados) ? implode(',', $mesesMarcados) : '';
    $stmt->execute([$data_pagamento, $montante, $metodo_pagamento, $referente_ano, $mesesStr, $id]);
    header('Location: gerir_pagamentos.php?msg=editado');
        exit;
    }
} else {
    $referente_ano = isset($pagamento['referente_ano']) ? $pagamento['referente_ano'] : date('Y');
    $mesesMarcados = isset($pagamento['meses']) ? explode(',', $pagamento['meses']) : [];
}
?>
<?php require_once '../templates/header.php'; ?>
<div class="container">
    <h1>Editar Pagamento</h1>
    <?php if (!empty($erros)): ?>
        <div style="color:red; font-weight:bold;">
            <?= implode('<br>', $erros) ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div style="display: flex; flex-wrap: wrap; gap: 2em;">
            <div style="flex: 1; min-width: 250px;">
                <label>Data de Pagamento:</label>
                <input type="date" name="data_pagamento" value="<?= htmlspecialchars($pagamento['data_pagamento']) ?>" required style="width:100%;">
            </div>
            <div style="flex: 1; min-width: 250px;">
                <div style="display: flex; gap: 1em; align-items: flex-end;">
                    <div style="flex:1;">
                        <label>Montante (€):</label>
                        <input type="text" name="montante" id="montante" value="<?= htmlspecialchars($pagamento['montante']) ?>" required readonly style="background:#eee; width:100%;">
                    </div>
                    <div style="flex:1;">
                        <label>Quota:</label>
                        <?php
                        $quotaNome = '';
                        if (isset($pagamento['quota_id'])) {
                            $stmtQN = $pdo->prepare('SELECT nome FROM tipos_quotas WHERE id = ?');
                            $stmtQN->execute([$pagamento['quota_id']]);
                            $quotaNome = $stmtQN->fetchColumn();
                        }
                        ?>
                        <input type="text" value="<?= htmlspecialchars($quotaNome) ?>" readonly style="background:#eee; width:100%;">
                    </div>
                </div>
                <br><br>
                <label>Método de Pagamento:</label>
                <select name="metodo_pagamento" required style="width:100%;">
                    <option value="">-- Selecione --</option>
                    <?php
                    foreach ($metodos as $m) {
                        $sel = ($pagamento['metodo_pagamento'] === $m) ? 'selected' : '';
                        echo "<option value=\"$m\" $sel>$m</option>";
                    }
                    ?>
                </select>
                <br><br>
                <label>Referente ao ano:</label>
                <select name="referente_ano" required style="width:100%;">
                    <?php
                    $anoSelecionado = $referente_ano;
                    foreach ($anos_disponiveis as $a) {
                        $sel = ($a == $anoSelecionado) ? 'selected' : '';
                        echo "<option value='$a' $sel>$a</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <label>Meses pagos neste pagamento:</label>
    <div id="meses-checkboxes" style="display:flex; flex-wrap:wrap; gap:1em; margin-bottom:1em;">
            <?php
            $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
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
// Supondo que o valor da quota está disponível via PHP
var valorQuota = null;
<?php
// Buscar valor da quota do associado
if (isset($pagamento['quota_id'])) {
    $stmtQ = $pdo->prepare('SELECT valor FROM tipos_quotas WHERE id = ?');
    $stmtQ->execute([$pagamento['quota_id']]);
    $valor = $stmtQ->fetchColumn();
    if ($valor !== false) {
        echo "valorQuota = '" . str_replace('.', ',', $valor) . "';\n";
    }
}
?>
function calcularMontante() {
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]:checked');
    var mesesSelecionados = checkboxes.length;
    if (valorQuota && mesesSelecionados > 0) {
        var total = parseFloat(valorQuota.replace(',', '.')) * mesesSelecionados;
        document.getElementById('montante').value = total.toFixed(2).replace('.', ',');
    } else if (valorQuota) {
        document.getElementById('montante').value = valorQuota;
    } else {
        document.getElementById('montante').value = '';
    }
}
window.onload = function() {
    var checkboxes = document.querySelectorAll('#meses-checkboxes input[type=checkbox]');
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', calcularMontante);
    });
    calcularMontante();
};
</script>
<?php require_once '../templates/footer.php'; ?>
