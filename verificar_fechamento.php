<?php
include 'config.php';
include 'funcoes.php';

$data = $_GET['data'] ?? date('Y-m-d');
$existe = existeFechamentoDoDia($data);

header('Content-Type: application/json');
echo json_encode(['existe' => $existe]);
?>