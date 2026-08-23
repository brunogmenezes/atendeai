<?php
require_once 'config.php';
require_once 'funcoes.php';
require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $logoNome = null;

    if (empty($nome) || empty($cnpj) || empty($endereco)) {
        header("Location: index.php?page=ListarEmpresa&status=error&message=" . urlencode("Preencha todos os campos obrigatórios."));
        exit;
    }

    try {
        $diretorioUpload = __DIR__ . '/uploads/';
        if (!is_dir($diretorioUpload)) {
            mkdir($diretorioUpload, 0755, true);
        }

        // Processar upload de novo arquivo de logo se enviado
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['logo']['tmp_name'];
            $fileName = $_FILES['logo']['name'];
            $fileSize = $_FILES['logo']['size'];
            $extensao = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            if (!in_array($extensao, $extensoesPermitidas)) {
                header("Location: index.php?page=ListarEmpresa&status=error&message=" . urlencode("Formato de imagem inválido. Formatos permitidos: JPG, PNG, WEBP, GIF, SVG."));
                exit;
            }

            if ($fileSize > 5 * 1024 * 1024) {
                header("Location: index.php?page=ListarEmpresa&status=error&message=" . urlencode("O arquivo da logo deve ter no máximo 5MB."));
                exit;
            }

            $novoNomeArquivo = "logo_empresa_" . time() . "_" . uniqid() . "." . $extensao;
            $destino = $diretorioUpload . $novoNomeArquivo;

            if (move_uploaded_file($fileTmp, $destino)) {
                $logoNome = $novoNomeArquivo;
            }
        }

        $dados = [
            'nome' => $nome,
            'cnpj' => $cnpj,
            'endereco' => $endereco,
            'telefone' => $telefone,
            'email' => $email,
            'logo' => $logoNome,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        $stmt = $pdo->prepare("INSERT INTO empresa (nome, cnpj, endereco, telefone, email, logo, data_atualizacao) 
                              VALUES (:nome, :cnpj, :endereco, :telefone, :email, :logo, :data_atualizacao)");
        $stmt->execute($dados);

        if (function_exists('registrarAuditoria')) {
            registrarAuditoria(
                $_SESSION['user_id'] ?? null,
                'Cadastrou Nova Empresa',
                $_SERVER['REMOTE_ADDR'] ?? '',
                ['nome' => $nome, 'tem_logo' => !empty($logoNome)]
            );
        }

        header("Location: index.php?page=ListarEmpresa&status=success&message=" . urlencode("Empresa cadastrada com sucesso!"));
        exit;
    } catch (PDOException $e) {
        header("Location: index.php?page=ListarEmpresa&status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}
