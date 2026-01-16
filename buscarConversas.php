<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

// Verifica se a requisição é AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    try {
        $conversas = buscarConversas();
        // Retorna os dados em JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $conversas]);
    } catch (Exception $e) {
        // Retorna erro em JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar conversas: ' . $e->getMessage()]);
    }
} else {
    // Se não for AJAX, retorna um JSON de erro
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acesso direto não permitido.']);
}