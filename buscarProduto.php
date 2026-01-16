<?php
include("config.php");
require_once 'auth.php';
verificarSessao();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Consulta no banco de dados
    $query = $conn->prepare("SELECT * FROM produtos WHERE id = :id");
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $produto = $query->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        echo json_encode($produto);
    } else {
        echo json_encode(["erro" => "Produto n�o encontrado"]);
    }
} else {
    echo json_encode(["erro" => "ID n�o fornecido"]);
}
?>
