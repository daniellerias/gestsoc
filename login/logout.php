<?php
// logout.php
session_start();
session_unset();
session_destroy();
$msg = urlencode('Sessão encerrada');
header('Location: ../login/login.php?msg=' . $msg);
exit;
?>
