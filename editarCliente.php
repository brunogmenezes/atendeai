<?php
    include("config.php");

    require_once 'auth.php';
verificarSessao();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']))
    {
        $id = (int) $_POST['id'];
        $nome = trim($_POST['nome']);
        $data_nascimento = trim($_POST['data_nascimento']);
        $data_nascimento_formatada = date('Y-m-d', strtotime($data_nascimento));
        $telefone = trim($_POST['telefone']);
    
        try
        {
            // Query de atualiza��o
            $query = "UPDATE clientes SET nome = :nome, data_nascimento = :data_nascimento, telefone = :telefone WHERE id = :id";
            $stmt = $pdo->prepare($query);
    
            // Executa a consulta com par�metros
            $stmt->execute([
                ':nome' => $nome,
                ':data_nascimento' => $data_nascimento_formatada,
                ':telefone' => $telefone,
                ':id' => $id
            ]);
    
            header("Location: index.php?page=ListarClientes");
        }
        catch (PDOException $e)
        {
            // Erro na execu��o
            header("Location: index.php?page=ListarClientes&error=" . urlencode("Erro ao atualizar o produto: " . $e->getMessage()));
        }
        exit();
    }
?>
