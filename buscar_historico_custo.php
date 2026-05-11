<?php
include("config.php");
require_once 'auth.php';
verificarSessao();

header('Content-Type: application/json');

$produtoId = $_GET['produto_id'] ?? null;

if (!$produtoId) {
    echo json_encode(['error' => 'ID do produto não fornecido']);
    exit;
}

try {
    $query = "
        SELECT 
            c.data_compra,
            ic.preco_custo,
            ic.preco_custo_diluido
        FROM itens_compra ic
        JOIN compras c ON ic.compra_id = c.id
        WHERE ic.produto_id = :produto_id
        ORDER BY c.data_compra ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':produto_id', $produtoId);
    $stmt->execute();
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($historico);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
