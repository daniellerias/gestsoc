<?php
require_once '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: gerir_pagamentos.php?msg=erro');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM pagamentos WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: gerir_pagamentos.php?msg=apagado');
    exit;
} catch (Exception $e) {
    // include header before outputting the error (only in error path)
    require_once '../templates/header.php';
    echo '<p style="color:red;">Erro ao eliminar pagamento: ' . htmlspecialchars($e->getMessage()) . '</p>';
    require_once '../templates/footer.php';
}
