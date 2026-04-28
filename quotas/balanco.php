<?php
require_once '../config.php';
require_once '../templates/header.php';


// Buscar anos disponíveis
$stmtAnos = $pdo->query('SELECT DISTINCT referente_ano FROM pagamentos ORDER BY referente_ano DESC');
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);
$anoSelecionado = isset($_GET['ano']) ? $_GET['ano'] : ($anos[0] ?? date('Y'));

// Calcular total recebido de quotas no ano selecionado
if ($anoSelecionado === 'todos') {
    $stmtRecebido = $pdo->query('SELECT SUM(montante) FROM pagamentos');
    $totalRecebido = $stmtRecebido->fetchColumn();
} else {
    $stmtRecebido = $pdo->prepare('SELECT SUM(montante) FROM pagamentos WHERE referente_ano = ?');
    $stmtRecebido->execute([$anoSelecionado]);
    $totalRecebido = $stmtRecebido->fetchColumn();
}
$totalRecebido = $totalRecebido ?: 0;

// Calcular total por cobrar
// 1. Buscar todos os socios e suas quotas (ativos)
$stmtAssoc = $pdo->query("SELECT a.id, a.numero_socio, a.nome_completo, a.quota_id, q.valor, a.estado FROM socios a JOIN tipos_quotas q ON a.quota_id = q.id");
$socios = $stmtAssoc->fetchAll(PDO::FETCH_ASSOC);

// Calcular valor total de quotas a cobrar por ano (apenas socios ativos)
$totalQuotasAno = 0;
foreach ($socios as $assoc) {
    $estado = strtolower((string)($assoc['estado'] ?? ''));
    if ($estado === 'suspenso' || $estado === 'suspensa') continue;
    if ($assoc['valor'] > 0) {
        $totalQuotasAno += 12 * $assoc['valor'];
    }
}

// 2. Para cada associado, calcular meses pagos no ano selecionado
$totalPorCobrar = 0;
foreach ($socios as $assoc) {
    $estado = strtolower((string)($assoc['estado'] ?? ''));
    if ($estado === 'suspenso' || $estado === 'suspensa') continue;
    // Buscar meses pagos deste associado no ano
    if ($anoSelecionado === 'todos') {
        $stmtPag = $pdo->prepare('SELECT meses FROM pagamentos WHERE associado_id = ?');
        $stmtPag->execute([$assoc['id']]);
    } else {
        $stmtPag = $pdo->prepare('SELECT meses FROM pagamentos WHERE associado_id = ? AND referente_ano = ?');
        $stmtPag->execute([$assoc['id'], $anoSelecionado]);
    }
    $mesesPagos = [];
    foreach ($stmtPag->fetchAll(PDO::FETCH_COLUMN) as $mstr) {
        $mesesPagos = array_merge($mesesPagos, array_filter(explode(',', $mstr), function($m) { return is_numeric($m) && $m >= 1 && $m <= 12; }));
    }
    $mesesPagos = array_unique($mesesPagos);
    $numPagos = count($mesesPagos);
    $numPorCobrar = 12 - $numPagos;
    if ($assoc['valor'] > 0 && $numPorCobrar > 0) {
        $totalPorCobrar += $numPorCobrar * $assoc['valor'];
    }
}

// Calcular total cobrado por meses no ano selecionado
$totaisPorMes = array_fill(1, 12, 0);
if ($anoSelecionado === 'todos') {
    $stmtPagamentos = $pdo->query('SELECT meses, montante, quota_id FROM pagamentos');
} else {
    $stmtPagamentos = $pdo->prepare('SELECT meses, montante, quota_id FROM pagamentos WHERE referente_ano = ?');
    $stmtPagamentos->execute([$anoSelecionado]);
}
if ($anoSelecionado === 'todos') {
    $pagamentos = $stmtPagamentos->fetchAll(PDO::FETCH_ASSOC);
} else {
    $pagamentos = $stmtPagamentos->fetchAll(PDO::FETCH_ASSOC);
}
// Buscar valor da quota por id
$stmtQuotas = $pdo->query('SELECT id, valor FROM tipos_quotas');
$mapaValorQuota = [];
foreach ($stmtQuotas->fetchAll(PDO::FETCH_ASSOC) as $q) {
    $mapaValorQuota[$q['id']] = $q['valor'];
}
foreach ($pagamentos as $pag) {
    $meses = array_filter(explode(',', $pag['meses']), function($m) { return is_numeric($m) && $m >= 1 && $m <= 12; });
    $valorQuota = isset($mapaValorQuota[$pag['quota_id']]) ? $mapaValorQuota[$pag['quota_id']] : 0;
    foreach ($meses as $m) {
        $totaisPorMes[(int)$m] += $valorQuota;
    }
}
?>
<div class="container">
    <h1>Balanço de Quotas</h1>
    <form method="get" style="margin-bottom:1em;">
        <label for="ano">Filtrar por ano:</label>
        <select name="ano" id="ano" onchange="this.form.submit()">
            <option value="todos" <?= $anoSelecionado === 'todos' ? 'selected' : '' ?>>Todos</option>
            <?php foreach ($anos as $ano): ?>
                <option value="<?= $ano ?>" <?= $ano == $anoSelecionado ? 'selected' : '' ?>><?= $ano ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <div style="display:flex; gap:2em; flex-wrap:wrap;">
        <div style="flex:1; min-width:250px; background:#f8f8f8; padding:1em; border-radius:8px;">
            <h2 style="margin-top:0;">Estatísticas Gerais</h2>
            <p style="float: right; text-align:right;"><strong>Valor Total de Quotas a Cobrar:</strong><br><span style="color:#333; font-size:1.1em;">€ <?= number_format($totalQuotasAno, 2, ',', '.') ?></span><br><span style="font-size: small;">(p/Ano - Sócios ativos)</span><br></p>
            
            <p><strong>Total Recebido:</strong> <span style="color:green; font-size:1.2em;">€ <?= number_format($totalRecebido, 2, ',', '.') ?></span></p>
            <p><strong>Total por Cobrar:</strong> <span style="color:red; font-size:1.2em;">€ <?= number_format($totalPorCobrar, 2, ',', '.') ?></span></p>
        </div>
        <div style="flex:1; min-width:250px; background:#f8f8f8; padding:1em; border-radius:8px;">
            <h2 style="margin-top:0;">Total Cobrado por Mês</h2>
            <table cellpadding="6" cellspacing="0" style="width:100%;">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Total Recebido (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nomesMeses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
                    foreach ($totaisPorMes as $num => $valor): ?>
                        <tr>
                            <td><?= $nomesMeses[$num] ?></td>
                            <td><?= number_format($valor, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="spacer"></div>
</div>
<?php require_once '../templates/footer.php'; ?>
