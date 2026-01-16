<?php
    include("config.php");
    require_once 'auth.php';
verificarSessao();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']))
    {
        $id = (int) $_POST['id'];
        $nome = trim($_POST['nome']);
        $tipo = trim($_POST['tipo']);
    
        try
        {
            // Query de atualiza��o
            $query = "UPDATE contas SET nome = :nome, tipo = :tipo, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($query);
    
            // Executa a consulta com par�metros
            $stmt->execute([
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':id' => $id
            ]);
    
            header("Location: index.php?page=ListarContas");
        }
        catch (PDOException $e)
        {
            // Erro na execu��o
            header("Location: index.php?page=ListarContas&error=" . urlencode("Erro ao atualizar a conta: " . $e->getMessage()));
        }
        exit();
    }
?>
