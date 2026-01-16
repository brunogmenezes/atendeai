<?php
    session_start();
    include("config.php");
    include("funcoes.php");

    // Gera o token CSRF se não existir
    if (empty($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {

        // Verifica o token CSRF
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
        {
            registrarAuditoria(
                null,
                'Tentativa de Login com CSRF inválido',
                $_SERVER['REMOTE_ADDR'],
                ['username' => $_POST['username'] ?? '']
            );
            die("Token de segurança inválido.");
            header("Location: login.php");
            exit;
        }

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Evitar SQL Injection
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password']))
        {
            // Protege contra sequestro de sessão
            session_regenerate_id(true);
        
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];

            // Registra a auditoria
            registrarAuditoria(
                $user['id'],
                'Fez Login',
                $_SERVER['REMOTE_ADDR'],
                ['username' => $user['username']]
            );

            header("Location: index.php");
            exit;
        }
        else
        {
            registrarAuditoria(
                null,
                'Tentativa de Login falhou',
                $_SERVER['REMOTE_ADDR'],
                ['username' => $username]
            );
            $error = "Usuário ou senha inválidos.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Estoque e Vendas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Login</h2>
            <form method="POST">
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus="" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center mt-3"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="mt-3 text-center">
                <small>&copy; <?php echo date('Y'); ?> - ATENDE AI - Sistema de Estoque e Vendas</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
