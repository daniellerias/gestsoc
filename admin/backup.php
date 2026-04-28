<?php
// admin/backup.php
// Criar um backup consistente: dump SQL da BD + ficheiro config.php num ZIP

// Segurança mínima: prevenir execução direta sem acesso permissivo
// (Projectos reais devem verificar autenticação/autorizações aqui.)

require_once '../config.php';
 $projectRoot = dirname(__DIR__);
 $configPath = $projectRoot . '/config.php';

$errors = [];
if (!file_exists($configPath)) {
    $errors[] = 'Ficheiro config.php não encontrado em: ' . htmlspecialchars($configPath);
}

// Se formulário enviado para criar backup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // Usar diretamente as variáveis definidas em config.php
    $db_host = $host ?? null;
    $db_name = $dbname ?? null;
    $db_user = $username ?? null;
    $db_pass = $password ?? null;

    if (!$db_host || !$db_name || $db_user === null) {
        $errors[] = 'Não foi possível obter credenciais da base de dados a partir de config.php.';
    } else {
        // Gerar ficheiro SQL temporário
        $tmpDir = sys_get_temp_dir();
        $timestamp = date('Ymd_His');
        $sqlFile = $tmpDir . DIRECTORY_SEPARATOR . "gestsoc_dump_{$timestamp}.sql";
        $zipFileName = "gestsoc_backup_{$timestamp}.zip";
        $zipFilePath = $tmpDir . DIRECTORY_SEPARATOR . $zipFileName;

        $dumpDone = false;

        // Tentar mysqldump se disponível
        $mysqldump_ok = false;
        @exec('mysqldump --version 2>&1', $out, $ret);
        if ($ret === 0) $mysqldump_ok = true;

        if ($mysqldump_ok) {
            // Construir comando de forma mais segura possível
            $passArg = '';
            if ($db_pass !== null && $db_pass !== '') {
                $passArg = ' --password=' . escapeshellarg($db_pass);
            }
            $cmd = 'mysqldump -h ' . escapeshellarg($db_host) . ' -u ' . escapeshellarg($db_user) . $passArg . ' ' . escapeshellarg($db_name) . ' > ' . escapeshellarg($sqlFile) . ' 2>&1';
            exec($cmd, $cmdOut, $cmdRet);
            if ($cmdRet === 0 && file_exists($sqlFile)) {
                $dumpDone = true;
            } else {
                $errors[] = 'mysqldump falhou ou retornou erro. Fallback para dump em PHP.';
            }
        }

        if (!$dumpDone) {
            // Fallback: criar dump via PHP+PDO
            try {
                $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8";
                $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

                $fp = fopen($sqlFile, 'w');
                if (!$fp) throw new Exception('Não foi possível criar ficheiro temporário para o dump');

                // Header
                fwrite($fp, "-- GestSoc backup\n-- Database: {$db_name}\n-- Generated: " . date('c') . "\n\n");

                // Obter tabelas
                $tables = [];
                $stmt = $pdo->query('SHOW TABLES');
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }

                foreach ($tables as $table) {
                    // CREATE TABLE
                    $row = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                    $create = $row['Create Table'] ?? array_values($row)[1] ?? '';
                    fwrite($fp, "-- Table structure for `{$table}`\nDROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n");

                    // Data
                    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                    if (count($rows) > 0) {
                        fwrite($fp, "-- Data for table `{$table}`\n");
                        foreach (array_chunk($rows, 100) as $chunk) {
                            $cols = array_map(function($c){ return "`$c`"; }, array_keys($chunk[0]));
                            $colsList = implode(', ', $cols);
                            $values = [];
                            foreach ($chunk as $r) {
                                $vals = array_map(function($v) use ($pdo) {
                                    if ($v === null) return 'NULL';
                                    return $pdo->quote($v);
                                }, array_values($r));
                                $values[] = '(' . implode(', ', $vals) . ')';
                            }
                            fwrite($fp, "INSERT INTO `{$table}` ({$colsList}) VALUES\n" . implode(",\n", $values) . ";\n\n");
                        }
                    }
                }

                fclose($fp);
                $dumpDone = true;
            } catch (Exception $e) {
                $errors[] = 'Erro ao gerar dump via PHP: ' . $e->getMessage();
                @unlink($sqlFile);
            }
        }

        // Se dump criado, criar zip com config.php
        if ($dumpDone && empty($errors)) {
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $errors[] = 'Não foi possível criar o ficheiro ZIP temporário.';
            } else {
                $zip->addFile($sqlFile, basename($sqlFile));
                $zip->addFile($configPath, 'config.php');
                $zip->close();

                // Forçar download
                if (file_exists($zipFilePath)) {
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
                    header('Content-Length: ' . filesize($zipFilePath));
                    // Limpando buffers
                    while (ob_get_level()) ob_end_clean();
                    readfile($zipFilePath);
                    // Cleanup temporários
                    @unlink($zipFilePath);
                    @unlink($sqlFile);
                    exit;
                } else {
                    $errors[] = 'Erro: ficheiro ZIP não encontrado após criação.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="container full-vertical">
    <div class="backup-box">
    <h1>Cópia de Segurança</h1>
    <p>Aqui pode gerar uma cópia de segurança completa da base de dados e do ficheiro de configuração num único ficheiro ZIP para download.</p><br>
    <p class="info"><strong>Nota:</strong> Por questões de performance a cópia de segurança não inclui ficheiros de imagem ou pdfs.</p><br>
    <?php if (!empty($errors)): ?>
        <div class="alert"><strong>Erros:</strong><br><?php echo implode('<br>', array_map('htmlspecialchars',$errors)); ?></div>
    <?php endif; ?>
    <form method="post">
        <p>
            <button type="submit" class="section_button">
            <i class="fa-solid fa-download"></i> Gerar Cópia de Segurança
            </button>
        </p>
    </form>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php';
