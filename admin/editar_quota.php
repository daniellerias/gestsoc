<?php
require_once '../config.php';
require_once '../templates/header.php';

$id = $_GET['id'] ?? null;
$mensagem = '';
$quota = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM tipos_quotas WHERE id = ?");
    $stmt->execute([$id]);
    $quota = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$quota) {
        echo '<p>Quota não encontrada.</p>';
        require_once '../templates/footer.php';
        exit;
    }
} else {
    echo '<p>ID de quota não fornecido.</p>';
    require_once '../templates/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && $_POST['eliminar'] == '1') {
    try {
        $stmt = $pdo->prepare("DELETE FROM tipos_quotas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        echo '<div class="container"><p style="color:green;">Quota eliminada com sucesso!</p>';
        echo '<a href="configurar_quotas.php" class="button">⬅️ Voltar</a></div>';
        require_once '../templates/footer.php';
        exit;
    } catch (PDOException $e) {
        $mensagem = '<p style="color:red;">Erro ao eliminar a quota: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $valor = str_replace(',', '.', $_POST['valor'] ?? '');
    $utilizador = $_SESSION['username'] ?? 'desconhecido';
    $confirmar = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';

    // Verificar se a quota está a ser usada em pagamentos
    $stmtPag = $pdo->prepare("SELECT COUNT(*) FROM pagamentos WHERE quota_id = ?");
    $stmtPag->execute([$id]);
    $pagamentosUsados = $stmtPag->fetchColumn();

    if ($pagamentosUsados > 0 && $confirmar !== 'sim') {
        // Mostrar aviso e pedir confirmação
        $mensagem = '<div class="alert">Esta quota está a ser utilizada em ' . $pagamentosUsados . ' pagamento(s) registado(s).<br>Esta alteração NÃO afetará os pagamentos existentes.<br><br><form method="post"><input type="hidden" name="nome" value="' . htmlspecialchars($nome) . '"><input type="hidden" name="valor" value="' . htmlspecialchars($valor) . '"><button type="submit" name="confirmar" value="sim">Confirmar</button> <button><a href="editar_quota.php?id=' . $id . '">Cancelar</a></button></form></div><br><br>';
    } else if ($nome && is_numeric($valor)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE tipos_quotas SET nome = :nome, valor = :valor, atualizado_em = NOW(), atualizado_por = :utilizador WHERE id = :id");
            $stmt->execute([
                'nome' => $nome,
                'valor' => $valor,
                'utilizador' => $utilizador,
                'id' => $id
            ]);
            // Não atualizar pagamentos, manter montante original
            $pdo->commit();
            $mensagem = '<p style="color:green;">Quota e pagamentos atualizados com sucesso!</p>';
            $quota['nome'] = $nome;
            $quota['valor'] = $valor;
            $quota['atualizado_em'] = date('Y-m-d H:i:s');
            $quota['atualizado_por'] = $utilizador;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensagem = '<p style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    } else {
        $mensagem = '<p style="color:red;">Preencha todos os campos corretamente.</p>';
    }
}
?>
<div class="container">
    <h2>Editar Quota</h2>
    <?= $mensagem ?>
    <form method="post">
        <label>Nome:<br>
            <input type="text" name="nome" value="<?= htmlspecialchars($quota['nome']) ?>" required>
        </label><br>
        <label>Valor (€):<br>
            <input type="number" name="valor" step="0.01" min="0" value="<?= htmlspecialchars($quota['valor']) ?>" required>
        </label><br>
        <button type="button" class="section_button">
            <a href="configurar_quotas.php"><i class="fa fa-arrow-left"></i> Voltar</a>
        </button>
        <button type="submit" class="section_button"><i class="fa fa-save"></i> Guardar</button>
        <button type="submit" name="eliminar" value="1" class="section_button" onclick="return confirm('Tem a certeza que deseja eliminar esta quota?');"><i class="fa fa-trash"></i> Eliminar</button>
    </form>
    <div class="spacer"></div>
</div>
<?php require_once '../templates/footer.php'; ?>
