<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['funcao'] == 'EditarColaborador')
{
    // Garantir que a sessão tenha o username correto
    if (!isset($_SESSION['username']) || empty($_SESSION['username']))
    {
        $_SESSION['error_message'] = "Usuário não autenticado.";
        header("Location: login.php");  // Redireciona para página de login
        exit;
    }

    $paginaPos = $_POST['page'] ?? '';
    
    // Se temos um ID de colaborador no POST, estamos editando um colaborador da lista (Salários/Colaboradores)
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $nome = trim($_POST['nome'] ?? '');
        $salario = trim($_POST['salario'] ?? '');
        $data_contratacao = trim($_POST['data_contratacao'] ?? '');
        
        $data_contratacao_formatada = !empty($data_contratacao) ? date('Y-m-d', strtotime($data_contratacao)) : null;

        $sqlColaborador = "UPDATE colaboradores SET nome = :nome, salario = :salario, data_contratacao = :data_contratacao WHERE id = :id"; $perfil_id = !empty($_POST['perfil_id']) ? intval($_POST['perfil_id']) : null; $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1'; $sqlUsuario = "UPDATE usuarios SET perfil_id = :perfil_id, \"isAdmin\" = :is_admin WHERE id = (SELECT idusuario FROM colaboradores WHERE id = :id)"; $stmtUsuario = $pdo->prepare($sqlUsuario);
        $stmt = $pdo->prepare($sqlColaborador);
        
        try {
            $pdo->beginTransaction(); $stmtUsuario->execute([':perfil_id' => $perfil_id, ':is_admin' => $is_admin ? 1 : 0, ':id' => $id]); $stmt->execute([
                ':nome' => $nome,
                ':salario' => $salario,
                ':data_contratacao' => $data_contratacao_formatada,
                ':id' => $id
            ]);
            
            $pdo->commit(); if (isset($_SESSION['user_id'])) { $stmtCheckSelf = $pdo->prepare("SELECT idusuario FROM colaboradores WHERE id = :id"); $stmtCheckSelf->execute([':id' => $id]); $selfColab = $stmtCheckSelf->fetch(PDO::FETCH_ASSOC); if ($selfColab && $selfColab['idusuario'] == $_SESSION['user_id']) { unset($_SESSION['permissoes']); unset($_SESSION['is_admin']); } } $_SESSION['success_message'] = "Colaborador atualizado com sucesso!";
            header("Location: index.php" . (!empty($paginaPos) ? "?page=" . urlencode($paginaPos) : ""));
            exit;
        } catch (Exception $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); }
            die("Erro ao atualizar colaborador: " . $e->getMessage());
        }
    } else {
        // Fallback: Editando o próprio perfil logado (caso esse formulário seja acessado de forma genérica)
        $username = $_SESSION['username'];  // Username da sessão
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Verificar se foi feito upload de um arquivo
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK)
        {
            // Gerar um nome aleatório para o arquivo
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION); // Obtém a extensão do arquivo
            $nomeArquivo = uniqid('fotoperfil_', true) . '.' . $extensao; // Nome único com extensão

            // Diretório de destino para armazenar a imagem
            $diretorioDestino = 'uploads/';

            // Caminho completo do arquivo
            $caminhoDestino = $diretorioDestino . $nomeArquivo;

            // Verificar se a pasta existe, caso contrário, criar
            if (!file_exists($diretorioDestino))
            {
                mkdir($diretorioDestino, 0777, true);
            }

            // Tentar mover o arquivo para o diretório de destino
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino))
            {
                // Imagem enviada com sucesso! (Pode ser estendida para salvar no banco)
            }
            else
            {
                die("Erro ao mover a imagem.");
            }
        }

        // Verificar se o username foi recuperado corretamente
        if (empty($username))
        {
            die("Erro: Nenhum username encontrado na sessão.");
        }

        // Atualizar os dados na tabela colaboradores
        $sqlColaborador = "UPDATE colaboradores SET nome = :nome" . (empty($cpf) ? "" : ", cpf = :cpf") . " WHERE idusuario = (SELECT id FROM usuarios WHERE username = :username)";
        $stmt = $pdo->prepare($sqlColaborador);
        $stmt->bindParam(':nome', $nome);
        
        // Se o CPF não estiver vazio, vinculamos o parâmetro
        if (!empty($cpf))
        {
            $stmt->bindParam(':cpf', $cpf);
        }
        
        $stmt->bindParam(':username', $username);

        try
        {
            $stmt->execute();
        }
        catch (PDOException $e)
        {
            die("Erro de execução na query: " . $e->getMessage());
        }

        // Se a senha foi preenchida, atualizar na tabela usuarios
        if (!empty($password))
        {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);  // Hash da senha
            $sqlSenha = "UPDATE usuarios SET password = :password WHERE username = :username";
            $stmt = $pdo->prepare($sqlSenha);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':username', $username);

            try {
                $stmt->execute();
            } catch (PDOException $e) {
                die("Erro de execução na query de senha: " . $e->getMessage());
            }
        }

        // Reinicia a sessão caso a senha tenha sido alterada
        if (!empty($password))
        {
            session_regenerate_id(true); // Protege contra sequestro de sessão
        }

        $_SESSION['success_message'] = "Dados atualizados com sucesso!";
        header("Location: index.php" . (!empty($paginaPos) ? "?page=" . urlencode($paginaPos) : ""));
        exit;
    }
}
?>
