<?php
require_once '../config.php';

$id = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if ($id && $id > 0) {
    try {
    $stmt = $pdo->prepare('DELETE FROM socios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        // Redireciona com mensagem de sucesso
    header('Location: gerir_socios.php?msg=eliminado');
        exit;
    } catch (PDOException $e) {
        echo '<p style="color:red;">Erro ao eliminar associado: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a class="section_button" href="gerir_socios.php">Voltar à lista</a>';
        require_once 'templates/footer.php';
        exit;
    }
}
require_once '../templates/header.php';
// Se não for POST/GET válido
header('Location: gerir_socios.php');
exit;
