<?php

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');
require 'config.php';

try {
    $query = "
        SELECT 
            tipo,
            EXTRACT(MONTH FROM data_lancamento) AS mes,
            SUM(valor) AS total
        FROM 
            financeiro
        GROUP BY 
            tipo, mes
        ORDER BY 
            mes, tipo";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organiza os dados para o gr�fico
    $meses = [];
    $tipos = [];
    $dados = [];

    foreach ($resultados as $row) {
        if (!in_array($row['mes'], $meses)) {
            $meses[] = $row['mes'];
        }
        if (!in_array($row['tipo'], $tipos)) {
            $tipos[] = $row['tipo'];
        }
        $dados[$row['tipo']][$row['mes']] = (float)$row['total'];
    }

    // Preenche meses faltantes para cada tipo
    foreach ($tipos as $tipo) {
        foreach ($meses as $mes) {
            if (!isset($dados[$tipo][$mes])) {
                $dados[$tipo][$mes] = 0;
            }
        }
        ksort($dados[$tipo]);
    }

    // Nomes dos meses
    $nomesMeses = [
        '1' => 'Janeiro', '2' => 'Fevereiro', '3' => 'Mar�o',
        '4' => 'Abril', '5' => 'Maio', '6' => 'Junho',
        '7' => 'Julho', '8' => 'Agosto', '9' => 'Setembro',
        '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
    ];

    $labels = [];
    foreach ($meses as $mes) {
        $labels[] = $nomesMeses[$mes] ?? "M�s $mes";
    }

    // Prepara datasets para Chart.js
    $datasets = [];
    $cores = [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ];

    foreach ($tipos as $index => $tipo) {
        $valores = [];
        foreach ($meses as $mes) {
            $valores[] = $dados[$tipo][$mes];
        }

        $datasets[] = [
            'label' => $tipo,
            'data' => $valores,
            'backgroundColor' => $cores[$index % count($cores)],
            'borderColor' => str_replace('0.7', '1', $cores[$index % count($cores)]),
            'borderWidth' => 1
        ];
    }

    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'datasets' => $datasets
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>