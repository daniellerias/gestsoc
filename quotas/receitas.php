<?php
require_once '../config.php';
require_once '../templates/header.php';

// Buscar anos disponíveis
$stmtAnos = $pdo->query('SELECT DISTINCT referente_ano FROM pagamentos ORDER BY referente_ano DESC');
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);
$anoSelecionado = isset($_GET['ano']) ? $_GET['ano'] : ($anos[0] ?? date('Y'));

// Paginação
$REGISTOS_POR_PAGINA = isset($REGISTOS_POR_PAGINA) ? (int)$REGISTOS_POR_PAGINA : 25;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $REGISTOS_POR_PAGINA;

// Buscar total de registos para paginação
if ($anoSelecionado === 'todos') {
    $stmtCount = $pdo->query('SELECT COUNT(*) FROM pagamentos');
    $totalRegistos = $stmtCount->fetchColumn();
} else {
    $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM pagamentos WHERE referente_ano = ?');
    $stmtCount->execute([$anoSelecionado]);
    $totalRegistos = $stmtCount->fetchColumn();
}
$totalPaginas = ceil($totalRegistos / $REGISTOS_POR_PAGINA);

// Buscar receitas por ano ou todos
if ($anoSelecionado === 'todos') {
    $stmt = $pdo->prepare('SELECT p.*, a.nome_completo, q.nome AS nome_quota FROM pagamentos p
    JOIN socios a ON p.associado_id = a.id
        JOIN tipos_quotas q ON p.quota_id = q.id
        ORDER BY p.data_pagamento DESC
        LIMIT ' . $REGISTOS_POR_PAGINA . ' OFFSET ' . $offset);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare('SELECT p.*, a.nome_completo, q.nome AS nome_quota FROM pagamentos p
    JOIN socios a ON p.associado_id = a.id
        JOIN tipos_quotas q ON p.quota_id = q.id
        WHERE p.referente_ano = ?
        ORDER BY p.data_pagamento DESC
        LIMIT ' . $REGISTOS_POR_PAGINA . ' OFFSET ' . $offset);
    $stmt->execute([$anoSelecionado]);
}
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total
$total = 0;
foreach ($pagamentos as $pag) {
    $total += $pag['montante'];
}
?>
<div class="container">
    <h1>Receitas</h1>
    <form method="get" style="margin-bottom:1em;">
        <label for="ano">Ano:</label>
        <select name="ano" id="ano" onchange="this.form.submit()">
            <option value="todos" <?= $anoSelecionado === 'todos' ? 'selected' : '' ?>>Todos</option>
            <?php foreach ($anos as $ano): ?>
                <option value="<?= $ano ?>" <?= $ano == $anoSelecionado ? 'selected' : '' ?>><?= $ano ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if ($totalPaginas > 1): ?>
        <nav class="pagination" style="margin-bottom:1em; text-align:left;">
            <?php if ($pagina > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" class="section_button">&laquo; Anterior</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" class="section_button" style="<?= $i == $pagina ? 'font-weight:bold; background:#e0e0e0;' : '' ?>"> <?= $i ?> </a>
            <?php endfor; ?>
            <?php if ($pagina < $totalPaginas): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" class="section_button">Próxima &raquo;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
    <table cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Data do Pagamento</th>
                <th>Sócio</th>
                <th>Tipo de Quota</th>
                <th>Meses Pagos</th>
                <th>Valor Total</th>
                <th>Método de Pagamento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagamentos as $pag): ?>
                <?php
                // Extrair meses pagos como array de inteiros
                $mesesPagos = array_filter(explode(',', $pag['meses']), function($m) { return is_numeric($m) && $m >= 1 && $m <= 12; });
                $numMeses = count($mesesPagos);
                ?>
                <tr>
                    <td><?= htmlspecialchars($pag['data_pagamento']) ?></td>
                    <td><?= htmlspecialchars($pag['nome_completo']) ?></td>
                    <td><?= htmlspecialchars($pag['nome_quota']) ?></td>
                    <td><?= $numMeses ?></td>
                    <td><?= number_format($pag['montante'], 2, ',', '.') ?> €</td>
                    <td><?= htmlspecialchars($pag['metodo_pagamento']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;">Total:</th>
                <th colspan="2" style="text-align:left; color:green; font-size:1.1em;">
                    <?= number_format($total, 2, ',', '.') ?> €
                </th>
            </tr>
        </tfoot>
    </table>
    <?php if ($totalPaginas > 1): ?>
        <nav class="pagination" style="margin-top:1em; text-align:left;">
            <?php if ($pagina > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" class="section_button">&laquo; Anterior</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" class="section_button" style="<?= $i == $pagina ? 'font-weight:bold; background:#e0e0e0;' : '' ?>"> <?= $i ?> </a>
            <?php endfor; ?>
            <?php if ($pagina < $totalPaginas): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" class="section_button">Próxima &raquo;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>
<div class="spacer"></div>
<?php require_once '../templates/footer.php'; ?>
