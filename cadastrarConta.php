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
	    $tipo	= trim($_POST['tipo']);
	    $saldo	= trim($_POST['saldo']);
	
	    try {
	        // Inserindo a nova conta
	        $query = "INSERT INTO contas (nome, tipo, saldo, data_atualizacao) 
	                  VALUES (:nome, :tipo, :saldo, NOW())";
	        $stmt = $pdo->prepare($query);
	        $stmt->execute([
	            ':nome' => $nome,
	            ':tipo' => $tipo,
	            ':saldo' => $saldo
	        ]);
	
	        // Recuperar o ID do cliente inserido
	        $clienteId = $pdo->lastInsertId();
	
	        // Registra a auditoria
	        registrarAuditoria(
	            $_SESSION['user_id'],
	            'Cadastrou uma conta',
	            $_SERVER['REMOTE_ADDR'],
	            ['idCliente' => $clienteId]
	        );
	
	        // Redirecionando para a página de gerenciamento de clientes
	        header('Location: index.php?page=ListarContas');
	        exit;
	    } catch (Exception $e) {
	        die("Erro ao cadastrar conta: " . $e->getMessage());
	    }
	}
?>
