<?php
require_once 'auth.php';
verificarSessao();

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

try {
    require 'config.php';
    
    if (!$pdo) {
        throw new Exception("Erro: Conexão com o banco de dados não estabelecida");
    }

    $anoHoje = date('Y');
    $mesHoje = date('m');
    $mesSeguinte = ($mesHoje == 12) ? 1 : $mesHoje + 1;
    $anoSeguinte = ($mesHoje == 12) ? $anoHoje + 1 : $anoHoje;
    $mesSeguinteFormatado = str_pad($mesSeguinte, 2, '0', STR_PAD_LEFT);
    $inicioMes = "$anoHoje-$mesHoje-01";
    $inicioMesSeguinte = "$anoSeguinte-$mesSeguinteFormatado-01";

    $query = "
    SELECT 
        COALESCE(tipo_atendimento, 'presencial') AS tipo,
        COUNT(*) AS total_vendas,
        COALESCE(SUM(total * (1 - COALESCE(desconto, 0) / 100.0)), 0) AS total_valor
    FROM 
        vendas
    WHERE 
        estornado = 'f'
        AND data_venda >= :inicio_mes
        AND data_venda < :inicio_mes_seguinte
    GROUP BY 
        tipo_atendimento";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':inicio_mes' => $inicioMes,
        ':inicio_mes_seguinte' => $inicioMesSeguinte
    ]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $atendimentos = [
        'presencial' => ['label' => 'Presencial', 'qtd' => 0, 'valor' => 0.0],
        'online' => ['label' => 'Online', 'qtd' => 0, 'valor' => 0.0],
    ];

    foreach ($resultados as $row) {
        $tipo = strtolower($row['tipo']);
        if (isset($atendimentos[$tipo])) {
            $atendimentos[$tipo]['qtd'] = (int)$row['total_vendas'];
            $atendimentos[$tipo]['valor'] = (float)$row['total_valor'];
        }
    }

    $labels = [];
    $data = [];
    $valores = [];

    foreach ($atendimentos as $tipo => $info) {
        $labels[] = $info['label'];
        $data[] = $info['qtd'];
        $valores[] = $info['valor'];
    }

    // Cores premium para o gráfico
    $backgroundColors = [
        '#2b5298', // Presencial (azul escuro refinado)
        '#2ecc71'  // Online (verde esmeralda vibrante)
    ];

    $response = [
        'success' => true,
        'labels' => $labels,
        'data' => $data,
        'valores' => $valores,
        'backgroundColor' => $backgroundColors
    ];

} catch (PDOException $e) {
    $response['message'] = "Erro no banco de dados: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

if (ob_get_length()) ob_clean();
echo json_encode($response);
exit;
