<?php
function verificarSessao() {
    // Se já está na página de login, não redirecione
    if (basename($_SERVER['PHP_SELF']) == 'login.php') {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Se não está logado, redireciona para login
    if (empty($_SESSION['loggedin'])) {
        // Limpa a sessão antes de redirecionar
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        // Redireciona para login SEM loop
        if (basename($_SERVER['PHP_SELF']) != 'login.php') {
            header("Location: login.php");
            exit();
        }
    }
}
?>