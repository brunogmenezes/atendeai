<?php
include("config.php");
try {
    $stmt = $pdo->query("SELECT 1 FROM compras LIMIT 1");
    echo "Table exists";
} catch (Exception $e) {
    echo "Table does not exist: " . $e->getMessage();
}
?>
