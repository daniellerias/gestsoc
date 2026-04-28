<?php
require_once '../config.php';
require_once '../templates/header.php';

// Ordenação
$ordem = $_GET['ordem'] ?? 'data_desc';
$ordem_sql = 'p.data_pagamento DESC';
if ($ordem === 'data_asc') {
    $ordem_sql = 'p.data_pagamento ASC';
} elseif ($ordem === 'ano_desc') {
    $ordem_sql = 'p.referente_ano DESC, p.data_pagamento DESC';
}

// Query anos disponíveis
$stmtAnos = $pdo->query('SELECT DISTINCT referente_ano FROM pagamentos ORDER BY referente_ano DESC');
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);
$anoSelecionado = isset($_GET['ano']) ? $_GET['ano'] : ($anos[0] ?? date('Y'));

// Filtro de sócio
$filtro_socio = trim($_GET['filtro_socio'] ?? '');
// Filtro de método de pagamento
$filtro_metodo = trim($_GET['filtro_metodo'] ?? '');
// Query de métodos de pagamento
$stmtMetodos = $pdo->query('SELECT DISTINCT metodo_pagamento FROM pagamentos WHERE metodo_pagamento IS NOT NULL AND metodo_pagamento <> "" ORDER BY metodo_pagamento');
$metodos_pagamento = $stmtMetodos->fetchAll(PDO::FETCH_COLUMN);

$where = [];
$params = [];
if ($filtro_metodo !== '') {
    $where[] = 'p.metodo_pagamento = ?';
    $params[] = $filtro_metodo;
}
if ($anoSelecionado !== 'todos') {
    $where[] = 'p.referente_ano = ?';
    $params[] = $anoSelecionado;
}
if ($filtro_socio !== '') {
    $where[] = '(a.numero_socio LIKE ? OR a.nome_completo LIKE ?)';
    $params[] = "%$filtro_socio%";
    $params[] = "%$filtro_socio%";
}
$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Paginação
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $REGISTOS_POR_PAGINA;

// Buscar total de registos para paginação
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM pagamentos p JOIN socios a ON p.associado_id = a.id $whereSQL");
$stmtCount->execute($params);
$totalRegistos = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalRegistos / $REGISTOS_POR_PAGINA);

// Pesquisar pagamentos
$sql = "SELECT p.*, a.nome_completo, a.numero_socio, q.nome AS nome_quota FROM pagamentos p
    JOIN socios a ON p.associado_id = a.id
    JOIN tipos_quotas q ON p.quota_id = q.id
    $whereSQL
    ORDER BY $ordem_sql
    LIMIT $REGISTOS_POR_PAGINA OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Pagamentos</h1>
    <a href="criar_pagamento.php" class="section_button">
        <i class="fa-solid fa-plus"></i> Adicionar Pagamento
    </a>
    <a href="imprimir_pagamentos.php?<?= http_build_query($_GET) ?>" class="section_button" target="_blank" style="margin-left:8px;">
        <i class="fa-solid fa-print"></i> Imprimir Lista
    </a>
    <br><br>
<!-- Paginação -->
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

<!-- Filtro Sócio -->
    <form method="get" style="margin-bottom:1em; display:flex; align-items:center; justify-content:space-between; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <label for="filtro_socio">Sócio:</label>
            <input type="text" name="filtro_socio" id="filtro_socio" value="<?= htmlspecialchars($filtro_socio) ?>" placeholder="Nº ou nome" style="width:160px;">
            <button type="submit">Pesquisar</button>
            <?php if ($filtro_socio !== ''): ?>
                <a href="?<?= http_build_query(array_diff_key($_GET, ['filtro_socio' => ''])) ?>" style="margin-left:8px;" class="section_button">Limpar filtro</a>
            <?php endif; ?>
        </div>
<!-- Ordenar -->            
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="ordem">Ordenar por:</label>
                <select name="ordem" id="ordem" onchange="this.form.submit()">
                    <option value="data_desc" <?= $ordem === 'data_desc' ? 'selected' : '' ?>>Data Pagamento (desc)</option>
                    <option value="data_asc" <?= $ordem === 'data_asc' ? 'selected' : '' ?>>Data Pagamento (asc)</option>
                    <option value="ano_desc" <?= $ordem === 'ano_desc' ? 'selected' : '' ?>>Ano Referência (desc)</option>
                </select>
            </div>
<!-- Filtro Métodos de Pagamento -->            
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="filtro_metodo">Método de Pagamento:</label>
                <select name="filtro_metodo" id="filtro_metodo" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($metodos_pagamento as $m): ?>
                        <option value="<?= htmlspecialchars($m) ?>" <?= $filtro_metodo === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
<!-- Filtro Anos -->            
        <div style="display:flex; align-items:center; gap:8px;">
                <label for="ano">Ano:</label>
                <select name="ano" id="ano" onchange="this.form.submit()">
                    <option value="todos" <?= $anoSelecionado === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <?php foreach ($anos as $ano): ?>
                    <option value="<?= $ano ?>" <?= $ano == $anoSelecionado ? 'selected' : '' ?>><?= $ano ?></option>
                        <?php endforeach; ?>
                </select>
        </div>
        </div>
    </form>
    
    <!-- Tabela de Pagamentos -->
    <table cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Nome (Nº)</th>
                <th class="col-medium">Quota</th>
                <th class="col-medium">Data</th>
                <th class="col-small">Montante</th>
                <th class="col-medium">Método</th>
                <th class="col-small">Ref. Ano</th>
                <th class="col-small">Meses</th>
                <th class="col-actions">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagamentos as $pag): ?>
                <?php
                $mesesPagos = array_filter(explode(',', $pag['meses']), function($m) { return is_numeric($m) && $m >= 1 && $m <= 12; });
                $numMeses = count($mesesPagos);
                ?>
                <tr>
                    <td><?= htmlspecialchars($pag['nome_completo']) ?><div class="numero_socio">(<?= htmlspecialchars($pag['numero_socio']) ?>)</div></td>
                    <td><?= htmlspecialchars($pag['nome_quota']) ?></td>
                    <td><?= htmlspecialchars($pag['data_pagamento']) ?></td>
                    <td><?= number_format($pag['montante'], 2, ',', '.') ?> €</td>
                    <td><?= htmlspecialchars($pag['metodo_pagamento']) ?></td>
                    <td><?= htmlspecialchars($pag['referente_ano']) ?></td>
                    <td><?= $numMeses ?></td>
                    <td><div class="actions_buttons">
                        <button><a href="eliminar_pagamento.php?id=<?= $pag['id'] ?>" title="Eliminar" onclick="return confirm('Tem a certeza que deseja eliminar este pagamento?');"><i class="fa-solid fa-trash"></i></a></button>
                        <button><a href="editar_pagamento.php?id=<?= $pag['id'] ?>" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a></button>
                    </div>
                </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
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
