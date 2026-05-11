<?php
include("config.php");
try {
    $stmt = $pdo->query("SELECT id, descricao, tipo, conta FROM financeiro ORDER BY id DESC LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
