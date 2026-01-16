<?php
   
    include 'config.php';
    include("funcoes.php");

    require_once 'auth.php';
verificarSessao();

    // Verificando se o formul�rio foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // Coletando os dados do formul�rio
        $descricao = trim($_POST['nome']);
        $valor = trim($_POST['valor']);
    
        try {
    
            // Inserindo o novo lan�amento
            $query = "INSERT INTO despesasfixas (descricao, valor, data_lancamento) 
                      VALUES (:descricao, :valor, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':descricao' => $descricao,
                ':valor' => $valor
            ]);
    
            // Recuperar o ID do lan�amento inserido
            //$clienteId = $pdo->lastInsertId();
    
            // Registra a auditoria
            //registrarAuditoria(
            //    $_SESSION['user_id'],
            //    'Cadastrou um lan�amento',
            //    $_SERVER['REMOTE_ADDR'],
            //    ['idCliente' => $clienteId]
            //);
    
            // Redirecionando para a p�gina de gerenciamento de clientes
            header('Location: index.php?page=ListarDespesasFixas');
            exit;
        } catch (Exception $e) {
            die("Erro ao cadastrar lan�amento: " . $e->getMessage());
        }
    }
?>
