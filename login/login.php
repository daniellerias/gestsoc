<?php
require_once '../config.php';

session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Utilizador ou palavra-passe inválidos.';
        }
    } else {
        $error = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?> - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
        <meta property="og:image" content="../assets/img/SocGestLogo.png" />
        <script src="../assets/js/voltar-topo.js"></script>
</head>
<body class="login-body">
    <div class="full-vertical">
    <div class="login">
    <img style="max-width: 120px;" src="../assets/img/SocGestLogo.png" alt="Sistema de Gestão de Sócios"><br>
    <h1><?= APP_NAME ?></h1>
    <p><?= APP_FULL_NAME ?></p><br>
    <h3>Iniciar sessão:</h3>
    <div class="login-form">
    <form method="post">
        <label>Utilizador:</label><input type="text" name="username" required><br>
        <label>Palavra-passe: </label><input type="password" name="password" required>
    </div>
    
    <button type="submit">Entrar</button>
    
    <?php if (isset($_GET['msg'])): ?>
    <br><p style="color:green;">✅ <?= htmlspecialchars($_GET['msg']) ?></p><br>
    <?php endif; ?>
    <?php if ($error): ?>
    <br><p style="color:red;">❌ <?= htmlspecialchars($error) ?></p><br>
    <?php endif; ?>

    </form>
    </div>
    </div>
<?php require_once '../templates/footer.php'; ?>
