<?php
require_once '../config.php';
require_once '../templates/header.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtpHost = $_POST['smtp_host'] ?? SMTP_HOST;
    $smtpPort = $_POST['smtp_port'] ?? SMTP_PORT;
    $smtpUser = $_POST['smtp_user'] ?? SMTP_USER;
    $smtpPass = $_POST['smtp_pass'] ?? SMTP_PASS;
    $smtpFromEmail = $_POST['smtp_from_email'] ?? SMTP_FROM_EMAIL;
    $smtpFromName = $_POST['smtp_from_name'] ?? SMTP_FROM_NAME;

    // Ler o arquivo config.php existente
    $configFile = '../config.php';
    $configContent = file_get_contents($configFile);

    // Atualizar apenas os valores SMTP
    $configContent = preg_replace("/define\('SMTP_HOST',\s*'[^']*'\);/", "define('SMTP_HOST', '" . addslashes($smtpHost) . "');", $configContent);
    $configContent = preg_replace("/define\('SMTP_PORT',\s*\d+\);/", "define('SMTP_PORT', " . (int)$smtpPort . ");", $configContent);
    $configContent = preg_replace("/define\('SMTP_USER',\s*'[^']*'\);/", "define('SMTP_USER', '" . addslashes($smtpUser) . "');", $configContent);
    $configContent = preg_replace("/define\('SMTP_PASS',\s*'[^']*'\);/", "define('SMTP_PASS', '" . addslashes($smtpPass) . "');", $configContent);
    $configContent = preg_replace("/define\('SMTP_FROM_EMAIL',\s*'[^']*'\);/", "define('SMTP_FROM_EMAIL', '" . addslashes($smtpFromEmail) . "');", $configContent);
    $configContent = preg_replace("/define\('SMTP_FROM_NAME',\s*'[^']*'\);/", "define('SMTP_FROM_NAME', '" . addslashes($smtpFromName) . "');", $configContent);

    file_put_contents($configFile, $configContent);
    echo '<p style="color: green;">Configurações atualizadas com sucesso!</p>';
    header("Refresh:1");
}
?>
    <div class="container">
    <h1>Configurações de Envio de Emails</h1>
    <form method="POST">
        <label for="smtp_host">SMTP Host:</label><br>
        <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars(SMTP_HOST); ?>" required><br><br>

        <label for="smtp_port">SMTP Port (SSL):</label><br>
        <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars(SMTP_PORT); ?>" required><br><br>

        <label for="smtp_user">SMTP User:</label><br>
        <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars(SMTP_USER); ?>" required><br><br>

        <label for="smtp_pass">SMTP Password:</label><br>
        <input type="password" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars(SMTP_PASS); ?>" required><br><br>

        <label for="smtp_from_email">Endereço Remetente:</label><br>
        <input type="email" id="smtp_from_email" name="smtp_from_email" value="<?php echo htmlspecialchars(SMTP_FROM_EMAIL); ?>" required><br><br>

        <label for="smtp_from_name">Nome Remetente:</label><br>
        <input type="text" id="smtp_from_name" name="smtp_from_name" value="<?php echo htmlspecialchars(SMTP_FROM_NAME); ?>" required><br><br>

        <button type="submit" class="section_button"><i class="fa-solid fa-save"></i> Guardar</button>
    </form>
    <div class="spacer"></div>
    </div>
<?php require_once '../templates/footer.php'; ?>
