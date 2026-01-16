<?php
include('config.php');
require_once 'auth.php';
verificarSessao();

try {
    $query = "SELECT id, nome, preco_venda, quantidade FROM produtos WHERE quantidade > 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($produtos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
