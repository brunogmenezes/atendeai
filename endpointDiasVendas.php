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
    
    try
    {
        require 'config.php';
        
        // Verifica se a conex�o est� ativa
        if (!$pdo)
        {
            throw new Exception("Erro: Conex�o com o banco de dados n�o estabelecida");
        }
    
        // Consulta os produtos mais vendidos
        $query = "
            SELECT 
                DATE(data_venda) AS dia,
                COUNT(*) AS quantidade_vendas,
                SUM(total * (1 - COALESCE(desconto, 0) / 100.0)) AS valor_total_vendas
            FROM 
                vendas
            WHERE 
                data_venda >= CURRENT_DATE - INTERVAL '7 days'
            AND
                data_venda < CURRENT_DATE + INTERVAL '1 day'
            AND
                estornado = 'f'
            GROUP BY 
                DATE(data_venda)
            ORDER BY 
            dia ASC;";
    
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (empty($resultados))
        {
            throw new Exception("Nenhum dado encontrado na consulta");
        }
    
        // Prepara os dados para o gr�fico
        $labels = [];
        $data = [];
        $backgroundColors = [
            'rgba(255, 99, 132, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(255, 205, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(201, 203, 207, 0.2)'
        ];
    
        foreach ($resultados as $row)
        {
            $labels[] = $row['dia'];
            $data[] = (float)$row['valor_total_vendas'];
        }
    
        // Monta a resposta de sucesso
        $response = [
            'success' => true,
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => array_slice($backgroundColors, 0, count($labels))
        ];
    
    }
    catch (PDOException $e)
    {
        $response['message'] = "Erro no banco de dados: " . $e->getMessage();
    }
    catch (Exception $e)
    {
        $response['message'] = $e->getMessage();
    }
    
    // Garante que qualquer sa�da anterior seja limpa
    if (ob_get_length()) ob_clean();
    
    // Retorna a resposta como JSON
    echo json_encode($response);
    exit;
?>