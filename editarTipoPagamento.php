<?php
    include("config.php");

    require_once 'auth.php';
verificarSessao();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']))
    {
        $id = (int) $_POST['id'];
        $nome = trim($_POST['nome']);
        $conta = trim($_POST['conta']);
    
        try
        {
            // Query de atualiza��o
            $query = "UPDATE tipopagamento SET nome = :nome, conta = :conta, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($query);
    
            // Executa a consulta com par�metros
            $stmt->execute([
                ':nome' => $nome,
                ':conta' => $conta,
                ':id' => $id
            ]);
    
            header("Location: index.php?page=ListarTipoPagamento");
        }
        catch (PDOException $e)
        {
            // Erro na execu��o
            header("Location: index.php?page=ListarTipoPagamento&error=" . urlencode("Erro ao atualizar a conta: " . $e->getMessage()));
        }
        exit();
    }
?>
