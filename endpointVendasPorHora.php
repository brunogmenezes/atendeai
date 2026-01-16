<?php

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

try {
    require 'config.php';
    
    if (!$pdo) {
        throw new Exception("Erro: Conexão com o banco de dados não estabelecida");
    }

    // Obtém o primeiro dia (domingo) e último dia (sábado) da semana atual
    $domingo = date('Y-m-d', strtotime('last sunday'));
    $sabado = date('Y-m-d', strtotime('next saturday'));

    // Ajusta o sábado para incluir todo o dia (até 23:59:59)
    $sabado_fim = $sabado . ' 23:59:59';

    // Consulta vendas por hora e dia da semana (apenas entre 8h e 20h da semana atual)
    $query = "
        SELECT 
            EXTRACT(DOW FROM data_venda) AS dia_semana,
            EXTRACT(HOUR FROM data_venda) AS hora,
            COUNT(*) AS quantidade_vendas
        FROM 
            vendas
        WHERE 
            data_venda >= :domingo
        AND
            data_venda <= :sabado_fim
        AND
            estornado = 'f'
        AND
            EXTRACT(HOUR FROM data_venda) BETWEEN 8 AND 20
        GROUP BY 
            EXTRACT(DOW FROM data_venda), EXTRACT(HOUR FROM data_venda)
        ORDER BY 
            dia_semana, hora;";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':domingo', $domingo);
    $stmt->bindParam(':sabado_fim', $sabado_fim);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    $horas = range(8, 20); // Apenas horas de 8 às 20
    
    $data = [];
    $maxValue = 0;
    
    // Preenche com zeros
    foreach ($diasSemana as $diaIndex => $diaNome) {
        foreach ($horas as $hora) {
            $data[] = [
                'x' => $diaNome,
                'y' => $hora,
                'v' => 0
            ];
        }
    }
    
    // Preenche com os valores reais
    foreach ($resultados as $row) {
        $diaIndex = (int)$row['dia_semana'];
        $hora = (int)$row['hora'];
        $quantidade = (int)$row['quantidade_vendas'];
        
        $dataIndex = $diaIndex * count($horas) + ($hora - 8);
        
        if (isset($data[$dataIndex])) {
            $data[$dataIndex]['v'] = $quantidade;
            
            if ($quantidade > $maxValue) {
                $maxValue = $quantidade;
            }
        }
    }

    $response = [
        'success' => true,
        'data' => $data,
        'diasSemana' => $diasSemana,
        'horas' => $horas,
        'maxValue' => $maxValue,
        'semana_atual' => "Semana de " . date('d/m/Y', strtotime($domingo)) . " a " . date('d/m/Y', strtotime($sabado))
    ];

} catch (PDOException $e) {
    $response['message'] = "Erro no banco de dados: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

if (ob_get_length()) ob_clean();

echo json_encode($response);
exit;
?>