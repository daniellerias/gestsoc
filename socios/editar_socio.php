<?php
require_once '../config.php';
require_once '../templates/header.php';

// Buscar quotas disponíveis
$quotas = $pdo->query("SELECT id, nome, valor FROM tipos_quotas")->fetchAll(PDO::FETCH_ASSOC);

// Buscar associações disponíveis
$associacoes = $pdo->query("SELECT id, nome FROM associacoes")->fetchAll(PDO::FETCH_ASSOC);

$assoc = null;
$resultados_nome = [];
$mensagem = '';
$numero_socio = '';
$id_associado = null;

// Verificar se o ID foi passado via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_associado = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $stmt = $pdo->prepare("SELECT * FROM socios WHERE id = :id");
    $stmt->execute(['id' => $id_associado]);
    $assoc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assoc) {
        $mensagem = "<p style='color:red;'>❌ Associado com ID " . htmlspecialchars($id_associado) . " não encontrado.</p>";
    } else {
        $numero_socio = $assoc['numero_socio'];
    }
}

// Processar submissão de número de sócio
// Processar pesquisa por nome
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesquisar'])) {
    $numero_socio_raw = trim($_POST['numero_socio'] ?? '');
    $nome_pesquisa = trim($_POST['nome_pesquisa'] ?? '');
    $resultados_nome = [];
    if ($nome_pesquisa !== '') {
        // Pesquisa por palavras em qualquer parte do nome_completo
        $palavras = preg_split('/\s+/', $nome_pesquisa);
        $where = [];
        $params = [];
        foreach ($palavras as $i => $palavra) {
            $where[] = "nome_completo LIKE :palavra$i COLLATE utf8_general_ci";
            $params["palavra$i"] = "%" . $palavra . "%";
        }
    $sql = "SELECT * FROM socios WHERE " . implode(" AND ", $where) . " ORDER BY nome_completo ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados_nome = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$resultados_nome) {
            $mensagem .= "<p style='color:red;'>❌ Nenhum sócio encontrado com esse nome.</p>";
        }
    } elseif ($numero_socio_raw !== '') {
        if (!preg_match('/^\d+$/', $numero_socio_raw)) {
            $mensagem = "<p style='color:red;'>❌ O número de sócio deve conter apenas dígitos.</p>";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM socios WHERE numero_socio = :numero_socio");
            $stmt->execute(['numero_socio' => $numero_socio_raw]);
            $assoc = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$assoc) {
                $mensagem = "<p style='color:red;'>❌ Nº de associado não existe.</p>";
            }
        }
    }
}

// Processar submissão de número de sócio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $numero_socio_raw = $_POST['numero_socio'] ?? '';
    $numero_socio = trim($numero_socio_raw);

    if (!preg_match('/^\d+$/', $numero_socio)) {
        $mensagem = "<p style='color:red;'>❌ O número de sócio deve conter apenas dígitos.</p>";
    } else {
    $stmt = $pdo->prepare("SELECT * FROM socios WHERE numero_socio = :numero_socio");
        $stmt->execute(['numero_socio' => $numero_socio]);
        $assoc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assoc) {
            $mensagem = "<p style='color:red;'>❌ Nº de sócio não existe.</p>";
        }
    }
}

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $numero_socio = filter_input(INPUT_POST, 'numero_socio', FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_completo = filter_input(INPUT_POST, 'nome_completo', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
    $quota_id = filter_input(INPUT_POST, 'quota_id', FILTER_VALIDATE_INT);
    $data_registo = $_POST['data_registo'];

    // Novos campos
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    $bi_cc = filter_input(INPUT_POST, 'bi_cc', FILTER_SANITIZE_SPECIAL_CHARS);
    $nif = filter_input(INPUT_POST, 'nif', FILTER_SANITIZE_NUMBER_INT);
    $morada = filter_input(INPUT_POST, 'morada', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $telemovel = filter_input(INPUT_POST, 'telemovel', FILTER_SANITIZE_SPECIAL_CHARS);
    $anotacoes = filter_input(INPUT_POST, 'anotacoes', FILTER_SANITIZE_SPECIAL_CHARS);
    $diabetico = !empty($usar_diabetico) && isset($_POST['diabetico']) ? 1 : 0;
    $associacao_id = filter_input(INPUT_POST, 'associacao_id', FILTER_VALIDATE_INT);


    $registo = DateTime::createFromFormat('Y-m-d', $data_registo);
    $hoje = new DateTime();
    if (!$registo || $registo > $hoje || !$email || !$quota_id) {
        $mensagem = "<p style='color:red;'>❌ Dados inválidos.</p>";
    } else {
    // Periodicidade removida: todas as quotas são mensais
        // Atualizar dados do associado (sem campos de quotas mensais)
        $stmt = $pdo->prepare("
            UPDATE socios
            SET numero_socio = :numero_socio,
                nome_completo = :nome_completo,
                email = :email,
                estado = :estado,
                quota_id = :quota_id,
                data_registo = :data_registo,
                data_nascimento = :data_nascimento,
                bi_cc = :bi_cc,
                nif = :nif,
                morada = :morada,
                telefone = :telefone,
                telemovel = :telemovel,
                anotacoes = :anotacoes,
                associacao_id = :associacao_id,
                diabetico = :diabetico
            WHERE id = :id
        ");
        $params = [
            'numero_socio' => $numero_socio,
            'nome_completo' => $nome_completo,
            'email' => $email,
            'estado' => $estado,
            'quota_id' => $quota_id,
            'data_registo' => $data_registo,
            'data_nascimento' => $data_nascimento,
            'bi_cc' => $bi_cc,
            'nif' => $nif,
            'morada' => $morada,
            'telefone' => $telefone,
            'telemovel' => $telemovel,
            'anotacoes' => $anotacoes,
            'associacao_id' => $associacao_id,
            'diabetico' => $diabetico,
            'id' => $id
        ];
        $stmt->execute($params);



    // Atualizar pagamentos socios
        $stmtPag = $pdo->prepare("UPDATE pagamentos SET associado_id = :id, quota_id = :quota_id WHERE associado_id = :id");
        $stmtPag->execute([
            'id' => $id,
            'quota_id' => $quota_id
        ]);

        // Recarregar dados
    $stmt = $pdo->prepare("SELECT * FROM socios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $assoc = $stmt->fetch(PDO::FETCH_ASSOC);
        $mensagem = "<p style='color:green;'>✅ Dados atualizados.</p>";
        $numero_socio = $assoc['numero_socio'];
    }
}
?>

<?php if (!$id_associado): ?>
<div class="container">
    <h1>Gestão de Sócios</h1>
    <h2><i class="fa-solid fa-magnifying-glass"></i> Procurar Sócio</h2>
    <form method="post" style="margin-bottom:16px;">
        <label>Número de Sócio:
            <input type="text" name="numero_socio" value="<?= htmlspecialchars($numero_socio) ?>">
        </label>
        <label style="margin-left:16px;">Nome:
            <input type="text" name="nome_pesquisa" value="<?= isset($_POST['nome_pesquisa']) ? htmlspecialchars($_POST['nome_pesquisa']) : '' ?>">
        </label>
        <button type="submit" name="pesquisar" class="section_button">
            <i class="fa-solid fa-magnifying-glass"></i> Pesquisar
        </button>
    </form>
    <button type="button" name="voltar" class="section_button">
        <a href="gerir_socios.php"><i class="fa-solid fa-arrow-left"></i> Voltar à lista</a>
    </button>
    <?php if (!empty($resultados_nome)): ?>
        <div style="margin-top:16px;">
            <h3>Resultados da pesquisa por nome:</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f4f4f4;">
                        <th>Nº Sócio</th>
                        <th>Nome Completo</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultados_nome as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['numero_socio']) ?></td>
                        <td><?= htmlspecialchars($a['nome_completo']) ?></td>
                        <td><?= htmlspecialchars($a['email']) ?></td>
                        <td>
                            <a href="editar_socio.php?id=<?= $a['id'] ?>" class="section_button">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?= $mensagem ?>

<?php if ($assoc): ?>
    <div class="container">
    <?php if ($id_associado): ?>
        <h1>Gestão de Sócios</h1>
    <?php endif; ?>
    <h2>Editar Sócio</h2>
    <div class="associado-form">
    <form method="post" style="max-width:900px;">
        <input type="hidden" name="id" value="<?= $assoc['id'] ?>">
        <div style="display:flex; width:100%;">
            <label style="flex:1;">Número de Sócio:<br>
                    <input type="text" maxlength="10" pattern="\d{1,10}" name="numero_socio" value="<?= htmlspecialchars($assoc['numero_socio']) ?>" required readonly style="background:#f4f4f4;">
            </label>
            <label style="flex:1;">Data de Admissão:<br>
                <input type="date" name="data_registo" value="<?= htmlspecialchars($assoc['data_registo']) ?>" required>
            </label>
            <label style="flex:1;">Estado da Inscrição:<br>
                <select name="estado">
                    <?php foreach (["Ativa", "Suspensa"] as $estado_op): ?>
                        <option value="<?= $estado_op ?>" <?= $assoc['estado'] === $estado_op ? 'selected' : '' ?>>
                            <?= $estado_op ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div style="display:flex; width:100%;">
            <label style="flex:1;">Data de Nascimento:<br>
                <input type="date" name="data_nascimento" value="<?= htmlspecialchars($assoc['data_nascimento'] ?? '') ?>">
            </label>
            <label style="flex:1;">BI/CC:<br>
                <input type="text" name="bi_cc" value="<?= htmlspecialchars($assoc['bi_cc'] ?? '') ?>">
            </label>
            <label style="flex:1;">NIF:<br>
                <input type="number" name="nif" value="<?= htmlspecialchars($assoc['nif'] ?? '') ?>">
            </label>
        </div>
        <label style="display:block; width:100%;">Nome Completo:<br>
            <input type="text" name="nome_completo" value="<?= htmlspecialchars($assoc['nome_completo']) ?>" required style="width:98%;">
        </label>
        <label style="display:block; width:100%;">Morada:<br>
            <textarea name="morada" rows="2" style="width:98%;"><?= htmlspecialchars($assoc['morada'] ?? '') ?></textarea>
        </label>
        <label>Telefone: <input type="text" name="telefone" value="<?= htmlspecialchars($assoc['telefone'] ?? '') ?>"></label>
        <label>Telemóvel: <input type="text" name="telemovel" value="<?= htmlspecialchars($assoc['telemovel'] ?? '') ?>"></label>
        <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($assoc['email']) ?>" required></label>
        <div style="display:flex; width:100%;">
            <label style="flex:1;">Quota:
                <select name="quota_id" id="quota_id" onchange="atualizarValorQuota()">
                    <?php foreach ($quotas as $q): ?>
                        <option value="<?= $q['id'] ?>" data-valor="<?= htmlspecialchars($q['valor']) ?>" <?= $assoc['quota_id'] == $q['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="flex:1;">Valor:<br>
                <input type="text" id="valor_quota" name="valor_quota" value="" readonly style="background:#f4f4f4;">
            </label>
        </div>
        <label style="display:block; width:100%;">Anotações:<br>
            <textarea name="anotacoes" rows="4" cols="50" style="width:98%;"><?= htmlspecialchars($assoc['anotacoes'] ?? '') ?></textarea>
        </label>
            <?php if (!empty($usar_diabetico)): ?>
            <label style="display:block; width:100%; margin-top:8px;">Diabético:
                <input type="checkbox" name="diabetico" value="1" <?= !empty($assoc['diabetico']) ? 'checked' : '' ?>>
            </label>
            <?php endif; ?>
        <script>
        function atualizarValorQuota() {
            var select = document.getElementById('quota_id');
            var valor = select.options[select.selectedIndex].getAttribute('data-valor');
            document.getElementById('valor_quota').value = valor ? valor.replace('.', ',') + ' €' : '';
        }
        window.onload = atualizarValorQuota;
        </script>

        <br><br>
        <button type="button" name="voltar" class="section_button">
            <a href="gerir_socios.php"><i class="fa-solid fa-arrow-left"></i> Voltar à lista</a>
        </button>
        <button type="submit" name="editar" class="section_button">
            <i class="fa-solid fa-floppy-disk"></i> Guardar
        </button>
        <button type="button" name="eliminar" class="section_button">
            <a href="eliminar_socio.php?id=<?= $assoc['id'] ?>" onclick="return confirm('Tem a certeza que deseja eliminar este associado?');">
                <i class="fa-solid fa-trash"></i> Eliminar
            </a>
        </button>
    </form>
    </div>
    </div>
    <div class="spacer"></div>
<?php endif;
require_once '../templates/footer.php';
?>
