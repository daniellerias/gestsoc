<?php
require_once '../config.php';
require_once '../templates/header.php';
require_once '../vendor/PHPMailer/src/PHPMailer.php';
require_once '../vendor/PHPMailer/src/SMTP.php';
require_once '../vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar se o ID foi passado
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('ID inválido.');
}

// Buscar dados do sócio pelo ID
$stmt = $pdo->prepare('SELECT nome_completo, email, id, data_nascimento FROM socios WHERE id = :id');
$stmt->execute(['id' => $id]);
$socio = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$socio) {
    die('Sócio não encontrado.');
}

// Preencher os dados do destinatário
$destinatarioNome = $socio['nome_completo'];
$destinatarioEmail = $socio['email'];

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assunto = $_POST['assunto'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';

    if (empty($assunto) || empty($mensagem)) {
        echo 'Assunto e mensagem são obrigatórios.';
    } else {
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL for port 465
            $mail->Port = SMTP_PORT; // Porta configurada no config.php
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Remetente
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($destinatarioEmail, $destinatarioNome);

            // Conteúdo do email
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = nl2br(htmlspecialchars($mensagem));

            $mail->send();
            echo 'Email enviado com sucesso para ' . htmlspecialchars($destinatarioNome);
        } catch (Exception $e) {
            echo 'Erro ao enviar o email: ' . $mail->ErrorInfo;
        }
    }
}
?>
<div class="container">
    <h1>Enviar Email para Aniversariante</h1>
    <form method="POST">
        <div style="margin-bottom: 15px;">
            <div class="info">
            <p><?php echo htmlspecialchars($destinatarioNome . '   |   Sócio Nº: ' . $socio['id'] . '   |   Data de Nascimento: ' . date('d/m/Y', strtotime($socio['data_nascimento']))); ?></p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="flex: 1;">
                <label for="destinatario">Destinatário:</label><br>
                <input type="text" id="destinatario" name="destinatario" value="<?php echo htmlspecialchars($destinatarioNome); ?>" disabled style="width: 100%;">
            </div>
            <div style="flex: 1;">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($destinatarioEmail); ?>" disabled style="width: 100%;">
            </div>
        </div><br>
        <label for="assunto">Assunto:</label><br>
        <input type="text" id="assunto" name="assunto" required style="width: 100%;" value="<?php echo htmlspecialchars('A ' . ASSOCIACAO_NOME . ' deseja-lhe um Feliz Aniversário!'); ?>"><br><br>

        <label for="mensagem">Mensagem:</label><br>
        <textarea id="mensagem" name="mensagem" rows="5" required style="width: 100%;"></textarea><br><br>
        <a href="aniversarios.php"><button type="button"><i class="fa-solid fa-arrow-left"></i> Voltar</button></a>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
    </form>
    <div class="spacer"></div>
</div>
<?php require_once '../templates/footer.php'; ?>
