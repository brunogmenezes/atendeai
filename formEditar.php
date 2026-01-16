<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['funcao'] == 'EditarColaborador')
{
    // Garantir que a sess�o tenha o username correto
    if (!isset($_SESSION['username']) || empty($_SESSION['username']))
    {
        $_SESSION['error_message'] = "Usu�rio n�o autenticado.";
        header("Location: login.php");  // Redireciona para p�gina de login
        exit;
    }

    $username = $_SESSION['username'];  // Username da sess�o
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $password = trim($_POST['password']);

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
            echo "Imagem enviada com sucesso!";
        }
        else
        {
            die("Erro ao mover a imagem.");
        }
    }
    else
    {
        $nomeArquivo = null; // Caso não tenha sido enviada imagem
    }

    // Verificar se o username foi recuperado corretamente
    if (empty($username))
    {
        die("Erro: Nenhum username encontrado na sess�o.");
    }

    // Atualizar os dados na tabela colaboradores
    $sqlColaborador = "UPDATE colaboradores SET nome = :nome" . (empty($cpf) ? "" : ", cpf = :cpf") . " WHERE idusuario = (SELECT id FROM usuarios WHERE username = :username)";
    $stmt = $pdo->prepare($sqlColaborador);
    $stmt->bindParam(':nome', $nome);
    
    // Se o CPF n�o estiver vazio, vinculamos o par�metro
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
        die("Erro de execu��o na query: " . $e->getMessage());
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
            die("Erro de execu��o na query de senha: " . $e->getMessage());
        }
    }

    // Reinicia a sess�o caso a senha tenha sido alterada
    if (!empty($password))
    {
        session_regenerate_id(true); // Protege contra sequestro de sess�o
    }

    $_SESSION['success_message'] = "Dados atualizados com sucesso!";
    header("Location: index.php");
    exit;
}
?>
