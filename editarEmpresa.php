<?php
require_once 'config.php';
require_once 'funcoes.php';

require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id' => $_POST['id'],
        'nome' => $_POST['nome'],
        'cnpj' => preg_replace('/[^0-9]/', '', $_POST['cnpj']),
        'endereco' => $_POST['endereco'],
        'telefone' => preg_replace('/[^0-9]/', '', $_POST['telefone']),
        'data_atualizacao' => date('Y-m-d H:i:s')
    ];

    try {
        $stmt = $pdo->prepare("UPDATE empresa SET 
                              nome = :nome, 
                              cnpj = :cnpj, 
                              endereco = :endereco, 
                              telefone = :telefone,
                              data_atualizacao = :data_atualizacao
                              WHERE id = :id");
        $stmt->execute($dados);
        
        header("Location: index.php?page=ListarEmpresa");
        exit;
    } catch (PDOException $e) {
        header("Location: index.php?page=ListarEmpresa&status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}