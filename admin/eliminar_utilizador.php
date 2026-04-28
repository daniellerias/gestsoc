<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);

            header('Location: gerir_utilizadores.php?msg=Utilizador eliminado com sucesso!');
            exit;
        } catch (PDOException $e) {
            $error = htmlspecialchars($e->getMessage());
            header("Location: gerir_utilizadores.php?msg=Erro ao eliminar utilizador: $error");
            exit;
        }
    } else {
        header('Location: gerir_utilizadores.php?msg=ID de utilizador inválido.');
        exit;
    }
} else {
    header('Location: gerir_utilizadores.php');
    exit;
}
