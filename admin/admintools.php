<?php
require_once '../config.php';
require_once '../templates/header.php';
require_once '../templates/footer.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            // Create PDO connection using config.php variables
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$username, $password);
            
            if ($_POST['action'] === 'delete_socios') {
                $stmt = $pdo->prepare('DELETE FROM socios');
                $stmt->execute();
                $message = 'Todos os registos de sócios foram eliminados com sucesso.';
                $messageType = 'success';
                
            } elseif ($_POST['action'] === 'delete_pagamentos') {
                $stmt = $pdo->prepare('DELETE FROM pagamentos');
                $stmt->execute();
                $message = 'Todos os registos de pagamentos foram eliminados com sucesso.';
                $messageType = 'success';
                
            } elseif ($_POST['action'] === 'restore_config') {
                $configPath = '../config.php';
                $defaultPath = '../config_default.php';
                
                if (file_exists($defaultPath)) {
                    copy($defaultPath, $configPath);
                    $message = 'Configuração restaurada com sucesso.';
                    $messageType = 'success';
                    
                } else {
                    $message = 'Erro: Ficheiro config_default.php não encontrado.';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            $message = 'Erro: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

    <div class="container">
        <h1>⚠️ Ferramentas do Programador</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message show <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Delete Members -->
        <div class="tool-section">
            <h2>1. Eliminar Todos os Sócios</h2>
            <p>Esta ação irá eliminar permanentemente todos os registos de sócios da base de dados.</p>
            <div class="warning">
                <strong>⚠️ AVISO:</strong> Esta ação é irreversível! Por favor, efectue um backup antes de continuar.
            </div>
            <form method="POST" onsubmit="return confirm('Tem certeza que deseja eliminar TODOS os sócios? Esta ação NÃO pode ser desfeita!');">
                <input type="hidden" name="action" value="delete_socios">
                <div class="button-group">
                    <button type="submit" class="btn btn-danger">Eliminar Todos os Sócios</button>
                </div>
            </form>
        </div>

        <!-- Delete Payments -->
        <div class="tool-section">
            <h2>2. Eliminar Todos os Pagamentos</h2>
            <p>Esta ação irá eliminar permanentemente todos os registos de pagamentos da base de dados.</p>
            <div class="warning">
                <strong>⚠️ AVISO:</strong> Esta ação é irreversível! Por favor, efectue um backup antes de continuar.
            </div>
            <form method="POST" onsubmit="return confirm('Tem certeza que deseja eliminar TODOS os pagamentos? Esta ação NÃO pode ser desfeita!');">
                <input type="hidden" name="action" value="delete_pagamentos">
                <div class="button-group">
                    <button type="submit" class="btn btn-danger">Eliminar Todos os Pagamentos</button>
                </div>
            </form>
        </div>

        <!-- Restore Config -->
        <div class="tool-section">
            <h2>3. Restaurar Configuração Padrão</h2>
            <p>Esta ação irá restaurar o ficheiro config.php com o conteúdo padrão de config_default.php.</p>
            <div class="warning">
                <strong>⚠️ AVISO:</strong> Esta ação é irreversível! As configurações actuais serão perdidas. Por favor, efectue um backup antes de continuar.
            </div>
            <form method="POST" onsubmit="return confirm('Tem certeza que deseja restaurar a configuração padrão? A configuração actual será perdida!');">
                <input type="hidden" name="action" value="restore_config">
                <div class="button-group">
                    <button type="submit" class="btn btn-danger">Restaurar Programa</button>
                </div>
            </form>
        </div>

        <div class="back-link">
            <a href="../index.php">← Voltar à página inicial</a>
        </div>
    </div>
