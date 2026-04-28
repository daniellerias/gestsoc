<?php
require_once '../config.php';
require_once '../templates/header.php';

// Optional: Only allow access if logged in as admin
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username && $password) {
        // Check if username exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $error = 'Já existe um utilizador com esse nome.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, nome, email) VALUES (:username, :password_hash, :nome, :email)');
            try {
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $hash,
                    'nome' => $nome,
                    'email' => $email
                ]);
                $success = '✅ Utilizador criado com sucesso!';
            } catch (PDOException $e) {
                $error = 'Erro ao criar utilizador: ' . htmlspecialchars($e->getMessage());
            }
        }
    } else {
        $error = 'Preencha pelo menos utilizador e palavra-passe.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Criar Novo Utilizador</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="container">
    <h1>Gestão de Utilizadores</h1><br><br>
    <h2>Criar Novo Utilizador</h2>
    <?php if ($error): ?>
        <p style="color:red;">❌ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <form method="post">
        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="username">Utilizador:</label><br>
                <input type="text" id="username" name="username" required style="width: 100%;">
            </div>
            <div style="flex: 1;">
                <label for="password">Palavra-passe:</label><br>
                <input type="password" id="password" name="password" required style="width: 100%;">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 2;">
                <label for="name">Nome e Apelido:</label><br>
                <input type="text" id="name" name="name" required style="width: 100%;">
            </div>
            <div style="flex: 2;">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" required style="width: 100%;">
            </div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-start;">            
                <button type="button" class="section_button"><i class="fa-solid fa-arrow-left"></i><a href="gerir_utilizadores.php"> Voltar atrás</a></button>
                <button type="submit" class="section_button"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        </div>
    </form>
</div>
<?php require_once '../templates/footer.php'; ?>
