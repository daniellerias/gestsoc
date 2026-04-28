<?php
require_once '../config.php';
require_once '../templates/header.php';

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirecionar o POST para o script de criação real
    $nome = trim($_POST['nome'] ?? '');
    $valor = str_replace(',', '.', $_POST['valor'] ?? '');
    if ($nome && is_numeric($valor)) {
        try {
            $utilizador = $_SESSION['username'] ?? 'desconhecido';
            $stmt = $pdo->prepare("INSERT INTO tipos_quotas (nome, valor, criado_em, atualizado_em, atualizado_por) VALUES (:nome, :valor, NOW(), NOW(), :utilizador)");
            $stmt->execute([
                'nome' => $nome,
                'valor' => $valor,
                'utilizador' => $utilizador
            ]);
            $mensagem = '<p style="color:green;">Quota criada com sucesso!</p>';
        } catch (PDOException $e) {
            $mensagem = '<p style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    } else {
        $mensagem = '<p style="color:red;">Preencha todos os campos corretamente.</p>';
    }
}
?>
<div class="container">
    <h2>Criar Nova Quota</h2>
    <?= $mensagem ?>
    <form method="post">
        <label>Nome da Quota:<br>
            <input type="text" name="nome" required>
        </label><br>
        <label>Valor (€):<br>
            <input type="number" name="valor" step="0.01" min="0" required>
        </label><br>
    <!-- Periodicidade removida: todas as quotas são mensais -->
        <button type="button"><a href="configurar_quotas.php" class="section_button"><i class="fa-solid fa-arrow-left"></i> Voltar</a></button>
        <button type="submit" class="section_button"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </form>
    <div class="spacer"></div>
</div>
<?php require_once '../templates/footer.php'; ?>
