<?php
// Instalador GestSoc v0.4b
// Daniel Lérias - https://daniellerias.sectid.pt
// v0.4b - 2026-03-04
$current_version = '0.4b';
$latest_update = '2026-03-04';
$metodos_default = ['Numerário', 'MB', 'MBWay', 'T. Bancária'];

function e($s){ return htmlspecialchars($s ?? ''); }

$step = $_POST['step'] ?? '';
$errors = [];
$success = '';
$smtp_test_success = '';

// Detectar endereço do browser para sugerir como WEB_ADDRESS
$detected_web_address = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    // Remove query string
    $request_uri = preg_replace('/\?.*$/', '', $request_uri);
    // Get directory part (remove filename like /install.php)
    $dir = rtrim(dirname($request_uri), '/\\');
    $detected_web_address = $scheme . $_SERVER['HTTP_HOST'] . ($dir !== '' && $dir !== '.' ? $dir : '');
}

// Helper: atualizar apenas as partes relevantes do config.php, preservando o resto do conteúdo e comentários. Se o ficheiro não existir ou não puder ser lido, pode criar um novo com o conteúdo fornecido em fallbackContent.
function update_config_file($cfgPath, $replacements, $fallbackContent = null)
{
    // if (file_exists($cfgPath)) @copy($cfgPath, $cfgPath . '.bak.' . date('YmdHis'));
    $content = @file_get_contents($cfgPath);
    if ($content === false) $content = '';

    $changed = false;
    $logpath = dirname($cfgPath) . '/install_error.log';

    // Replace scalar php variables like $dbname = '...'; or with double quotes
    $replace_var = function($varName, $value) use (&$content, &$changed, &$logpath) {
        $pat = '/(\\$' . preg_quote($varName, '/') . '\\s*=\\s*)(["\']?)(.*?)(["\']?)(\\s*;)/s';
        $rep = '$1' . "'" . addslashes($value) . "'" . '$5';
        if (preg_match($pat, $content)) {
            $content = preg_replace($pat, $rep, $content, 1);
            $changed = true;
            @file_put_contents($logpath, date('c') . " - update_config_file: replaced var $varName\n", FILE_APPEND);
            return true;
        }
        // append if not found
        $content .= "\n\\$" . $varName . " = '" . addslashes($value) . "';\n";
        $changed = true;
        @file_put_contents($logpath, date('c') . " - update_config_file: appended var $varName\n", FILE_APPEND);
        return true;
    };

    // Replace array literals like $anos_disponiveis = [ ... ];
    $replace_array = function($varName, $literal) use (&$content, &$changed, &$logpath) {
        $pat = '/(\\$' . preg_quote($varName, '/') . '\\s*=\\s*)[^;]*;/s';
        $rep = '$1' . $literal . ';';
        if (preg_match($pat, $content)) {
            $content = preg_replace($pat, $rep, $content, 1);
            $changed = true;
            @file_put_contents($logpath, date('c') . " - update_config_file: replaced array $varName\n", FILE_APPEND);
            return true;
        }
        $content .= "\n\\$" . $varName . " = " . $literal . ";\n";
        $changed = true;
        @file_put_contents($logpath, date('c') . " - update_config_file: appended array $varName\n", FILE_APPEND);
        return true;
    };

    if (isset($replacements['INSTALLER_MODE'])) {
        $boolVal = $replacements['INSTALLER_MODE'] ? 'true' : 'false';
        $content = preg_replace('/(\$INSTALLER_MODE\s*=\s*)(true|false)\s*;/', '$1' . $boolVal . ';', $content, -1, $c0);
        if ($c0) {
            $changed = true;
        } else {
            $content .= "\n\$INSTALLER_MODE = " . $boolVal . ";\n";
            $changed = true;
        }
    }

    if (isset($replacements['WEB_ADDRESS'])) {
        $val = $replacements['WEB_ADDRESS'];
        $rep = "define('WEB_ADDRESS', '" . addslashes($val) . "');";
        // Try to replace existing definition using a more robust pattern that handles any quote type and spacing
        if (preg_match("/define\s*\(\s*['\"]WEB_ADDRESS['\"]\s*,/i", $content)) {
            // Replace from the comma to the closing paren/semicolon, handling any spacing and quote variations
            $content = preg_replace("/define\s*\(\s*['\"]WEB_ADDRESS['\"]\s*,\s*['\"][^'\"]*['\"]\s*\);/s", $rep, $content, -1, $cc);
            if ($cc) $changed = true;
        } else {
            // Append if not found
            $content .= "\n" . $rep . "\n";
            $changed = true;
        }
    }
    if (isset($replacements['VERSION'])) {
        $content = preg_replace("/define\s*\(\s*['\"]VERSION['\"]\s*,\s*['\"][^'\"]*['\"]\s*\);/s", "define('VERSION', '" . addslashes($replacements['VERSION']) . "');", $content, -1, $c2);
        if ($c2) $changed = true;
    }

    if (isset($replacements['ANOS_LITERAL'])) {
        $replace_array('anos_disponiveis', $replacements['ANOS_LITERAL']);
    }
    if (isset($replacements['METODOS_LITERAL'])) {
        $replace_array('metodos', $replacements['METODOS_LITERAL']);
    }

    // DB vars
    if (isset($replacements['DB_HOST'])) $replace_var('host', $replacements['DB_HOST']);
    if (isset($replacements['DB_NAME'])) $replace_var('dbname', $replacements['DB_NAME']);
    if (isset($replacements['DB_USER'])) $replace_var('username', $replacements['DB_USER']);
    if (isset($replacements['DB_PASS'])) $replace_var('password', $replacements['DB_PASS']);

    // SMTP vars
    foreach (['SMTP_HOST','SMTP_PORT','SMTP_USER','SMTP_PASS','SMTP_FROM_EMAIL','SMTP_FROM_NAME'] as $k) {
        if (isset($replacements[$k])) {
            $val = $replacements[$k];
            if (is_int($val) || (is_string($val) && ctype_digit($val))) {
                $rep = "define('$k', " . intval($val) . ");";
            } else {
                $rep = "define('$k', '" . addslashes($val) . "');";
            }
            // Use a more robust pattern that doesn't rely on backreferences
            if (preg_match("/define\s*\(\s*['\"]" . preg_quote($k, '/') . "['\"]\s*,/i", $content)) {
                $content = preg_replace("/define\s*\(\s*['\"]" . preg_quote($k, '/') . "['\"]\s*,\s*['\"]?[^'\"]*['\"]?\s*\);/s", $rep, $content, -1, $cc);
                if ($cc) $changed = true;
            } else {
                $content .= "\n" . $rep . "\n";
                $changed = true;
            }
        }
    }

    if ($changed) {
        $w = @file_put_contents($cfgPath, $content);
        @file_put_contents($logpath, date('c') . " - update_config_file: wrote config (changed). write_ok=" . ($w !== false ? '1' : '0') . "\n", FILE_APPEND);
        return $w !== false;
    }

    if ($fallbackContent !== null) {
        $w = @file_put_contents($cfgPath, $fallbackContent);
        @file_put_contents($logpath, date('c') . " - update_config_file: wrote fallback config. write_ok=" . ($w !== false ? '1' : '0') . "\n", FILE_APPEND);
        return $w !== false;
    }
    @file_put_contents($logpath, date('c') . " - update_config_file: no changes and no fallback provided\n", FILE_APPEND);
    return false;
}

// Detectar se já existe um ficheiro config.php com configurações
$cfgPath = __DIR__ . 'config.php';
$config_exists = false;
$config_has_configs = false;
if (file_exists($cfgPath)) {
    $config_exists = true;
    $cfgContent = @file_get_contents($cfgPath);
    if ($cfgContent !== false) {
        if (preg_match("/define\\s*\\(\\s*'WEB_ADDRESS'|define\\s*\\(\\s*'SMTP_HOST'|\\$host\\s*=|\\$anos_disponiveis|\\$metodos/", $cfgContent)) {
            $config_has_configs = true;
        } elseif (trim($cfgContent) !== '') {
            // Se o ficheiro não estiver vazio, assumir que tem configurações
            $config_has_configs = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'run') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');

    // forçar a detecção do endereço web mesmo que o utilizador tenha deixado o campo vazio, para evitar problemas de configuração posterior
    $web_address = $detected_web_address;
    
    if (empty($web_address)) {
        $errors[] = 'Aviso: não foi possível detectar automaticamente o endereço web. Configure manualmente WEB_ADDRESS em config.php depois da instalação.';
    }

    $assoc_nome = trim($_POST['assoc_nome'] ?? '');
    $assoc_morada = trim($_POST['assoc_morada'] ?? '');
    $assoc_contacto = trim($_POST['assoc_contacto'] ?? '');
    $assoc_nif = trim($_POST['assoc_nif'] ?? '');
    $assoc_email = trim($_POST['assoc_email'] ?? '');

    // Inicializar o caminho do logotipo
    $logotipo_path = null;
    // Validar e processar upload do logotipo se um ficheiro foi enviado
    if (!empty($_FILES['logotipo']) && ($_FILES['logotipo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['logotipo'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $imgInfo = @getimagesize($f['tmp_name']);
            $mime = ($imgInfo && isset($imgInfo['mime'])) ? $imgInfo['mime'] : @mime_content_type($f['tmp_name']);
            $allowed = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/gif' => 'gif',
                'image/svg+xml' => 'svg'
            ];
            if (!$imgInfo || !$mime || !isset($allowed[$mime])) {
                $errors[] = 'O ficheiro do logotipo não é uma imagem válida (PNG/JPG/GIF/SVG).';
            } else {
                // verificar se IMG_DIR está definida em config.php, senão definir para o padrão
                if (!defined('IMG_DIR')) {
                    define('IMG_DIR', __DIR__ . '/assets/img/');
                }
                $destDir = IMG_DIR;
                if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
                $filename = basename($f['name']);
                $dest = $destDir . $filename;
                if (@move_uploaded_file($f['tmp_name'], $dest)) {
                    // path stored in DB/views remains relative
                    $logotipo_path = 'assets/img/' . $filename;
                } else {
                    $errors[] = 'Falha ao mover o ficheiro do logotipo para o servidor.';
                }
            }
        } else {
            $errors[] = 'Erro no upload do logotipo (código ' . intval($f['error']) . ').';
        }
    }

    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = (int)($_POST['smtp_port'] ?? 465);
    $smtp_user = trim($_POST['smtp_user'] ?? '');
    $smtp_pass = trim($_POST['smtp_pass'] ?? '');
    $smtp_from_email = trim($_POST['smtp_from_email'] ?? '');
    $smtp_from_name = trim($_POST['smtp_from_name'] ?? '');

    // Flag to skip installation when just testing SMTP
    $skip_install = false;

    // Handle SMTP test action (button named 'test_smtp')
    if (isset($_POST['test_smtp'])) {
        $test_to = trim($_POST['test_email_to'] ?? $smtp_from_email ?? '');
        if (!$smtp_host || !$smtp_from_email || !$test_to) {
            $errors[] = 'Por favor preencha pelo menos SMTP Host, Email Remetente e Email de teste.';
        } else {
            // Try to load PHPMailer from vendor if available. If composer autoload is empty/missing,
            // attempt to require the PHPMailer src files directly as a fallback.
            $autoloaded = false;
            if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                // some distributions include a real autoload.php; try to require it
                if (@filesize(__DIR__ . '/vendor/autoload.php') > 0) {
                    require_once __DIR__ . '/vendor/autoload.php';
                    $autoloaded = true;
                }
            }
            // If PHPMailer still not available, try to include the library files directly
            if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                $phps = [
                    __DIR__ . '/vendor/PHPMailer/src/Exception.php',
                    __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php',
                    __DIR__ . '/vendor/PHPMailer/src/SMTP.php',
                ];
                $found = true;
                foreach ($phps as $p) {
                    if (file_exists($p)) {
                        require_once $p;
                    } else {
                        $found = false;
                    }
                }
                if ($found) {
                    // classes should now be available via their namespace declarations
                }
            }
            try {
                if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = $smtp_host;
                    $mail->Port = $smtp_port;
                    $mail->SMTPAuth = !empty($smtp_user) || !empty($smtp_pass);
                    if (!empty($smtp_user)) $mail->Username = $smtp_user;
                    if (!empty($smtp_pass)) $mail->Password = $smtp_pass;
                    // Ensure UTF-8 encoded content and safe transfer encoding
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->isHTML(false);
                    // Choose secure method heuristically
                    if ($smtp_port == 465) {
                        if (defined('PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_SMTPS')) $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        else $mail->SMTPSecure = 'ssl';
                    } else {
                        if (defined('PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS')) $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        else $mail->SMTPSecure = 'tls';
                    }
                    $mail->setFrom($smtp_from_email, $smtp_from_name ?: 'GestSoc');
                    $mail->addAddress($test_to);
                    $mail->Subject = 'Teste de configuração SMTP - GestSoc';
                    $mail->Body = 'Este é um email de teste enviado pelo instalador do GestSoc em ' . date('c');
                    $mail->send();
                    $smtp_test_success = 'Email de teste enviado com sucesso para ' . htmlspecialchars($test_to);
                } else {
                    // PHPMailer not available, try basic mail() as fallback
                    $subject = 'Teste de configuração SMTP - GestSoc';
                    $body = 'Este é um email de teste enviado pelo instalador do GestSoc em ' . date('c');
                    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
                    $encodedBody = chunk_split(base64_encode($body));
                    $headers = 'From: ' . $smtp_from_name . ' <' . $smtp_from_email . '>\r\n';
                    $headers = "From: " . $smtp_from_name . " <" . $smtp_from_email . ">\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    $headers .= "Content-Transfer-Encoding: base64\r\n";
                    $ok = @mail($test_to, $encodedSubject, $encodedBody, $headers);
                    if ($ok) {
                        $smtp_test_success = 'Email de teste enviado (via mail()) para ' . htmlspecialchars($test_to) . '. Se este método falhar, instale o PHPMailer.';
                    } else {
                        $errors[] = 'Falha ao enviar email de teste usando mail(). Instale o PHPMailer para melhores mensagens de erro.';
                    }
                }
            } catch (Exception $e) {
                    $errors[] = 'Falha ao enviar email de teste: ' . $e->getMessage();
                @file_put_contents(__DIR__ . '/install_error.log', date('c') . " - SMTP test error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
            }
        }
        // prevent continuing with DB installation when just testing
        $skip_install = true;
    }

    if (!$db_name || !$db_user) {
        $errors[] = 'Database name and user are required.';
    }

    if (empty($errors) && empty($skip_install)) {
        try {
            // Ligar ao servidor MySQL (sem base de dados) para criar a BD se necessário
            try {
                $pdo = new PDO("mysql:host=$db_host;charset=utf8", $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            } catch (PDOException $e) {
                // Fallback: em alguns ambientes 'localhost' tenta socket e falha. Tentar TCP para 127.0.0.1
                if ($db_host === 'localhost') {
                    try {
                        $pdo = new PDO("mysql:host=127.0.0.1;charset=utf8", $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
                    } catch (PDOException $e2) {
                        // rethrow original (or new) to be caught by outer try
                        throw $e2;
                    }
                } else {
                    throw $e;
                }
            }

            // Criar a base de dados se não existir
            try {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `".str_replace('`','``',$db_name)."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                throw $e;
            }

            // Ligar à base de dados
            try {
                $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            } catch (PDOException $e) {
                if ($db_host === 'localhost') {
                    try {
                        $pdo = new PDO("mysql:host=127.0.0.1;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
                    } catch (PDOException $e2) {
                        throw $e2;
                    }
                } else {
                    throw $e;
                }
            }

            // Criar tabelas
            $sqls = [];
            $sqls[] = "CREATE TABLE IF NOT EXISTS associacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                morada TEXT,
                contacto VARCHAR(100),
                logotipo VARCHAR(255),
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                nif VARCHAR(50),
                email VARCHAR(255)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $sqls[] = "CREATE TABLE IF NOT EXISTS tipos_quotas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255),
                valor DECIMAL(10,2),
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
                atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                atualizado_por VARCHAR(100) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $sqls[] = "CREATE TABLE IF NOT EXISTS socios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_socio VARCHAR(50),
                nome_completo VARCHAR(255),
                data_nascimento DATE,
                bi_cc VARCHAR(100),
                nif VARCHAR(100),
                morada TEXT,
                telefone VARCHAR(50),
                telemovel VARCHAR(50),
                email VARCHAR(255),
                estado VARCHAR(50),
                quota_id INT,
                anotacoes TEXT,
                data_registo DATE,
                associacao_id INT,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                diabetico TINYINT(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $sqls[] = "CREATE TABLE IF NOT EXISTS pagamentos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                associado_id INT NOT NULL,
                quota_id INT NOT NULL,
                data_pagamento DATE,
                montante DECIMAL(10,2) DEFAULT 0,
                metodo_pagamento VARCHAR(100),
                referente_ano INT,
                meses VARCHAR(255),
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $sqls[] = "CREATE TABLE IF NOT EXISTS recibos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            // Criar tabela users para autenticação
            $sqls[] = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                nome VARCHAR(100) DEFAULT NULL,
                email VARCHAR(100) DEFAULT NULL,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY (username)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            // Executar instruções de criação
            $pdo->beginTransaction();
            foreach ($sqls as $s) $pdo->exec($s);

            // Inserir amostra padrão de tipos_quotas se estiver vazia
            $stmt = $pdo->query("SELECT COUNT(*) as c FROM tipos_quotas");
            $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
            if ($cnt == 0) {
                $pdo->exec("INSERT INTO tipos_quotas (nome, valor) VALUES
                    ('Quota Simples Exemplo', 1.00),
                    ('Quota Normal Exemplo', 8.00),
                    ('Quota Reduzida Exemplo', 3.00),
                    ('Quota Premium Exemplo', 10.00)");
            }

            // Inserir associação com id = 1
            // Se já existir, atualizar (incluindo logotipo quando disponível)
            $stmt = $pdo->prepare('SELECT id FROM associacoes WHERE id = 1 LIMIT 1');
            $stmt->execute();
            if ($stmt->fetch()) {
                if ($logotipo_path) {
                    $upd = $pdo->prepare('UPDATE associacoes SET nome=?, morada=?, contacto=?, nif=?, email=?, logotipo=? WHERE id=1');
                    $upd->execute([$assoc_nome, $assoc_morada, $assoc_contacto, $assoc_nif, $assoc_email, $logotipo_path]);
                } else {
                    $upd = $pdo->prepare('UPDATE associacoes SET nome=?, morada=?, contacto=?, nif=?, email=? WHERE id=1');
                    $upd->execute([$assoc_nome, $assoc_morada, $assoc_contacto, $assoc_nif, $assoc_email]);
                }
            } else {
                if ($logotipo_path) {
                    $ins = $pdo->prepare('INSERT INTO associacoes (id, nome, morada, contacto, nif, email, logotipo, criado_em) VALUES (1, ?, ?, ?, ?, ?, ?, NOW())');
                    $ins->execute([$assoc_nome, $assoc_morada, $assoc_contacto, $assoc_nif, $assoc_email, $logotipo_path]);
                } else {
                    $ins = $pdo->prepare('INSERT INTO associacoes (id, nome, morada, contacto, nif, email, criado_em) VALUES (1, ?, ?, ?, ?, ?, NOW())');
                    $ins->execute([$assoc_nome, $assoc_morada, $assoc_contacto, $assoc_nif, $assoc_email]);
                }
            }

            // Commit only if transaction still active; some engines auto-commit DDL and end the transaction.
            try {
                if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                    $pdo->commit();
                }
            } catch (Exception $cEx) {
                @file_put_contents(__DIR__ . '/install_error.log', date('c') . " - Commit warning: " . $cEx->getMessage() . "\n" . $cEx->getTraceAsString() . "\n\n", FILE_APPEND);
                // proceed: admin creation and config update should still run
            }

            // Criar utilizador admin se dados fornecidos
            $admin_username = trim($_POST['admin_username'] ?? '');
            $admin_password = $_POST['admin_password'] ?? '';
            $admin_nome = trim($_POST['admin_nome'] ?? '');
            $admin_email = trim($_POST['admin_email'] ?? '');

            // Validate admin credentials before attempting create/update
            if ($admin_username) {
                if (!$admin_password) {
                    // Explicit warning: username provided but no password
                    $errors[] = 'Aviso: foi fornecido um username de administrador mas a palavra-passe está vazia. O utilizador não será criado.';
                } else {
                    try {
                        // Verificar se o utilizador já existe
                        $uStmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
                        $uStmt->execute([$admin_username]);
                        $exists = (bool)$uStmt->fetch(PDO::FETCH_ASSOC);
                        $hash = password_hash($admin_password, PASSWORD_DEFAULT);

                        if ($exists) {
                            $up = $pdo->prepare('UPDATE users SET password_hash = ?, nome = ?, email = ? WHERE username = ?');
                            $up->execute([$hash, $admin_nome ?: null, $admin_email ?: null, $admin_username]);
                            $rows = $up->rowCount();
                            if ($rows === 0) {
                                // No rows updated - warn
                                $errors[] = 'Aviso: tentativa de actualizar utilizador admin não actualizou registos.';
                                @file_put_contents(__DIR__ . '/install_error.log', date('c') . " - Admin update: no rows updated for $admin_username\n", FILE_APPEND);
                            }
                        } else {
                            $insu = $pdo->prepare('INSERT INTO users (username, password_hash, nome, email, criado_em) VALUES (?, ?, ?, ?, NOW())');
                            $insu->execute([$admin_username, $hash, $admin_nome ?: null, $admin_email ?: null]);
                            $lastId = $pdo->lastInsertId();
                            if (!$lastId) {
                                $errors[] = 'Aviso: não foi possível obter o id do utilizador inserido. Verifique a tabela users.';
                                @file_put_contents(__DIR__ . '/install_error.log', date('c') . " - Admin insert: lastInsertId returned empty for $admin_username\n", FILE_APPEND);
                            } else {
                                @file_put_contents(__DIR__ . '/install_error.log', date('c') . " - Admin insert: created user id=$lastId username=$admin_username\n", FILE_APPEND);
                            }
                        }
                    } catch (PDOException $uex) {
                        // Logar erro detalhado e adicionar mensagem amigável
                        @file_put_contents(__DIR__ . '/install_error.log', date('c') . " - User create/update error: " . $uex->getMessage() . "\n" . $uex->getTraceAsString() . "\n\n", FILE_APPEND);
                        $errors[] = 'Erro ao criar/actualizar o utilizador administrador: ' . $uex->getMessage();
                    }
                }
            }

            // Analisar métodos de pagamento e anos disponíveis a partir do formulário de instalação
            $metodos_input = $_POST['metodos_list'] ?? '';
            $anos_input = trim($_POST['anos_list'] ?? '');

            // Accept either an array of selected checkboxes or a comma-separated string (backwards compatibility)
            if (is_array($metodos_input)) {
                $metodos_arr = array_filter(array_map('trim', $metodos_input), function($v){ return $v !== ''; });
            } else {
                $metodos_input = trim((string)$metodos_input);
                $metodos_arr = $metodos_input !== '' ? array_filter(array_map('trim', explode(',', $metodos_input)), function($v){ return $v !== ''; }) : [];
            }

            $anos_arr = array_filter(array_map('trim', explode(',', $anos_input)), function($v){ return $v !== ''; });

            // Gerar literais PHP para arrays
            if (!empty($anos_arr)) {
                $anos_items = array_map(function($v){ return (int)$v; }, $anos_arr);
                $anos_literal = '[' . implode(', ', $anos_items) . ']';
            } else {
                $anos_literal = '[2024, 2025, 2026, 2027]';
            }

            if (!empty($metodos_arr)) {
                $metodos_items = array_map(function($v){ return "'" . addslashes($v) . "'"; }, $metodos_arr);
                $metodos_literal = '[' . implode(', ', $metodos_items) . ']';
            } else {
                // Use the defaults defined at the top of the installer when no selection provided
                $metodos_items = array_map(function($v){ return "'" . addslashes($v) . "'"; }, $metodos_default);
                $metodos_literal = '[' . implode(', ', $metodos_items) . ']';
            }

            $cfgPath = __DIR__ . '/config.php';

            // Gerar conteúdo do config.php baseado no config.php atual
            $configContent = "<?php\n";
            $configContent .= "// Generated by install.php on " . date('c') . "\n";
            $configContent .= "\$INSTALLER_MODE = false; // true = modo instalador ativo, false = modo aplicação ativo\n";
            $configContent .= "if (\$INSTALLER_MODE) {\n    header(\"Location: install.php\");\n    exit;\n}\n";
            $configContent .= "//Mostrar erros (DEBUG)\n";
            $configContent .= "\$MOSTRAR_ERROS = false;\n\n";
            $configContent .= "if (\$MOSTRAR_ERROS) {\n    error_reporting(E_ALL);\n    ini_set('display_errors', 1);\n}\n\n";
            $configContent .= "// Aumentar limites para geração de múltiplos PDFs\n";
            $configContent .= "ini_set('memory_limit', '512M');\n";
            $configContent .= "// Limite de post (5 minutos)\n";
            $configContent .= "set_time_limit(300);\n";
            $configContent .= "// Definir o diretório raiz do projeto\n";
            $configContent .= "define('PROJECT_ROOT', __DIR__);\n";
            $configContent .= "// Definir o endereco web do sistema\n";
            $configContent .= "define('WEB_ADDRESS', '" . addslashes($web_address) . "');\n\n";
            $configContent .= "// Nome da Aplicação\n";
            $configContent .= "define('APP_NAME', 'GestSoc');\n";
            $configContent .= "define('APP_FULL_NAME', 'Sistema de Gestão de Sócios');\n\n";
            $configContent .= "// Versão do sistema\n";
            $configContent .= "define('VERSION', '" . addslashes($current_version) . "');\n\n";
            $configContent .= "// Data última atualização\n";
            $configContent .= "define('LAST_UPDATE', '" . addslashes($latest_update) . "');\n\n";
            $configContent .= "// URL base do sistema (ajustar conforme necessário)\n";
            $configContent .= "\$base_url = WEB_ADDRESS . '/';\n\n";
            $configContent .= "// Mostrar campo \"Associação\" ao editar associado (para sistemas com múltiplas associações)\n";
            $configContent .= "\$show_associacao_on_edit = false;\n\n";
            $configContent .= "// Ativar ou desativar o uso do campo 'diabético' em todo o sistema\n";
            $configContent .= "\$usar_diabetico = false; // true = ativo, false = desativado\n\n";
            $configContent .= "// Anos disponíveis para pagamentos (pode ser alterado nas configurações)\n";
            $configContent .= "\$anos_disponiveis = [2026, 2027];\n\n";
            $configContent .= "// Métodos de pagamento disponíveis (pode ser alterado nas configurações)\n";
            $configContent .= "\$metodos = ['Numerário'];\n\n";
            $configContent .= "// Número de registos a mostrar por página por defeito (pode ser alterado nas configurações)\n";
            $configContent .= "\$REGISTOS_POR_PAGINA = 25;\n\n";
            $configContent .= "// Configurações de email SMTP\n";
            $configContent .= "define('SMTP_HOST', '" . addslashes($smtp_host) . "'); // Substitua pelo host SMTP\n";
            $configContent .= "define('SMTP_PORT', " . (int)$smtp_port . " ); // Porta SMTP\n";
            $configContent .= "define('SMTP_USER', '" . addslashes($smtp_user) . "'); // Substitua pelo seu email\n";
            $configContent .= "define('SMTP_PASS', '" . addslashes($smtp_pass) . "'); // Substitua pela sua senha\n";
            $configContent .= "define('SMTP_FROM_EMAIL', '" . addslashes($smtp_from_email) . "'); // Email do remetente\n";
            $configContent .= "define('SMTP_FROM_NAME', '" . addslashes($smtp_from_name) . "'); // Nome do remetente\n\n";
            $configContent .= "\n// === Ligação à Base de Dados ===\n";
            $configContent .= "\$host = 'localhost';\n";
            $configContent .= "\$dbname = 'gestao_socios_dev';\n";
            $configContent .= "\$username = 'root';\n";
            $configContent .= "\$password = '';\n\n";
            $configContent .= "\$nome_associacao = '';\n\n";
            $configContent .= "// Definir o nome da associação como constante\n";
            $configContent .= "define('ASSOCIACAO_NOME', \$nome_associacao);\n\n";
            $configContent .= "// Considerar a base de dados \"configurada\" quando todas as variáveis de ligação estão preenchidas\n";
            $configContent .= "\$db_configured = !empty(\$host) && !empty(\$dbname) && !empty(\$username) && !empty(\$password);\n\n";
            $configContent .= "// Auxiliar: registar a mensagem do PDO e mostrar uma página HTML amigável com link para o instalador\n";
            $configContent .= "function render_db_error_page_and_log(\$message) {\n    \$logfile = defined('PROJECT_ROOT') ? PROJECT_ROOT . '/install_error.log' : __DIR__ . '/install_error.log';\n    \$entry = date('c') . \" - \" . \$message . PHP_EOL;\n    @file_put_contents(\$logfile, \$entry, FILE_APPEND | LOCK_EX);\n\n    header('HTTP/1.1 500 Internal Server Error');\n    echo '<!doctype html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">';\n    echo '<title>Erro ao ligar à base de dados</title>';\n    echo '<link rel=\"stylesheet\" href=\"assets/css/style.css\">';\n    echo '</head><body>';\n    echo '<div class=\"container\" style=\"padding:2rem;\">';\n    echo '<h1>Erro ao ligar à base de dados</h1>';\n    echo '<p>Ocorreu um problema ao tentar estabelecer a ligação à base de dados. A mensagem técnica foi registada no ficheiro de logs e está apresentada abaixo para fins de diagnóstico.</p>';\n    echo '<div class=\"alert\" style=\"white-space:pre-wrap;word-wrap:break-word;\">' . htmlspecialchars(\$message) . '</div>';\n    echo '<p style=\"margin-top:1rem\"><a class=\"section_button\" href=\"install.php\">Ir para o instalador</a></p>';\n    echo '</div></body></html>';\n    exit;\n}\n\n";

            $replacements = [
                'INSTALLER_MODE' => false,
                'WEB_ADDRESS' => $web_address,
                'VERSION' => $current_version,
                'LAST_UPDATE' => $latest_update,
                'ANOS_LITERAL' => $anos_literal,
                'METODOS_LITERAL' => $metodos_literal,
                'DB_HOST' => $db_host,
                'DB_NAME' => $db_name,
                'DB_USER' => $db_user,
                'DB_PASS' => $db_pass,
                'SMTP_HOST' => $smtp_host,
                'SMTP_PORT' => $smtp_port,
                'SMTP_USER' => $smtp_user,
                'SMTP_PASS' => $smtp_pass,
                'SMTP_FROM_EMAIL' => $smtp_from_email,
                'SMTP_FROM_NAME' => $smtp_from_name,
            ];
            $fallback_content = $configContent;
            update_config_file($cfgPath, $replacements, $fallback_content);

            $success = 'Instalação concluída com sucesso. O ficheiro config.php foi actualizado e as tabelas foram criadas.';

        } catch (Exception $ex) {
            // Safe rollback: only attempt if PDO object exists and reports an active transaction.
            if (isset($pdo) && is_object($pdo)) {
                try {
                    if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                } catch (Exception $rbEx) {
                    $errors[] = 'Aviso: falha ao fazer rollback: ' . $rbEx->getMessage();
                }
            }

            // Log full exception details for diagnostics
            @file_put_contents(__DIR__ . '/install_process.log', date('c') . " - Exception: " . $ex->getMessage() . "\n" . $ex->getTraceAsString() . "\n\n", FILE_APPEND);

            // If tables were created successfully, still write config.php so the application can start,
            // and report a partial success instead of failing completely.
            $canWriteConfig = false;
            if (isset($pdo) && is_object($pdo)) {
                try {
                    $check = $pdo->query("SHOW TABLES LIKE 'associacoes'");
                    if ($check && $check->fetch()) {
                        $canWriteConfig = true;
                    }
                } catch (Exception $chkEx) {
                    // ignore
                }
            }

            if ($canWriteConfig) {
                // regenerate config content (same format as successful path)
                $cfgPath = __DIR__ . '/config.php';
                $configContent = "<?php\n";
                $configContent .= "// Generated by install.php on " . date('c') . "\n";
                $configContent .= "\$INSTALLER_MODE = false; // true = modo instalador ativo, false = modo aplicação ativo\n";
                $configContent .= "if (\$INSTALLER_MODE) {\n    header(\"Location: install.php\");\n    exit;\n}\n";
                $configContent .= "//Mostrar erros (DEBUG)\n";
                $configContent .= "\$MOSTRAR_ERROS = false;\n\n";
                $configContent .= "if (\$MOSTRAR_ERROS) {\n    error_reporting(E_ALL);\n    ini_set('display_errors', 1);\n}\n\n";
                $configContent .= "// Aumentar limites para geração de múltiplos PDFs\n";
                $configContent .= "ini_set('memory_limit', '512M');\n";
                $configContent .= "// Limite de post (5 minutos)\n";
                $configContent .= "set_time_limit(300);\n";
                $configContent .= "// Definir o diretório raiz do projeto\n";
                $configContent .= "define('PROJECT_ROOT', __DIR__);\n";
                $configContent .= "// Definir o endereco web do sistema\n";
                $configContent .= "define('WEB_ADDRESS', '" . addslashes($web_address) . "');\n\n";
                $configContent .= "// Nome da Aplicação\n";
                $configContent .= "define('APP_NAME', 'GestSoc');\n";
                $configContent .= "define('APP_FULL_NAME', 'Sistema de Gestão de Sócios');\n\n";
                $configContent .= "// Versão do sistema\n";
                $configContent .= "define('VERSION', '" . addslashes($current_version) . "');\n\n";
                $configContent .= "// Data última atualização\n";
                $configContent .= "define('LAST_UPDATE', '" . addslashes($latest_update) . "');\n\n";
                $configContent .= "// URL base do sistema (ajustar conforme necessário)\n";
                $configContent .= "\$base_url = WEB_ADDRESS . '/';\n\n";
                $configContent .= "// Mostrar campo \"Associação\" ao editar associado (para sistemas com múltiplas associações)\n";
                $configContent .= "\$show_associacao_on_edit = false;\n\n";
                $configContent .= "// Ativar ou desativar o uso do campo 'diabético' em todo o sistema\n";
                $configContent .= "\$usar_diabetico = false; // true = ativo, false = desativado\n\n";
                $configContent .= "// Anos disponíveis para pagamentos (pode ser alterado nas configurações)\n";
                $configContent .= "\$anos_disponiveis = [2026, 2027];\n\n";
                $configContent .= "// Métodos de pagamento disponíveis (pode ser alterado nas configurações)\n";
                $configContent .= "\$metodos = ['Numerário'];\n\n";
                $configContent .= "// Número de registos a mostrar por página por defeito (pode ser alterado nas configurações)\n";
                $configContent .= "\$REGISTOS_POR_PAGINA = 25;\n\n";
                $configContent .= "// Configurações de email SMTP\n";
                $configContent .= "define('SMTP_HOST', '" . addslashes($smtp_host) . "'); // Substitua pelo host SMTP\n";
                $configContent .= "define('SMTP_PORT', " . (int)$smtp_port . " ); // Porta SMTP\n";
                $configContent .= "define('SMTP_USER', '" . addslashes($smtp_user) . "'); // Substitua pelo seu email\n";
                $configContent .= "define('SMTP_PASS', '" . addslashes($smtp_pass) . "'); // Substitua pela sua senha\n";
                $configContent .= "define('SMTP_FROM_EMAIL', '" . addslashes($smtp_from_email) . "'); // Email do remetente\n";
                $configContent .= "define('SMTP_FROM_NAME', '" . addslashes($smtp_from_name) . "'); // Nome do remetente\n\n";

                $replacements = [
                    'INSTALLER_MODE' => false,
                    'WEB_ADDRESS' => $web_address,
                    'VERSION' => $current_version,
                    'LAST_UPDATE' => $latest_update,
                    'ANOS_LITERAL' => $anos_literal,
                    'METODOS_LITERAL' => $metodos_literal,
                    'DB_HOST' => $db_host,
                    'DB_NAME' => $db_name,
                    'DB_USER' => $db_user,
                    'DB_PASS' => $db_pass,
                    'SMTP_HOST' => $smtp_host,
                    'SMTP_PORT' => $smtp_port,
                    'SMTP_USER' => $smtp_user,
                    'SMTP_PASS' => $smtp_pass,
                    'SMTP_FROM_EMAIL' => $smtp_from_email,
                    'SMTP_FROM_NAME' => $smtp_from_name,
                ];
                $fallback_content = $configContent;
                update_config_file($cfgPath, $replacements, $fallback_content);

                $success = 'Instalação concluída com sucesso (com avisos). O ficheiro config.php foi actualizado e as tabelas parecem existir. Verifique os avisos e o ficheiro install_error.log se necessário.';
            } else {
                $errors[] = 'Erro: ' . $ex->getMessage();
            }
         }
     }
 }
?>


<!-- HTML -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GestSoc 0.4b - Assistente de Instalação</title>
     <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
        <style>
            /* Small installer-specific overrides */
            .installer-box{padding:1.2rem;background:#fff;border:1px solid var(--color-border);border-radius:8px}
            .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
            @media(max-width:720px){.form-grid{grid-template-columns:1fr}}
            /* Remove default fieldset contour */
            fieldset{border:none;padding:0;margin:0 0 1rem 0}
            legend{font-weight:600;color:var(--color-text);margin-bottom:0.5rem}
        </style>
</head>
<body>
<div class="container">
    <img src="assets/img/SocGestLogo.png" alt="GestSoc" style="max-width:150px; display:block; margin:1rem auto">
    <h1>GestSoc 0.4b - Sistema de Gestão de Sócios</h1>
    <h2>Assistente de Instalação</h2>
    <?php if($errors): ?><div class="alert"><strong>Erros:</strong><br><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div><?php endif; ?>
    <?php if(!empty($smtp_test_success)): ?><div class="info"><?=htmlspecialchars($smtp_test_success)?></div><?php endif; ?>
    <?php if($config_exists && $config_has_configs && !$success): ?>
        <div class="alert"><strong>Aviso:</strong> Já existe um ficheiro <code>config.php</code> com configurações. Se continuar, o ficheiro será sobrescrito. Marque a caixa abaixo para confirmar que pretende sobrescrever o ficheiro.</div><br>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="info"><?php echo e($success); ?></div>
        <div style="margin-top:1.5rem; text-align:center">
            <a class="section_button" href="index.php" style="display:inline-block;padding:0.75rem 1.25rem;font-size:1.05rem">Ir para a aplicação</a>
        </div>
        <br>
    <?php else: ?>

    <div class="installer-box associado-form">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="step" value="run">

        <fieldset>
            <legend>Base de Dados</legend>
            <div class="form-grid">
                <div>
                    <label>Host</label>
                    <input name="db_host" value="<?= e($_POST['db_host'] ?? 'localhost') ?>">
                </div>
                <div>
                    <label>Database</label>
                    <input name="db_name" value="<?= e($_POST['db_name'] ?? 'gestao_socios') ?>">
                </div>
                <div>
                    <label>Usuário</label>
                    <input name="db_user" value="<?= e($_POST['db_user'] ?? '') ?>">
                </div>
                <div>
                    <label>Senha</label>
                    <input type="password" name="db_pass" value="<?= e($_POST['db_pass'] ?? '') ?>">
                </div>
            </div>
        </fieldset>
        <br>
        <hr>
        <br>
        <fieldset>
            <legend>Criar Utilizador Principal</legend>
            <div class="form-grid">
                <div>
                    <label>Utilizador</label>
                    <input name="admin_username" value="<?= e($_POST['admin_username'] ?? '') ?>">
                </div>
                <div>
                    <label>Palavra-passe</label>
                    <input type="password" name="admin_password" value="">
                </div>
                <div>
                    <label>Nome do Utilizador</label>
                    <input name="admin_nome" value="<?= e($_POST['admin_nome'] ?? '') ?>">
                </div>
                <div>
                    <label>Email do Utilizador</label>
                    <input name="admin_email" value="<?= e($_POST['admin_email'] ?? '') ?>">
                </div>
            </div>
        </fieldset>
        <br>
        <hr>
        <br>
        <fieldset>
            <legend>Configuração do Sistema</legend>
            <div class="form-grid">
                <div>
                    <label>Endereço do site</label>
                    <input name="web_address" value="<?= e($_POST['web_address'] ?? $detected_web_address)?>" style="width:90%">
                </div>
                <div>
                    <label>Nome da Associação</label>
                    <p><input name="assoc_nome" value="<?= e($_POST['assoc_nome'] ?? '') ?>" style="width:90%"></p>
                </div>
                <div>
                    <label>Logotipo (opcional)</label>
                    <p><input type="file" name="logotipo" accept="image/*"></p>
                </div>
                <div>
                    <label>Morada</label>
                    <p><input name="assoc_morada" value="<?= e($_POST['assoc_morada'] ?? '') ?>"></p>
                </div>
                <div>
                    <label>Contacto (Tel.)</label>
                    <p><input name="assoc_contacto" value="<?= e($_POST['assoc_contacto'] ?? '') ?>"></p>
                </div>
                <div>
                    <label>NIF (9 dígitos)</label>
                    <p><input name="assoc_nif" value="<?= e($_POST['assoc_nif'] ?? '') ?>" inputmode="numeric" pattern="\d{9}" maxlength="9" title="NIF com 9 digitos"></p>
                </div>
                <div>
                    <label>Email</label>
                    <p><input name="assoc_email" value="<?= e($_POST['assoc_email'] ?? '') ?>"></p>
                </div>
                                <div>
                    <label>Anos disponíveis (ex.: 2024, 2025, 2026)</label>
                    <p><input name="anos_list" value="<?= e($_POST['anos_list'] ?? '') ?>" style="width:90%"></p>
                </div>
                <div>
                    <label>Métodos de Pagamento (seleccione as opções pretendidas)</label>
                    <?php
                    // Render checkboxes for the default payment methods. Preserve posted selections if available.
                    $posted_methods = $_POST['metodos_list'] ?? null;
                    if (is_array($posted_methods)) {
                        $selected_methods = $posted_methods;
                    } elseif (is_string($posted_methods) && trim($posted_methods) !== '') {
                        $selected_methods = array_map('trim', explode(',', $posted_methods));
                    } else {
                        $selected_methods = $metodos_default;
                    }
                    foreach ($metodos_default as $m) {
                        $checked = in_array($m, $selected_methods) ? 'checked' : '';
                        echo '<label style="display:block;padding-top: 0.3em;"><input type="checkbox" name="metodos_list[]" value="' . htmlspecialchars($m) . '" ' . $checked . '> ' . htmlspecialchars($m) . '</label>';
                    }
                    ?>
                </div>

            </div>
        </fieldset>
        <br>
        <hr>
        <br>
        <fieldset>
            <legend>Configuração de Envio de Email (opcional)</legend>
            <div class="form-grid">
                <div>
                    <label>Endereço de Email remetente</label>
                    <input name="smtp_from_email" value="<?= e($_POST['smtp_from_email'] ?? '') ?>">
                </div>
                <div>
                    <label>Nome do Remetente</label>
                    <input name="smtp_from_name" value="<?= e($_POST['smtp_from_name'] ?? '') ?>">
                </div>
                <div>
                    <label>SMTP Host</label>
                    <input name="smtp_host" value="<?= e($_POST['smtp_host'] ?? '') ?>">
                </div>
                <div>
                    <label>SMTP Port SSL</label>
                    <input name="smtp_port" value="<?= e($_POST['smtp_port'] ?? '465') ?>">
                </div>
                <div>
                    <label>SMTP User</label>
                    <input name="smtp_user" value="<?= e($_POST['smtp_user'] ?? '') ?>">
                </div>
                <div>
                    <label>SMTP Password</label>
                    <input type="password" name="smtp_pass" value="<?= e($_POST['smtp_pass'] ?? '') ?>">
                </div>
                <div>
                        <label>Endereço de Email (Teste)</label>
                        <input name="test_email_to" id="test_email_to" value="<?= e($_POST['test_email_to'] ?? $_POST['smtp_from_email'] ?? $_POST['admin_email'] ?? '') ?>">
                        <button type="button" id="test_smtp_btn" class="section_button" style="margin-top:0.5rem; margin-right:0.6rem">Testar Configuração de Email</button>
                        <p><small>Este botão envia um email de teste usando apenas as definições SMTP acima sem executar a instalação completa.</small></p>
                    <?php if(!empty($smtp_test_success)): ?>
                        <div class="info" style="margin-top:0.6rem"><?php echo e($smtp_test_success); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>
        <br>
        <hr>
        <br>
        <div class="actions_buttons">
            <?php if($config_exists && $config_has_configs): ?>
            <?php endif; ?>
            <button type="submit" class="section_button">Executar Instalação</button>
        </div>
    </form>
    </div>
    <?php endif; ?>

</div>
</body>
</html>

<script>
// Submit only SMTP fields when clicking the test button to avoid submitting files or DB fields
document.addEventListener('DOMContentLoaded', function(){
    var btn = document.getElementById('test_smtp_btn');
    if (!btn) return;
    btn.addEventListener('click', function(e){
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        // required fields for SMTP test
        var fields = ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_email','smtp_from_name','test_email_to'];
        fields.forEach(function(name){
            var el = document.querySelector('[name="'+name+'"]');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = el ? el.value : '';
            form.appendChild(input);
        });
        // mark as test submit
        var i1 = document.createElement('input'); i1.type='hidden'; i1.name='test_smtp'; i1.value='1'; form.appendChild(i1);
        var i2 = document.createElement('input'); i2.type='hidden'; i2.name='step'; i2.value='run'; form.appendChild(i2);
        document.body.appendChild(form);
        form.submit();
    });
});
</script>
