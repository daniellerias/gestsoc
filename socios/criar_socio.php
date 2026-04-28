<?php
require_once '../config.php';
require_once '../templates/header.php';

$data_registo_atual = (new DateTime())->format('Y-m-d');
$stmtUltimoSocio = $pdo->query("SELECT MAX(CAST(numero_socio AS UNSIGNED)) AS max_numero FROM socios");
$ultimo_numero = $stmtUltimoSocio->fetchColumn();
$proximo_numero = $ultimo_numero ? $ultimo_numero + 1 : 1;
// Buscar quotas disponíveis
$quotas = $pdo->query("SELECT id, nome, valor FROM tipos_quotas")->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variáveis do formulário
$form = [
    'numero_socio' => $proximo_numero,
    'data_registo' => $data_registo_atual,
    'data_nascimento' => '',
    'bi_cc' => '',
    'nif' => '',
    'nome_completo' => '',
    'morada' => '',
    'telefone' => '',
    'telemovel' => '',
    'email' => '',
    'quota_id' => $quotas[0]['id'] ?? '',
    'anotacoes' => '',
    'diabetico' => '',
];
$mensagem_erro = '';

// Processar submissão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['criar'])) {
        // Preencher variáveis do formulário com os valores submetidos
        $form['numero_socio'] = filter_input(INPUT_POST, 'numero_socio', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['nome_completo'] = filter_input(INPUT_POST, 'nome_completo', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['email'] = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $form['estado'] = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
        if (empty($form['estado'])) {
            $form['estado'] = 'Ativa';
        }
        $form['quota_id'] = filter_input(INPUT_POST, 'quota_id', FILTER_VALIDATE_INT);
        $form['data_registo'] = $_POST['data_registo'];
        $form['data_nascimento'] = $_POST['data_nascimento'] ?? '';
        $form['bi_cc'] = filter_input(INPUT_POST, 'bi_cc', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['nif'] = filter_input(INPUT_POST, 'nif', FILTER_SANITIZE_NUMBER_INT);
        $form['morada'] = filter_input(INPUT_POST, 'morada', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['telefone'] = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['telemovel'] = filter_input(INPUT_POST, 'telemovel', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['anotacoes'] = filter_input(INPUT_POST, 'anotacoes', FILTER_SANITIZE_SPECIAL_CHARS);
        $form['diabetico'] = !empty($usar_diabetico) && isset($_POST['diabetico']) ? 1 : 0;

        // Validações
        if (empty($form['nome_completo'])) {
            $mensagem_erro = "<p style='color:red;'>❌ O campo Nome completo é obrigatório.</p>";
        } elseif (empty($form['bi_cc'])) {
            $mensagem_erro = "<p style='color:red;'>❌ O campo BI/CC é obrigatório.</p>";
        } elseif (empty($form['morada'])) {
            $mensagem_erro = "<p style='color:red;'>❌ O campo Morada é obrigatório.</p>";
        } elseif (!$form['email'] || !$form['quota_id']) {
            $mensagem_erro = "<p style='color:red;'>❌ Dados inválidos. Verifica o email e a quota.</p>";
        } elseif (empty($form['data_nascimento'])) {
            $mensagem_erro = "<p style='color:red;'>❌ O campo Data de Nascimento é obrigatório.</p>";
        } else {
            // Validar data de registo
            $hoje = new DateTime();
            $registo = DateTime::createFromFormat('Y-m-d', $form['data_registo']);
            if (!$registo || $registo > $hoje) {
                $mensagem_erro = "<p style='color:red;'>❌ Data de registo inválida ou futura.</p>";
            } else {
                // Verificar duplicado de número de sócio
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM socios WHERE numero_socio = :numero_socio");
                $stmtCheck->execute(['numero_socio' => $form['numero_socio']]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $mensagem_erro = "<p style='color:red;'>❌ Já existe um associado com o número de sócio {$form['numero_socio']}.</p>";
                } else {
                    // Inserir sócio
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO socios (
                                numero_socio, nome_completo, estado, quota_id, email, data_registo,
                                data_nascimento, bi_cc, nif, morada, telefone, telemovel, anotacoes, diabetico
                            ) VALUES (
                                :numero_socio, :nome_completo, :estado, :quota_id, :email, :data_registo,
                                :data_nascimento, :bi_cc, :nif, :morada, :telefone, :telemovel, :anotacoes, :diabetico
                            )
                        ");
                        $stmt->execute([
                            'numero_socio' => $form['numero_socio'],
                            'nome_completo' => $form['nome_completo'],
                            'estado' => $form['estado'],
                            'quota_id' => $form['quota_id'],
                            'email' => $form['email'],
                            'data_registo' => $form['data_registo'],
                            'data_nascimento' => $form['data_nascimento'],
                            'bi_cc' => $form['bi_cc'],
                            'nif' => $form['nif'],
                            'morada' => $form['morada'],
                            'telefone' => $form['telefone'],
                            'telemovel' => $form['telemovel'],
                            'anotacoes' => $form['anotacoes'],
                            'diabetico' => $form['diabetico']
                        ]);
                        echo "<p style='color:green;'>✅ Sócio registado com sucesso!</p>";
                        // Resetar formulário após sucesso
                        $form = [
                            'numero_socio' => $form['numero_socio'] + 1,
                            'data_registo' => $data_registo_atual,
                            'data_nascimento' => '',
                            'bi_cc' => '',
                            'nif' => '',
                            'nome_completo' => '',
                            'morada' => '',
                            'telefone' => '',
                            'telemovel' => '',
                            'email' => '',
                            'quota_id' => $quotas[0]['id'] ?? '',
                            'anotacoes' => '',
                            'diabetico' => '',
                        ];
                    } catch (PDOException $e) {
                        $mensagem_erro = "<p style='color:red;'>❌ Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
                    }
                }
            }
        }
    }
}
?>
<div class="container">
<h1>Gestão de Sócios</h1>
<h2>Criar Sócio</h2><br>
<?php if (!empty($mensagem_erro)) echo $mensagem_erro; ?>
<div class="socio-form">
<form method="post" style="max-width:900px;">
    <div style="display:flex; gap:16px; width:100%;">
        <label style="flex:1;">Número de Sócio:<br>
            <input type="text" name="numero_socio" maxlength="10" pattern="\d{1,10}" title="Apenas 10 dígitos" required value="<?= htmlspecialchars($form['numero_socio']) ?>">
        </label>
        <label style="flex:1;">Data de Admissão:<br>
            <input type="date" name="data_registo" value="<?= htmlspecialchars($form['data_registo']) ?>" required>
        </label>
    </div>
    <div style="display:flex; gap:16px; width:100%;">
        <label style="flex:1;">Data de Nascimento:<br>
            <input type="date" name="data_nascimento" value="<?= htmlspecialchars($form['data_nascimento']) ?>" required>
        </label>
        <label style="flex:1;">BI/CC:<br>
            <input type="text" name="bi_cc" required value="<?= htmlspecialchars($form['bi_cc']) ?>">
        </label>
        <label style="flex:1;">NIF:<br>
            <input type="number" name="nif" value="<?= htmlspecialchars($form['nif']) ?>">
        </label>
    </div>
    <label style="display:block; width:100%;">Nome completo:<br>
        <input type="text" name="nome_completo" required style="width:98%;" value="<?= htmlspecialchars($form['nome_completo']) ?>">
    </label>
    <label style="display:block; width:100%;">Morada:<br>
        <textarea name="morada" rows="2" style="width:98%;" required><?= htmlspecialchars($form['morada']) ?></textarea>
    </label>
    <label>Telefone: <input type="text" name="telefone" value="<?= htmlspecialchars($form['telefone']) ?>"></label>
    <label>Telemóvel: <input type="text" name="telemovel" value="<?= htmlspecialchars($form['telemovel']) ?>"></label>
    <label>Email: <input type="email" name="email" required value="<?= htmlspecialchars($form['email']) ?>"></label>
    <div style="display:flex; gap:16px; width:100%;">
        <label style="flex:1;">Quota:<br>
            <select name="quota_id" id="quota_id" onchange="atualizarValorQuota()">
                <?php foreach ($quotas as $q): ?>
                    <option value="<?= htmlspecialchars($q['id'], ENT_QUOTES, 'UTF-8') ?>" data-valor="<?= htmlspecialchars($q['valor']) ?>" <?= $form['quota_id'] == $q['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($q['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label style="flex:1;">Valor:<br>
            <input type="text" id="valor_quota" name="valor_quota" value="" readonly style="background:#f4f4f4;">
        </label>
    </div>
    <label style="display:block; width:100%;">Anotações:<br>
        <textarea name="anotacoes" rows="4" cols="50" style="width:98%;"><?= htmlspecialchars($form['anotacoes']) ?></textarea>
    </label>
        <?php if (!empty($usar_diabetico)): ?>
        <label style="display:block; width:100%; margin-top:8px;">Diabético:
            <input type="checkbox" name="diabetico" value="1" <?= $form['diabetico'] ? 'checked' : '' ?>>
        </label>
        <?php endif; ?>
    <br><br>
    <button type="button" name="voltar" class="section_button">
        <a href="gerir_socios.php" style="text-decoration:none; color:inherit;">
            <i class="fa-solid fa-arrow-left"></i> Voltar à lista
        </a>
    </button>
    <button type="submit" name="criar" class="section_button">
        <i class="fa-solid fa-floppy-disk"></i> Guardar
    </button>
</form>
</div>
<div class="spacer"></div>
</div>
<script>
function atualizarValorQuota() {
    var select = document.getElementById('quota_id');
    var valorInput = document.getElementById('valor_quota');
    var valor = select.options[select.selectedIndex].getAttribute('data-valor');
    valorInput.value = valor ? valor : '';
}
// Atualizar ao carregar a página
window.addEventListener('DOMContentLoaded', atualizarValorQuota);
document.getElementById('quota_id').addEventListener('change', atualizarValorQuota);
</script>
<?php
require_once '../templates/footer.php';
?>