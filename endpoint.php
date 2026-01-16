<?php

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');
require 'config.php';

try {
    // Consulta SQL para buscar entradas e saídas separadas por mês
    $sql = "
        SELECT
            tipo_ajustado AS tipo,
            mes,
            SUM(valor) AS total 
        FROM
            (
            SELECT
                CASE
                    WHEN tipo = 3 THEN 1 -- trata estorno como entrada negativa
                    ELSE tipo 
                END AS tipo_ajustado,
                EXTRACT(MONTH FROM data_lancamento) AS mes,
                CASE
                    WHEN tipo = 3 THEN -valor -- subtrai estorno
                    ELSE valor 
                END AS valor 
            FROM
                financeiro 
            WHERE
                tipo IN (1, 2, 3) -- ou outros tipos, se necessário
            ) AS ajustado 
        GROUP BY
            tipo_ajustado,
            mes 
        ORDER BY
            mes,
            tipo_ajustado;
    ";
    
    $stmt = $pdo->query($sql);
    if (!$stmt) {
        throw new Exception("Erro ao executar a consulta SQL.");
    }

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar os dados para o gráfico
    $entradas = array_fill(1, 12, 0); // 12 meses com valor 0 inicial
    $saidas = array_fill(1, 12, 0);
    $saldoAcumulado = array_fill(1, 12, 0); // Array para saldo acumulado

    foreach ($resultados as $row) {
        if ($row['tipo'] == 1) {
            $entradas[(int)$row['mes']] = (float)$row['total'];
        } elseif ($row['tipo'] == 2) {
            $saidas[(int)$row['mes']] = (float)$row['total'];
        }
    }

    // Calcular saldo mensal e acumulado com 2 casas decimais
    $saldoAnterior = 0;
    for ($mes = 1; $mes <= 12; $mes++) {
        $saldoMensal = $entradas[$mes] - $saidas[$mes];
        $saldoAcumulado[$mes] = round($saldoAnterior + $saldoMensal, 2);
        $saldoAnterior = $saldoAcumulado[$mes];
        
        // Aplicar formatação de 2 casas decimais para entradas e saídas
        $entradas[$mes] = round($entradas[$mes], 2);
        $saidas[$mes] = round($saidas[$mes], 2);
    }

    echo json_encode([
        'entradas' => array_values($entradas),
        'saidas' => array_values($saidas),
        'saldoAcumulado' => array_values($saldoAcumulado)
    ], JSON_NUMERIC_CHECK);
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do financeiro: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao carregar dados. Por favor, tente novamente mais tarde.']);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao processar a requisição.']);
}
?>