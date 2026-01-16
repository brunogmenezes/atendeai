<?php
include 'config.php';
include("funcoes.php");

require_once 'auth.php';
verificarSessao();

// Verificando se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletando os dados do formulário
    $nome = trim($_POST['nome']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $telefone = trim($_POST['telefone']);

    try {

        // Validando a data de nascimento
        $data_nascimento_formatada = date('Y-m-d', strtotime($data_nascimento));
        if (!$data_nascimento_formatada) {
            throw new Exception("Data de nascimento inválida!");
        }

        // Inserindo o novo cliente
        $query = "INSERT INTO clientes (nome, data_nascimento, telefone) 
                  VALUES (:nome, :data_nascimento, :telefone)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':nome' => $nome,
            ':data_nascimento' => $data_nascimento_formatada,
            ':telefone' => $telefone
        ]);

        // Recuperar o ID do cliente inserido
        $clienteId = $pdo->lastInsertId();

        // Registra a auditoria
        registrarAuditoria(
            $_SESSION['user_id'],
            'Cadastrou um cliente',
            $_SERVER['REMOTE_ADDR'],
            ['idCliente' => $clienteId]
        );

        // Redirecionando para a página de gerenciamento de clientes
        header('Location: index.php?page=ListarClientes');
        exit;
    } catch (Exception $e) {
        die("Erro ao cadastrar cliente: " . $e->getMessage());
    }
}
?>
