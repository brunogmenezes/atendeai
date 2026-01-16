<?php

require_once 'auth.php';
verificarSessao();

header('Content-Type: application/json');

// Habilita exibi��o de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializa a resposta
$response = ['success' => false, 'message' => ''];

try {
    require 'config.php';
    
    // Verifica se a conex�o est� ativa
    if (!$pdo) {
        throw new Exception("Erro: Conex�o com o banco de dados n�o estabelecida");
    }

    // Consulta os produtos mais vendidos
    $query = "
    SELECT 
        p.nome AS produto,
        SUM(iv.quantidade) AS total_vendido
    FROM 
        itens_venda iv
    JOIN 
        produtos p ON iv.produto_id = p.id
    JOIN 
        vendas v ON iv.venda_id = v.id
    WHERE 
        v.estornado = false
    GROUP BY 
        p.nome
    ORDER BY 
        total_vendido DESC
    LIMIT 5";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resultados)) {
        throw new Exception("Nenhum dado encontrado na consulta");
    }

    // Prepara os dados para o gr�fico
    $labels = [];
    $data = [];
    $backgroundColors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ];

    foreach ($resultados as $row) {
        $labels[] = $row['produto'];
        $data[] = (int)$row['total_vendido'];
    }

    // Monta a resposta de sucesso
    $response = [
        'success' => true,
        'labels' => $labels,
        'data' => $data,
        'backgroundColor' => array_slice($backgroundColors, 0, count($labels))
    ];

} catch (PDOException $e) {
    $response['message'] = "Erro no banco de dados: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Garante que qualquer sa�da anterior seja limpa
if (ob_get_length()) ob_clean();

// Retorna a resposta como JSON
echo json_encode($response);
exit;
?>