<?php
// Segurança extra para sessão
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Regenerar ID de sessão após login para evitar fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Redirect to login if not logged in and not on login.php
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?> - <?= ASSOCIACAO_NOME ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/admintools.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="<?php echo $base_url; ?>assets/img/favicon.ico">
    <meta property="og:image" content="<?php echo $base_url; ?>assets/img/GestSocLogo.png" />
    <script src="<?php echo $base_url; ?>assets/js/scripts.js" defer></script>
</head>
<body>
<script src="<?php echo $base_url; ?>assets/js/voltar-topo.js"></script>
<div id="menu">
    <div class="session-menu">
    <?php if (isset($_SESSION['user_id'])): ?>
        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?> | <a href="<?php echo $base_url; ?>login/logout.php">Sair</a>
    <?php else: ?>
        <p><a href="<?php echo $base_url; ?>login/login.php">Entrar</a></p>
    <?php endif; ?>
    </div>
<div class="navbar">
    <button class="dropbtn-home"><a href="<?php echo $base_url; ?>index.php"><i class="fa-solid fa-house"></i></a></button>
    
    <div class="dropdown">
        <button class="dropbtn"><i class="fa-solid fa-user-group"></i> Sócios<i class="fa fa-caret-down"></i></button>
        <div class="dropdown-content">
            <a href="<?php echo $base_url; ?>socios/gerir_socios.php">Lista de Sócios</a>
            <a href="<?php echo $base_url; ?>socios/criar_socio.php">Novo Sócio</a>
            <a href="<?php echo $base_url; ?>socios/editar_socio.php">Editar Sócio</a>
            <a href="<?php echo $base_url; ?>socios/aniversarios.php">Aniversários</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropbtn"><i class="fa-solid fa-coins"></i> Quotas<i class="fa fa-caret-down"></i></button>
        <div class="dropdown-content">
            <a href="<?php echo $base_url; ?>quotas/gerar_quotas.php">Gerar Quotas</a>
            <a href="<?php echo $base_url; ?>quotas/gerir_pagamentos.php">Pagamentos</a>
            <a href="<?php echo $base_url; ?>quotas/receitas.php">Receitas</a>
            <a href="<?php echo $base_url; ?>quotas/balanco.php">Balanço de Quotas</a>
        </div>
    </div>
    <div class="dropdown hide">
        <button class="dropbtn"><i class="fa-solid fa-gear"></i> Configurações<i class="fa fa-caret-down"></i></button>
        <div class="dropdown-content">
            <a href="<?php echo $base_url; ?>admin/editar_associacao.php?id=1">Geral</a>
            <a href="<?php echo $base_url; ?>admin/gerir_utilizadores.php">Utilizadores</a>
            <a href="<?php echo $base_url; ?>admin/configurar_quotas.php">Quotas</a>
            <a href="<?php echo $base_url; ?>admin/email_config.php">Email</a>
            <a href="<?php echo $base_url; ?>admin/backup.php">Cópia de Segurança</a>
            <a href="<?php echo $base_url; ?>admin/admintools.php">⚠️ Programador</a>
        </div>
    </div>
    
        <button class="dropbtn-home" style="display:flex;align-items:center;gap:6px;">
            <a href="<?php echo $base_url; ?>admin/version.php"><i class="fa-solid fa-circle-info" style="font-size: 1.25em;"></i></a>
        </button>
    
    <button id="menu-toggle" class="menu-toggle" aria-expanded="false" aria-label="Menu">
        <i class="fa fa-bars" aria-hidden="true" style="font-size:1.4em; color:white;"></i>
    </button>
</div>
<img src="<?php echo $base_url; ?>assets/img/SocGestLogo.png" alt="SocGest Logo" class="menu-logo-mobile">
</div>
</div>
</div>