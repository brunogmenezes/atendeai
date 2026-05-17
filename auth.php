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

/**
 * Verifica se o usuário autenticado possui a permissão requerida.
 * Administradores master (isAdmin = true) possuem acesso automático e total.
 *
 * @param string $permissaoSlug Slug da permissão (ex: 'ver_dashboard', 'acessar_pdv')
 * @return bool True se possuir permissão, False caso contrário
 */
function temPermissao($permissaoSlug) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Se o usuário não está logado, não tem permissão
    if (empty($_SESSION['loggedin']) || empty($_SESSION['user_id'])) {
        return false;
    }

    // Se as permissões ainda não estão cacheadas na sessão, busca do banco de dados
    if (!isset($_SESSION['permissoes'])) {
        global $pdo;

        if (!isset($pdo)) {
            include_once __DIR__ . '/config.php';
        }

        try {
            // Buscar informações de privilégio do usuário
            $stmtUser = $pdo->prepare("SELECT \"isAdmin\", \"perfil_id\" FROM usuarios WHERE id = :id");
            $stmtUser->execute([':id' => $_SESSION['user_id']]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            // Suportar variações de case do PostgreSQL
            $isAdmin = false;
            $perfilId = null;

            if ($user) {
                $isAdmin = isset($user['isAdmin']) ? $user['isAdmin'] : ($user['isadmin'] ?? false);
                $perfilId = $user['perfil_id'] ?? null;
            }

            $_SESSION['is_admin'] = (bool)$isAdmin;

            if ($isAdmin) {
                // Administrador tem bypass total
                $_SESSION['permissoes'] = ['all'];
            } else if ($perfilId) {
                // Busca as permissões atribuídas ao grupo/perfil
                $stmtPerms = $pdo->prepare("
                    SELECT p.nome 
                    FROM permissoes p
                    INNER JOIN perfil_permissoes pp ON pp.permissao_id = p.id
                    WHERE pp.perfil_id = :perfil_id
                ");
                $stmtPerms->execute([':perfil_id' => $perfilId]);
                $_SESSION['permissoes'] = $stmtPerms->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $_SESSION['permissoes'] = [];
            }
        } catch (Exception $e) {
            $_SESSION['permissoes'] = [];
        }
    }

    // Se for admin com bypass, tem permissão total
    if (in_array('all', $_SESSION['permissoes'])) {
        return true;
    }

    // Verifica se o slug está contido nas permissões autorizadas
    return in_array($permissaoSlug, $_SESSION['permissoes']);
}
?>