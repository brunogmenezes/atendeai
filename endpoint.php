<?php

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');
require 'config.php';

try {
    $anoAtual = (int)date('Y');

    $stmtAnos = $pdo->query("SELECT DISTINCT EXTRACT(YEAR FROM data_lancamento)::int AS ano FROM financeiro ORDER BY ano DESC");
    $anosDisponiveis = $stmtAnos ? array_map('intval', $stmtAnos->fetchAll(PDO::FETCH_COLUMN)) : [];

    if (empty($anosDisponiveis)) {
        $anosDisponiveis = [$anoAtual];
    }

    $anosSelecionados = [];
    if (!empty($_GET['years'])) {
        $anosInformados = explode(',', $_GET['years']);
        foreach ($anosInformados as $ano) {
            $ano = (int)trim($ano);
            if ($ano >= 2000 && $ano <= 2100) {
                $anosSelecionados[] = $ano;
            }
        }
        $anosSelecionados = array_values(array_unique($anosSelecionados));
    }

    if (empty($anosSelecionados)) {
        $anosSelecionados = [in_array($anoAtual, $anosDisponiveis, true) ? $anoAtual : $anosDisponiveis[0]];
    }

    // Mantem apenas anos que existem no financeiro para evitar consultas vazias desnecessarias.
    $anosSelecionados = array_values(array_filter(
        $anosSelecionados,
        fn($ano) => in_array($ano, $anosDisponiveis, true)
    ));

    if (empty($anosSelecionados)) {
        $anosSelecionados = [$anosDisponiveis[0]];
    }

    $placeholders = [];
    $params = [];
    foreach ($anosSelecionados as $idx => $ano) {
        $param = ':ano' . $idx;
        $placeholders[] = $param;
        $params[$param] = $ano;
    }

    $sql = "
        SELECT
            ano,
            tipo_ajustado AS tipo,
            mes,
            SUM(valor) AS total
        FROM (
            SELECT
                EXTRACT(YEAR FROM data_lancamento)::int AS ano,
                EXTRACT(MONTH FROM data_lancamento)::int AS mes,
                CASE
                    WHEN tipo = 3 THEN 1
                    ELSE tipo
                END AS tipo_ajustado,
                CASE
                    WHEN tipo = 3 THEN -valor
                    ELSE valor
                END AS valor
            FROM financeiro
            WHERE tipo IN (1, 2, 3)
        ) AS ajustado
        WHERE ano IN (" . implode(', ', $placeholders) . ")
        GROUP BY ano, tipo_ajustado, mes
        ORDER BY ano, mes, tipo_ajustado;
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $series = [];
    foreach ($anosSelecionados as $ano) {
        $series[$ano] = [
            'ano' => $ano,
            'entradas' => array_fill(1, 12, 0.0),
            'saidas' => array_fill(1, 12, 0.0),
            'saldoAcumulado' => array_fill(1, 12, 0.0)
        ];
    }

    foreach ($resultados as $row) {
        $ano = (int)$row['ano'];
        $mes = (int)$row['mes'];
        $tipo = (int)$row['tipo'];
        $total = (float)$row['total'];

        if (!isset($series[$ano]) || $mes < 1 || $mes > 12) {
            continue;
        }

        if ($tipo === 1) {
            $series[$ano]['entradas'][$mes] = $total;
        } elseif ($tipo === 2) {
            $series[$ano]['saidas'][$mes] = $total;
        }
    }

    foreach ($series as $ano => $dadosAno) {
        $saldoAnterior = 0.0;
        for ($mes = 1; $mes <= 12; $mes++) {
            $entrada = (float)$dadosAno['entradas'][$mes];
            $saida = (float)$dadosAno['saidas'][$mes];
            $saldoMensal = $entrada - $saida;
            $saldoAnterior = round($saldoAnterior + $saldoMensal, 2);

            $series[$ano]['entradas'][$mes] = round($entrada, 2);
            $series[$ano]['saidas'][$mes] = round($saida, 2);
            $series[$ano]['saldoAcumulado'][$mes] = $saldoAnterior;
        }

        $series[$ano]['entradas'] = array_values($series[$ano]['entradas']);
        $series[$ano]['saidas'] = array_values($series[$ano]['saidas']);
        $series[$ano]['saldoAcumulado'] = array_values($series[$ano]['saldoAcumulado']);
    }

    echo json_encode([
        'yearsAvailable' => $anosDisponiveis,
        'selectedYears' => $anosSelecionados,
        'series' => array_values($series)
    ], JSON_NUMERIC_CHECK);
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do financeiro: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao carregar dados. Por favor, tente novamente mais tarde.']);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao processar a requisição.']);
}
?>