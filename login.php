<?php
    session_start();
    include("config.php");
    include("funcoes.php");

    // Buscar dados da empresa para exibição na tela de login
    $dadosEmpresa = buscarDadosEmpresa();
    $empresaNome = !empty($dadosEmpresa['nome']) ? $dadosEmpresa['nome'] : 'ATENDE AI';
    $empresaLogo = !empty($dadosEmpresa['logo']) ? $dadosEmpresa['logo'] : null;
    $temLogo = !empty($empresaLogo) && file_exists(__DIR__ . '/uploads/' . $empresaLogo);

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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($empresaNome) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .login-card {
            background: #ffffff;
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            width: 100%;
            max-width: 440px;
            padding: 2.25rem 2.25rem;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #3b82f6, #6366f1, #8b5cf6);
        }
        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            text-align: center;
        }
        .company-logo {
            max-height: 125px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
            image-rendering: -webkit-optimize-contrast;
            transition: transform 0.3s ease;
        }
        .company-logo:hover {
            transform: scale(1.02);
        }
        .logo-placeholder-icon {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #4338ca;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 4px 10px rgba(67, 56, 202, 0.15);
        }
        .company-title {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.35rem;
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
            text-align: center;
            letter-spacing: -0.3px;
        }
        .company-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .form-floating > .form-control {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding-left: 2.75rem;
        }
        .form-floating > .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 5;
            font-size: 1.05rem;
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 1.15rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            color: #ffffff;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
            color: #ffffff;
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .login-footer {
            margin-top: 1.75rem;
            text-align: center;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo / Identidade da Empresa -->
        <div class="logo-wrapper">
            <?php if ($temLogo): ?>
                <img src="uploads/<?= htmlspecialchars($empresaLogo) ?>" alt="<?= htmlspecialchars($empresaNome) ?>" class="company-logo">
            <?php else: ?>
                <div class="logo-placeholder-icon">
                    <i class="fas fa-building"></i>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="company-title"><?= htmlspecialchars($empresaNome) ?></h1>
        <p class="company-subtitle">Sistema de Gestão e Vendas</p>

        <!-- Formulário de Autenticação -->
        <form method="POST" autocomplete="off">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="input-group-custom">
                <i class="fas fa-user input-icon"></i>
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Usuário" required autofocus autocomplete="off">
                    <label for="username" style="padding-left: 2.75rem;">Usuário</label>
                </div>
            </div>

            <div class="input-group-custom">
                <i class="fas fa-lock input-icon"></i>
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                    <label for="password" style="padding-left: 2.75rem;">Senha</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100 mt-2">
                <i class="fas fa-sign-in-alt me-2"></i> Entrar
            </button>
        </form>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mt-3 py-2 px-3 rounded-3" role="alert" style="font-size: 0.9rem;">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> <strong><?= htmlspecialchars($empresaNome) ?></strong> &bull; AtendeAI
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
