<?php
include("config.php");
$compraId = $_GET['id'] ?? '5'; // Exemplo
$desc = "Compra de Estoque (ID: $compraId)%";
try {
    $stmt = $pdo->prepare("SELECT id, descricao, tipo, conta FROM financeiro WHERE descricao LIKE :desc");
    $stmt->execute([':desc' => $desc]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
