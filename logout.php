<?php
require_once 'config.php';
require_once 'funcoes.php';

session_start();

// Registrar logout na auditoria
if (!empty($_SESSION['user_id'])) {
    registrarAuditoria(
        $_SESSION['user_id'],
        'Logout do sistema',
        $_SERVER['REMOTE_ADDR']
    );
}

// Limpa completamente a sessão
$_SESSION = array();

// Destrói o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redireciona para login
header("Location: login.php");
exit();
?>