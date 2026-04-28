<?php
require_once '../config.php';
require_once '../templates/header.php';

$id = $_GET['id'] ?? null;
$mensagem = '';
$user = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo '<p>Utilizador não encontrado.</p>';
        require_once '../templates/footer.php';
        exit;
    }
} else {
    echo '<p>ID de utilizador não fornecido.</p>';
    require_once '../templates/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username) {
        try {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = :username, nome = :nome, email = :email, password_hash = :password_hash WHERE id = :id");
                $stmt->execute([
                    'username' => $username,
                    'nome' => $nome,
                    'email' => $email,
                    'password_hash' => $hash,
                    'id' => $id
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :username, nome = :nome, email = :email WHERE id = :id");
                $stmt->execute([
                    'username' => $username,
                    'nome' => $nome,
                    'email' => $email,
                    'id' => $id
                ]);
            }
            $mensagem = '<p style="color:green;">Utilizador atualizado com sucesso!</p>';
            // Atualizar dados do utilizador
            $user['username'] = $username;
            $user['nome'] = $nome;
            $user['email'] = $email;
        } catch (PDOException $e) {
            $mensagem = '<p style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    } else {
        $mensagem = '<p style="color:red;">Preencha o campo Utilizador.</p>';
    }
}
?>
<div class="container">
    <h2>Editar Utilizador</h2>
    <?= $mensagem ?>
    <form method="post">
        <label>Utilizador:<br>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </label><br>
        <label>Nome:<br>
            <input type="text" name="nome" value="<?= htmlspecialchars($user['nome']) ?>">
        </label><br>
        <label>Email:<br>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        </label><br>
        <label>Nova Palavra-passe:<br>
            <input type="password" name="password" placeholder="Deixar em branco para manter" style="width: 100%; max-width: 1200px;">
        </label><br><br>
        <button type="submit">💾 Guardar</button><br><br>
        <button type="button"><a href="gerir_utilizadores.php" class="button">⬅️ Voltar atrás</a></button>
    </form>
    <div class="spacer"></div>
</div>
<?php require_once '../templates/footer.php'; ?>
