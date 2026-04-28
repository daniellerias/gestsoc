<?php
require_once '../config.php';
require_once '../templates/header.php';

// Página - lista de socios com checkboxes
// Definir o número de registros por página com opção de filtro
$opcoesRegistros = [25, 50, 100, 300, 'todos'];
$registrosPorPagina = isset($_GET['registros']) && in_array($_GET['registros'], $opcoesRegistros) ? $_GET['registros'] : 50;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * ($registrosPorPagina === 'todos' ? PHP_INT_MAX : $registrosPorPagina);

// Seleção de ano para gerar quotas (usa $anos_disponiveis do config)
$selectedYear = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');
if (!in_array($selectedYear, $anos_disponiveis)) {
    // se ano não estiver na lista, escolher primeiro disponível ou ano actual
    if (!empty($anos_disponiveis)) {
        $selectedYear = (int)$anos_disponiveis[0];
    } else {
        $selectedYear = date('Y');
    }
}

// Consultar o total de registros para calcular o número de páginas
try {
    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM socios WHERE LOWER(COALESCE(estado, '')) != 'suspensa'");
    $totalRegistros = $stmtTotal->fetchColumn();
    $totalPaginas = $registrosPorPagina === 'todos' ? 1 : ceil($totalRegistros / $registrosPorPagina);
} catch (PDOException $e) {
    die("<p style='color:red;'>Erro ao carregar total de sócios: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Atualizar a consulta para incluir LIMIT e OFFSET
try {
    // Ordenar por numero_socio de forma numérica para garantir a sequência correta
    $stmt = $pdo->prepare("SELECT id, numero_socio, nome_completo, data_nascimento, telemovel, telefone, email FROM socios WHERE LOWER(COALESCE(estado, '')) != 'suspensa' ORDER BY CAST(numero_socio AS UNSIGNED) ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $registrosPorPagina === 'todos' ? PHP_INT_MAX : $registrosPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;'>Erro ao carregar sócios: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<div class="container">
    <h1>Gerador de Quotas</h1>
    <div class="info">
    <p>Selecione os sócios que pretende gerar quotas e clique em "Gerar Selecionados". Pode também gerar quotas individualmente para cada sócio no ícone <i class="fa-solid fa-file"></i>. Tenha em atenção que quanto maior a quantidade de sócios selecionados, maior será o tempo necessário para gerar as quotas.</p>
    </div>
    <br>
    <!-- Selector de anos -->
    <?php if (!empty($anos_disponiveis)): ?>
    <form method="get" id="form-anos" style="margin-bottom:1rem;">
        <input type="hidden" name="registros" value="<?= htmlspecialchars($registrosPorPagina) ?>">
        <input type="hidden" name="pagina" value="1">
        <label for="ano">Ano para quotas:</label>
        <select name="ano" id="ano" onchange="document.getElementById('form-anos').submit();">
            <?php foreach ($anos_disponiveis as $ano): ?>
                <option value="<?= intval($ano) ?>" <?= intval($ano) === $selectedYear ? 'selected' : '' ?>><?= intval($ano) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash'])): ?>
        <p><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></p>
    <?php endif; ?>
        <!-- Controles de paginação estilizados -->
    <div style="margin-top:24px; text-align:center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <!-- Navegação de páginas à esquerda -->
            <div class="pagination" style="text-align: left;">
                <?php if ($totalPaginas > 1): ?>
                    <?php if ($paginaAtual > 1): ?>
                        <a href="?pagina=<?= $paginaAtual - 1 ?>&registros=<?= $registrosPorPagina ?>&ano=<?= $selectedYear ?>" class="section_button">&laquo; Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <a href="?pagina=<?= $i ?>&registros=<?= $registrosPorPagina ?>&ano=<?= $selectedYear ?>" class="section_button" style="<?= $i == $paginaAtual ? 'font-weight:bold; background:#e0e0e0;' : '' ?>"> <?= $i ?> </a>
                    <?php endfor; ?>
                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="?pagina=<?= $paginaAtual + 1 ?>&registros=<?= $registrosPorPagina ?>&ano=<?= $selectedYear ?>" class="section_button">Próxima &raquo;</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Filtro de registros por página à direita -->
            <form method="get" style="display: inline;">
                <label for="registros">Registros por página:</label>
                <select name="registros" id="registros" onchange="this.form.submit()">
                    <?php foreach ($opcoesRegistros as $opcao): ?>
                        <option value="<?= $opcao ?>" <?= $opcao == $registrosPorPagina ? 'selected' : '' ?>><?= $opcao ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="pagina" value="1">
            </form>
        </div>
    </div>

    <form method="post" action="gerar_quotas_process.php" id="form-gerar">
        <input type="hidden" name="ano" value="<?= $selectedYear ?>">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="display: flex; align-items: center;">
            <input type="checkbox" id="select_all" style="vertical-align: middle; margin-right: 6px;">
            <label for="select_all" style="margin-bottom:0; vertical-align: middle;">Selecionar todos</label>
            <span style="margin-left:12px;">Selecionados: <span id="selected_count">0</span></span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" id="btn-gerar" disabled class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Gerar Selecionados
                </button>
                <button type="submit" formaction="gerar_quotas_process.php?todos=true" class="btn btn-success">
                    <i class="fa-solid fa-database"></i> Gerar Todos
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover sidebyside-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Sócio (Nº)</th>
                        <th>Ações</th>
                        <th></th>
                        <th>Sócio (Nº)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 0; $i < count($socios); $i += 2):
                        $a1 = $socios[$i];
                        $a2 = ($i + 1 < count($socios)) ? $socios[$i + 1] : null;
                    ?>
                        <tr>
                            <!-- Associado 1 -->
                            <td><input type="checkbox" name="ids[]" value="<?= $a1['id'] ?>" class="cb-id"></td>
                            <td>
                                <?= htmlspecialchars($a1['nome_completo']) ?>
                                <div class="numero_socio">(<?= htmlspecialchars($a1['numero_socio']) ?>)</div>
                            </td>
                            <td>
                                <form method="post" action="gerar_quotas_process.php" style="display:inline">
                                    <input type="hidden" name="ids[]" value="<?= $a1['id'] ?>">
                                    <button type="submit" title="Gerar Quotas">
                                        <i class="fa-solid fa-file" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- Associado 2 -->
                            <?php if ($a2): ?>
                                <td><input type="checkbox" name="ids[]" value="<?= $a2['id'] ?>" class="cb-id"></td>
                                <td>
                                    <?= htmlspecialchars($a2['nome_completo']) ?>
                                    <div class="numero_socio">(<?= htmlspecialchars($a2['numero_socio']) ?>)</div>
                                </td>
                                <td>
                                    <form method="post" action="gerar_quotas_process.php" style="display:inline">
                                        <input type="hidden" name="ids[]" value="<?= $a2['id'] ?>">
                                        <button type="submit" title="Gerar Quotas">
                                        <i class="fa-solid fa-file" aria-hidden="true"></i>
                                    </button>
                                    </form>
                                </td>
                            <?php else: ?>
                                <td></td><td></td><td></td>
                            <?php endif; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </form>
    <div class="spacer"></div>
</div>

<script>

const selectedCountEl = document.getElementById('selected_count');
const btnGerar = document.getElementById('btn-gerar');

function updateCount() {
    const checked = document.querySelectorAll('input.cb-id:checked').length;
    selectedCountEl.textContent = checked;
    btnGerar.disabled = (checked === 0);
}

document.getElementById('select_all').addEventListener('change', function(e){
    const checked = e.target.checked;
    document.querySelectorAll('input.cb-id').forEach(function(cb){ cb.checked = checked; });
    updateCount();
});

document.querySelectorAll('input.cb-id').forEach(function(cb){
    cb.addEventListener('change', function(){
        updateCount();
    });
});
</script>

<?php require_once '../templates/footer.php';
