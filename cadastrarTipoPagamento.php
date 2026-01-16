<?php
	
	include 'config.php';
	include("funcoes.php");

	require_once 'auth.php';
verificarSessao();
	
	// Verificando se o formulário foi enviado
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
	    // Coletando os dados do formulário
	    $nome	= trim($_POST['nome']);
	    $conta	= trim($_POST['conta']);
	
	    try {
	        // Inserindo a nova conta
	        $query = "INSERT INTO tipopagamento (nome, conta, data_atualizacao) 
	                  VALUES (:nome, :conta, NOW())";
	        $stmt = $pdo->prepare($query);
	        $stmt->execute([
	            ':nome' => $nome,
	            ':conta' => $conta
	        ]);
	
	        // Recuperar o ID do cliente inserido
	        $clienteId = $pdo->lastInsertId();
	
	        // Registra a auditoria
	        registrarAuditoria(
	            $_SESSION['user_id'],
	            'Cadastrou um tipo de pagamento',
	            $_SERVER['REMOTE_ADDR'],
	            ['idCliente' => $clienteId]
	        );
	
	        // Redirecionando para a página de gerenciamento de clientes
	        header('Location: index.php?page=ListarTipoPagamento');
	        exit;
	    } catch (Exception $e) {
	        die("Erro ao cadastrar tipo de pagamento: " . $e->getMessage());
	    }
	}
?>
