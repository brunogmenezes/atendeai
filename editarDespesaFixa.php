<?php
include("config.php");
require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $descricao = trim($_POST['descricao']);
    $valor = trim($_POST['valor']);

    try {
        // Query de atualiza��o
        $query = "UPDATE despesasfixas SET descricao = :descricao, valor = :valor WHERE id = :id";
        $stmt = $pdo->prepare($query);

        // Executa a consulta com par�metros
        $stmt->execute([
            ':descricao' => $descricao,
            ':valor' => $valor,
            ':id' => $id
        ]);

        header("Location: index.php?page=ListarDespesasFixas");
    } catch (PDOException $e) {
        // Erro na execu��o
        header("Location: index.php?page=ListarDespesasFixas&error=" . urlencode("Erro ao atualizar o produto: " . $e->getMessage()));
    }
    exit();
}
?>
