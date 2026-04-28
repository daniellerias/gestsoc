<?php
require_once '../config.php';

$id = $_POST['id'] ?? $_GET['id'] ?? null;
$mensagem = '';

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tipos_quotas WHERE id = ?");
        $stmt->execute([$id]);
        $mensagem = '<p style="color:green;">Quota eliminada com sucesso.</p>';
    } catch (PDOException $e) {
        $mensagem = '<p style="color:red;">Ocorreu um erro. Não foi possível eliminar a quota.</p>';
    }
} else {
    $mensagem = '<p style="color:red;">Ocorreu um erro. Não foi possível eliminar a quota.</p>';
}

// Se houver mensagem, redireciona para configurar_quotas.php com a mensagem
if ($mensagem) {
    $msg = urlencode(strip_tags($mensagem));
    header('Location: configurar_quotas.php?msg=' . $msg);
    exit;
}
?>
