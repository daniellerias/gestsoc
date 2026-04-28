<?php require_once '../config.php'; ?>
<div class="container">
<?php
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'numero_socio';
$ordem_sql = 'a.data_registo DESC';
if ($ordem === 'numero_socio') {
    $ordem_sql = 'CAST(a.numero_socio AS UNSIGNED) ASC';
} elseif ($ordem === 'numero_socio_desc') {
    $ordem_sql = 'CAST(a.numero_socio AS UNSIGNED) DESC';
} elseif ($ordem === 'nome') {
    $ordem_sql = 'a.nome_completo ASC';
}
?>
<?php
$registos_por_pagina = isset($_GET['registos_por_pagina']) && $_GET['registos_por_pagina'] !== 'todos'
    ? max(1, intval($_GET['registos_por_pagina']))
    : (isset($REGISTOS_POR_PAGINA) ? (int)$REGISTOS_POR_PAGINA : 25);
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $registos_por_pagina;

// Filtro (diabéticos / não diabéticos / inscrição ativa / inscrição suspensa)
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

// Se o campo diabetico não estiver ativo no sistema, garantir que o filtro relacionado não seja usado
if ((empty($usar_diabetico) || !$usar_diabetico) && in_array($filtro, ['diabeticos','nao_diabeticos'])) {
    $filtro = 'todos';
}

// Construir cláusulas WHERE conforme filtro selecionado
$where_clauses = [];
if ($filtro === 'diabeticos') {
    $where_clauses[] = 'a.diabetico = 1';
} elseif ($filtro === 'nao_diabeticos') {
    $where_clauses[] = 'a.diabetico = 0';
} elseif ($filtro === 'inscricao_ativa') {
    $where_clauses[] = "a.estado = 'Ativa'";
} elseif ($filtro === 'inscricao_suspensa') {
    $where_clauses[] = "a.estado = 'Suspensa'";
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Buscar total de socios (com filtro) para paginação
$total_stmt = $pdo->query("SELECT COUNT(*) FROM socios a $where_sql");
$total_socios = (int)$total_stmt->fetchColumn();
$total_paginas = $registos_por_pagina > 0 ? ceil($total_socios / $registos_por_pagina) : 1;

// Buscar socios da página com filtro
$sql = "SELECT a.*, q.valor AS valor_quota FROM socios a LEFT JOIN tipos_quotas q ON a.quota_id = q.id $where_sql ORDER BY $ordem_sql LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $registos_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h1>Gestão de Sócios</h1>
    <a href="<?php $base_url ?>criar_socio.php" class="section_button"><i class="fa-solid fa-plus"></i> Novo Sócio</a>
    <a href="imprimir_socios.php?ordem=<?= urlencode($ordem) ?>&filtro=<?= urlencode($filtro) ?>" class="section_button" target="_blank" style="margin-left:8px;"><i class="fa-solid fa-print"></i> Imprimir Listagem</a>
    <h2>Lista de Sócios</h2>
    <!-- Paginação topo -->
    <div style="margin-bottom:16px; text-align:center;">
        <?php if ($total_paginas > 1): ?>
            <nav class="pagination" style="text-align: left;">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>&ordem=<?= $ordem ?>" class="section_button">&laquo; Anterior</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $total_paginas; $p++): ?>
                    <a href="?pagina=<?= $p ?>&ordem=<?= $ordem ?>" class="section_button" style="<?= $p == $pagina ? 'font-weight:bold; background:#e0e0e0;' : '' ?>"> <?= $p ?> </a>
                <?php endfor; ?>
                <?php if ($pagina < $total_paginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&ordem=<?= $ordem ?>" class="section_button">Próxima &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
    <form method="get" style="margin-bottom:16px; text-align:right; display:flex; gap:12px; align-items:center; justify-content:flex-end;">
        <label>Ordenar por:</label>
        <select name="ordem" onchange="this.form.submit()">
            <option value="data_registo" <?= $ordem === 'data_registo' ? 'selected' : '' ?>>Data de Admissão</option>
            <option value="numero_socio" <?= $ordem === 'numero_socio' ? 'selected' : '' ?>>Número de Sócio (Ascendente)</option>
            <option value="numero_socio_desc" <?= $ordem === 'numero_socio_desc' ? 'selected' : '' ?>>Número de Sócio (Descendente)</option>
            <option value="nome" <?= $ordem === 'nome' ? 'selected' : '' ?>>Nome (A-Z)</option>
        </select>
        <?php if (isset($_GET['pagina'])): ?>
            <input type="hidden" name="pagina" value="<?= (int)$_GET['pagina'] ?>">
        <?php endif; ?>
    <label for="registos_por_pagina" style="margin-left:8px;">Mostrar:</label>
    <select name="registos_por_pagina" id="registos_por_pagina" onchange="this.form.submit()">
        <?php
            $opcoes = [25, 50, 100, 300, 'todos'];
            foreach ($opcoes as $opcao) {
                $valor = $opcao === 'todos' ? $total_socios : $opcao;
                $selected = ($registos_por_pagina == $valor) ? 'selected' : '';
                $label = $opcao === 'todos' ? 'Todos' : $opcao;
                echo "<option value=\"$valor\" $selected>$label</option>";
            }
        ?>
    </select>
    
    <!-- Filtro personalizado ao lado do controlo de número de registos -->
    <label for="filtro" style="margin-left:8px;">Filtro:</label>
    <select name="filtro" id="filtro" onchange="this.form.submit()">
        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
        <?php if (!empty($usar_diabetico) && $usar_diabetico): ?>
            <option value="diabeticos" <?= $filtro === 'diabeticos' ? 'selected' : '' ?>>Diabéticos</option>
            <option value="nao_diabeticos" <?= $filtro === 'nao_diabeticos' ? 'selected' : '' ?>>Não Diabéticos</option>
        <?php endif; ?>
        <option value="inscricao_ativa" <?= $filtro === 'inscricao_ativa' ? 'selected' : '' ?>>Inscrição Ativa</option>
        <option value="inscricao_suspensa" <?= $filtro === 'inscricao_suspensa' ? 'selected' : '' ?>>Inscrição Suspensa</option>
    </select>
    
    <!-- Preservar a página atual ao submeter outros controles -->
    <?php if (isset($_GET['pagina'])): ?>
        <input type="hidden" name="pagina" value="<?= (int)$_GET['pagina'] ?>">
    <?php endif; ?>
    </form>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
        <div class="alert alert-success" style="color:green; font-weight:bold; margin-bottom:1em;">Registo eliminado com sucesso.</div>
    <?php endif; ?>
    <table cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Número Sócio</th>
                <?php if (!empty($usar_diabetico)): ?><th class="col-small">Diabético</th><?php endif; ?>
                <th class="col-small">Quota</th>
                <th class="col-small">Data de Admissão</th>
                <th class="col-small">Inscrição</th>
                <th class="col-actions">Acções</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($socios as $assoc): ?>
                <tr>
                    <td><?= htmlspecialchars($assoc['nome_completo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($assoc['numero_socio'] ?? '-') ?></td>
                    <?php if (!empty($usar_diabetico)): ?>
                    <td><?= intval($assoc['diabetico']) ? 'Sim' : 'Não' ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($assoc['valor_quota'] ?? '-') ?>€</td>
                    <td><?= !empty($assoc['data_registo']) ? date('d/m/Y', strtotime($assoc['data_registo'])) : '-' ?></td>
                    <td
                        <?php
                            $estado = $assoc['estado'] ?? '-';
                            if ($estado === 'Suspensa') {
                                $cor_letra = 'color: var(--color-ematraso);';
                            } elseif ($estado === 'Ativa') {
                                $cor_letra = 'color: var(--color-emdia);';
                            } else {
                                $cor_letra = '';
                            }
                            echo $cor_letra ? "style=\"$cor_letra\"" : "";
                        ?>
                    ><?= htmlspecialchars($estado) ?></td>
                    <td>
                        <div class="actions_buttons">
                            <form method="post" action="eliminar_socio.php" onsubmit="return confirm('Tem a certeza que deseja eliminar este sócio?');" style="display:inline; margin-left:0.3em;">
                            <input type="hidden" name="id" value="<?= $assoc['id'] ?>">
                            <button type="submit">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        <form method="get" action="editar_socio.php">
                            <input type="hidden" name="id" value="<?= $assoc['id'] ?>">
                            <button type="submit" >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </form>
                        <button onclick="abrirModalAssociado(<?= $assoc['id'] ?>)" style="margin-left:0.3em;"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Paginação fundo -->
    <div style="margin-top:24px; text-align:center;">
        <?php if ($total_paginas > 1): ?>
            <nav class="pagination" style="text-align: left;">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>&ordem=<?= $ordem ?>" class="section_button">&laquo; Anterior</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $total_paginas; $p++): ?>
                    <a href="?pagina=<?= $p ?>&ordem=<?= $ordem ?>" class="section_button" style="<?= $p == $pagina ? 'font-weight:bold; background:#e0e0e0;' : '' ?>"> <?= $p ?> </a>
                <?php endfor; ?>
                <?php if ($pagina < $total_paginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&ordem=<?= $ordem ?>" class="section_button">Próxima &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
    <!-- Modal para detalhes do sócio -->
    <div id="modal-socio" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div style="background:#fff; width:80vw; height:80vh; position:relative; border-radius:8px; overflow:auto; display:flex; flex-direction:column;">
        <button onclick="fecharModal()" style="position:absolute; top:10px; right:10px; font-size:1.5em; background:none; border:none; cursor:pointer;">&times;</button>
        <iframe id="iframe-socio" src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>
    </div>
    <script src="<?php echo $base_url ?>assets/js/scripts.js"></script>
    </div>
    <div class="spacer"></div>
<?php
