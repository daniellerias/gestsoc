<?php
// Estado da instalação
$INSTALLER_MODE = true; // true = modo instalador ativo, false = modo aplicação ativo
if ($INSTALLER_MODE) {
    header("Location: install.php");
    exit;
}
//Mostrar erros (DEBUG)
$MOSTRAR_ERROS = true;

if ($MOSTRAR_ERROS) {
error_reporting(E_ALL);
ini_set('display_errors', 1);
}

// Aumentar limites para geração de múltiplos PDFs
ini_set('memory_limit', '512M');
// Limite de post (5 minutos)
set_time_limit(300);
// Definir o diretório raiz do projeto
define('PROJECT_ROOT', __DIR__);
// Definir o diretório das imagens
define('IMG_DIR', __DIR__ . '/assets/img/');
// Definir o endereço web do sistema
define('WEB_ADDRESS', '');

// URL base do sistema (ajustar conforme necessário)
$base_url = WEB_ADDRESS . '/';

// Version control v1.1
require __DIR__ . '/version.php';

// Mostrar campo "Associação" ao editar associado (para sistemas com múltiplas associações)
$show_associacao_on_edit = false;

// Ativar ou desativar o uso do campo 'diabético' em todo o sistema
$usar_diabetico = false; // true = ativo, false = desativado

// Anos disponíveis para pagamentos (pode ser alterado nas configurações)
$anos_disponiveis = [2026, 2027];

// Métodos de pagamento disponíveis (pode ser alterado nas configurações)
$metodos = ['Numerário', 'MB', 'MBWay', 'T. Bancária'];

// Número de registos a mostrar por página por defeito (pode ser alterado nas configurações)
$REGISTOS_POR_PAGINA = 25;

// Configurações de email SMTP
define('SMTP_HOST', ''); // Substitua pelo host SMTP
define('SMTP_PORT', 465); // Porta SMTP
define('SMTP_USER', ''); // Substitua pelo seu email
define('SMTP_PASS', ''); // Substitua pela sua senha
define('SMTP_FROM_EMAIL', ''); // Email do remetente
define('SMTP_FROM_NAME', ''); // Nome do remetente


// === Ligação à Base de Dados ===
$host = 'localhost';
$dbname = '';
$username = '';
$password = '';

// Considerar a base de dados "configurada" quando todas as variáveis de ligação estão preenchidas
$db_configured = !empty($host) && !empty($dbname) && !empty($username) && !empty($password);

// Auxiliar: registar a mensagem do PDO e mostrar uma página HTML amigável com link para o instalador
function render_db_error_page_and_log($message)
{
    // Tentar registar a mensagem bruta (append)
    $logfile = defined('PROJECT_ROOT') ? PROJECT_ROOT . '/install_error.log' : __DIR__ . '/install_error.log';
    $entry = date('c') . " - " . $message . PHP_EOL;
    @file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);

    // Saída em HTML amigável usando o CSS da app
    header('HTTP/1.1 500 Internal Server Error');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Erro ao ligar à base de dados</title>';
    echo '<link rel="stylesheet" href="assets/css/style.css">';
    echo '</head><body>';
    echo '<div class="container" style="padding:2rem;">';
    echo '<h1>Erro ao ligar à base de dados</h1>';
    echo '<p>Ocorreu um problema ao tentar estabelecer a ligação à base de dados. A mensagem técnica foi registada no ficheiro de logs e está apresentada abaixo para fins de diagnóstico.</p>';
    echo '<div class="alert" style="white-space:pre-wrap;word-wrap:break-word;">' . htmlspecialchars($message) . '</div>';
    echo '<p style="margin-top:1rem"><a class="section_button" href="install.php">Ir para o instalador</a></p>';
    echo '</div></body></html>';
    exit;
}

// Sea a base de dados não estiver configurada, redirecionar para o instalador
//if (!$db_configured) {
//    $msg = "Não foi encontrada nenhuma instalação ou base de dados válida";
//    header("Location: install.php?notice=" . urlencode($msg));
//    exit;
//}

// Tentar ligar à base de dados com PDO. As variáveis de BD parecem configuradas, por isso deve tentar-se a ligação e mostrar erros
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Ativar erros PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Comum em alguns ambientes: ligar-se a 'localhost' tenta um socket e pode falhar com
    // "No such file or directory". Tentar fallback TCP para 127.0.0.1 quando o host é 'localhost'.
    if ($host === 'localhost') {
        try {
            $pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e2) {
            // substituir exceção para que a mensagem abaixo seja da tentativa TCP
            $e = $e2;
        }
    }

    // Se nao for possível estabelecer a ligação, registar e mostrar uma página amigável com a mensagem do PDO.
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        render_db_error_page_and_log("Erro de ligação: " . $e->getMessage());
    }
}

// Obter o nome da associação para usar no SMTP_FROM_NAME
try {
    $stmt = $pdo->prepare("SELECT nome FROM associacoes WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $associacao = $stmt->fetch(PDO::FETCH_ASSOC);
    $nome_associacao = $associacao ? $associacao['nome'] : '';
} catch (PDOException $e) {
    // Se as variáveis de BD não estiverem configuradas, redirecionar o utilizador para o instalador. Caso contrário, mostrar erro.
    if (!$db_configured) {
        $msg = "Não foi encontrada nenhuma instalação ou base de dados válida";
        header("Location: install.php?notice=" . urlencode($msg));
        exit;
    }

    render_db_error_page_and_log("Erro na consulta inicial: " . $e->getMessage());
}



// Registo de erros para este ficheiro: escrever erros que se originem neste ficheiro para error.log
if (!function_exists('config_file_log_error')) {
    function config_file_log_error($level, $message, $file = '', $line = 0, $trace = '')
    {
        $logfile = defined('PROJECT_ROOT') ? PROJECT_ROOT . '/error.log' : __DIR__ . '/error.log';
        $entry = date('c') . " [$level] $message in $file:$line" . PHP_EOL;
        if ($trace) $entry .= $trace . PHP_EOL;
        $entry .= PHP_EOL;
        @file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
    }

    // Registar erros padrão do PHP (apenas registar os que se originem neste ficheiro)
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if ($errfile === __FILE__) {
            $levels = [
                E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING', E_PARSE => 'E_PARSE',
                E_NOTICE => 'E_NOTICE', E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING',
                E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING', E_USER_NOTICE => 'E_USER_NOTICE',
            ];
            $level = $levels[$errno] ?? 'E_UNKNOWN';
            config_file_log_error($level, $errstr, $errfile, $errline);
        }
        // Deixar o handler interno do PHP correr também
        return false;
    });

    // Registar exceções não apanhadas (apenas as que se originem neste ficheiro)
    set_exception_handler(function ($e) {
        $file = $e->getFile();
        if ($file === __FILE__) {
            config_file_log_error('UncaughtException', $e->getMessage(), $file, $e->getLine(), $e->getTraceAsString());
        }
        // Se display_errors estiver ativado, deixar o PHP mostrar a exceção; caso contrário, sair
        if (ini_get('display_errors')) {
            throw $e;
        }
        exit(1);
    });

    // Registar shutdown function para capturar erros fatais (apenas os que se originem neste ficheiro)
    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err && isset($err['file']) && $err['file'] === __FILE__) {
            $msg = $err['message'] ?? 'shutdown';
            $file = $err['file'] ?? __FILE__;
            $line = $err['line'] ?? 0;
            config_file_log_error('Shutdown', $msg, $file, $line);
        }
    });
}

// Definir o nome da associação como constante
define('ASSOCIACAO_NOME', $associacao['nome'] ?? '');