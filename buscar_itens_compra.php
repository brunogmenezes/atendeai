<?php
include("config.php");
include("funcoes.php");

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');

$compraId = $_GET['id'] ?? null;

if (!$compraId) {
    echo json_encode(['status' => 'error', 'message' => 'ID da compra não informado.']);
    exit;
}

try {
    // 1. Buscar dados da compra
    $stmtCompra = $pdo->prepare("SELECT id, fornecedor, total, data_compra FROM compras WHERE id = :id");
    $stmtCompra->bindValue(':id', $compraId);
    $stmtCompra->execute();
    $compra = $stmtCompra->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        throw new Exception("Compra não encontrada.");
    }

    // Formatar data
    $compra['data'] = date('d/m/Y H:i', strtotime($compra['data_compra']));

    // 2. Buscar itens da compra
    $stmtItens = $pdo->prepare("
        SELECT 
            ic.quantidade, 
            ic.preco_custo, 
            ic.subtotal, 
            p.nome 
        FROM itens_compra ic
        JOIN produtos p ON ic.produto_id = p.id
        WHERE ic.compra_id = :compra_id
    ");
    $stmtItens->bindValue(':compra_id', $compraId);
    $stmtItens->execute();
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'compra' => $compra,
        'itens' => $itens
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
