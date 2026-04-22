<?php

require_once 'auth.php';
verificarSessao();
    function registrarAuditoria($usuarioId, $acao, $ipUsuario, $detalhes = null)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "INSERT INTO auditoria (usuario_id, acao, ip_usuario, detalhes) VALUES (:usuario_id, :acao, :ip_usuario, :detalhes)";
        $stmt = $pdo->prepare($query);
    
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':acao' => $acao,
            ':ip_usuario' => $ipUsuario,
            ':detalhes' => json_encode($detalhes)
        ]);
    }

    function buscarDadosEmpresa()
    {
        global $pdo;
    
        try
        {
            $stmt = $pdo->prepare("SELECT * FROM empresa LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e)
        {
            // Retorna dados padrão em caso de erro
            return [
                'nome' => 'Minha Empresa',
                'endereco' => 'Endereço não cadastrado',
                'cnpj' => '00.000.000/0000-00',
                'telefone' => '(00) 0000-0000'
            ];
        }
    }

    // Função para buscar produtos com paginação
    function buscarProdutos($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        // Monta a consulta com WHERE antes do ORDER BY para manter SQL válido
        $query = "SELECT * FROM produtos";

        $params = [
            ':limite' => $limite,
            ':offset' => $offset
        ];

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
            $params[':valor'] = "%$valor%";
        }

        $query .= " ORDER BY id ASC LIMIT :limite OFFSET :offset";
    
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para buscar produtos com paginação
    function buscarProdutosRelatorios()
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT * FROM produtos ORDER BY id ASC";
    
        $stmt = $pdo->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

function buscarConversas()
{
    global $pdo;

    try {
        $query = "SELECT DISTINCT remetente FROM mensagens;";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna os dados
    } catch (PDOException $e) {
        return ['error' => 'Erro ao buscar conversas: ' . $e->getMessage()]; // Retorna erro
    }
}

    // Função para buscar clientes com paginação
    function buscarClientes($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT * FROM clientes ORDER BY id ASC";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $query .= " LIMIT :limite OFFSET :offset";
    
        $stmt = $pdo->prepare($query);

        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%", ':limite' => $limite, ':offset' => $offset]);
        }
        else
        {
            $stmt->execute([':limite' => $limite, ':offset' => $offset]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para buscar contas com paginação
    function buscarContas($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
    
        // Base da consulta SQL
        $query = "SELECT * FROM contas";
    
        // Adiciona a cláusula WHERE se necessário
        if (!empty($filtro) && !empty($valor))
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        // Adiciona a ordenação e os limites de paginação
        $query .= " ORDER BY id ASC LIMIT $limite OFFSET $offset";
    
        // Prepara a consulta
        $stmt = $pdo->prepare($query);
    
        // Verifica se tem filtro e valor para definir os parâmetros
        if (!empty($filtro) && !empty($valor))
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }
        // Retorna os resultados como um array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para buscar contas com paginação
    function buscarTipoPagamento($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
    
        // Base da consulta SQL
        $query = "SELECT * FROM tipopagamento";
    
        // Adiciona a cláusula WHERE se necessário
        if (!empty($filtro) && !empty($valor))
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        // Adiciona a ordenação e os limites de paginação
        $query .= " ORDER BY id ASC LIMIT $limite OFFSET $offset";
    
        // Prepara a consulta
        $stmt = $pdo->prepare($query);
    
        // Verifica se tem filtro e valor para definir os parâmetros
        if (!empty($filtro) && !empty($valor))
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }
        // Retorna os resultados como um array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function buscarTipoPagamentoporID($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
    
        // Base da consulta SQL
        $query = "SELECT * FROM tipopagamento";
    
        // Adiciona a cláusula WHERE se necessário
        if (!empty($filtro) && !empty($valor))
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        // Adiciona a ordenação e os limites de paginação
        $query .= " ORDER BY id ASC LIMIT $limite OFFSET $offset";
    
        // Prepara a consulta
        $stmt = $pdo->prepare($query);
    
        // Verifica se tem filtro e valor para definir os parâmetros
        if (!empty($filtro) && !empty($valor))
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }
        // Retorna os resultados como um array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para buscar contas com paginação
    function buscarTabela($tabela = '', $filtro = '', $valor = '', $limite = 10, $offset = 0, $orderby = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
    
        // Base da consulta SQL
        $query = "SELECT * FROM ". $tabela ."";
    
        // Adiciona a cláusula WHERE se necessário
        if (!empty($filtro) && !empty($valor))
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
        
        if(!isset($orderby))
        {
            $orderby = "ASC";
        }
        else
        {
            $orderby = "DESC";
        }
        // Adiciona a ordenação e os limites de paginação
        $query .= " ORDER BY id $orderby LIMIT $limite OFFSET $offset";
    
        // Prepara a consulta
        $stmt = $pdo->prepare($query);
    
        // Verifica se tem filtro e valor para definir os parâmetros
        if (!empty($filtro) && !empty($valor))
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }
        // Retorna os resultados como um array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function buscarTotalVendasnoPeriodo($dia = null, $mes = null, $ano = null)
    {
        global $pdo;
    
        // Se algum parâmetro estiver vazio, usa a data atual
        if (empty($dia) || empty($mes) || empty($ano))
        {
            $data_inicio = date('Y-m-d');
        }
        else
        {
            // Garante que os valores são numéricos e formata corretamente
            $dia = str_pad(intval($dia), 2, '0', STR_PAD_LEFT);
            $mes = str_pad(intval($mes), 2, '0', STR_PAD_LEFT);
            $data_inicio = "$ano-$mes-$dia";
        }
    
        // Calcula a data final (dia seguinte)
        $data_fim = date('Y-m-d', strtotime($data_inicio . ' +1 day'));
    
        $query = "
            SELECT COALESCE(SUM(vnd.total * (1 - COALESCE(vnd.desconto, 0) / 100.0)), 0) as total_vendas_periodo
            FROM vendas vnd 
            WHERE vnd.data_venda >= :data_inicio 
            AND estornado = 'f'
            AND vnd.data_venda < :data_fim
            ";
    
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    function buscarTotalVendasPorTipoPagamento($dia = null, $mes = null, $ano = null)
    {
        global $pdo;
    
        // Se algum parâmetro estiver vazio, usa a data atual
        if (empty($dia) || empty($mes) || empty($ano))
        {
            $data_inicio = date('Y-m-d');
        }
        else
        {
            // Garante que os valores são numéricos e formata corretamente
            $dia = str_pad(intval($dia), 2, '0', STR_PAD_LEFT);
            $mes = str_pad(intval($mes), 2, '0', STR_PAD_LEFT);
            $data_inicio = "$ano-$mes-$dia";
        }
    
        // Calcula a data final (dia seguinte)
        $data_fim = date('Y-m-d', strtotime($data_inicio . ' +1 day'));
    
        $query = "
            SELECT 
                COALESCE(tp.nome, 'Sem tipo') AS tipo_pagamento, 
                SUM(v.valor) AS total_vendas
            FROM 
                pagamentos_venda v
            LEFT JOIN 
                tipopagamento tp ON v.forma_pagamento_id = tp.id
            LEFT JOIN
                vendas vnd ON v.venda_id = vnd.id
            WHERE 
                v.data_pagamento >= :data_inicio 
            AND
                v.data_pagamento < :data_fim
            AND
                vnd.estornado = 'f'
            GROUP BY 
                tp.nome
            ORDER BY 
                total_vendas DESC
            ";

            //SELECT COALESCE(SUM(vnd.total), 0) as total_vendas_periodo
            //FROM vendas vnd 
            //WHERE vnd.data_venda >= :data_inicio 
            //AND estornado = 'f'
            //AND vnd.data_venda < :data_fim
    
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    function buscarTotalVendasPorTipoPagamentoPeriodo($startData = null, $endData = null)
{
    global $pdo;

    // Validação básica das datas
    if ($startData === null || $endData === null) {
        throw new InvalidArgumentException("As datas de início e fim são obrigatórias");
    }

    // Verifica se as datas são válidas
    if (!strtotime($startData) || !strtotime($endData)) {
        throw new InvalidArgumentException("Datas fornecidas são inválidas");
    }

    // Formata as datas corretamente
    $startDateFormatted = date('Y-m-d 00:00:00', strtotime($startData));
    $endDateExclusive = date('Y-m-d 00:00:00', strtotime($endData . ' +1 day'));

    $query = "
        WITH vendas_filtradas AS (
            SELECT id
            FROM vendas
            WHERE estornado = 'f'
              AND data_venda >= :startData
              AND data_venda < :endDateExclusive
        )
        SELECT 
            COALESCE(tp.nome, 'Sem tipo') AS tipo_pagamento,
            SUM(v.valor) AS total_vendas
        FROM pagamentos_venda v
        JOIN vendas_filtradas vf ON vf.id = v.venda_id
        LEFT JOIN tipopagamento tp ON v.forma_pagamento_id = tp.id
        GROUP BY tp.nome
        ORDER BY total_vendas DESC
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':startData', $startDateFormatted, PDO::PARAM_STR);
        $stmt->bindParam(':endDateExclusive', $endDateExclusive, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar vendas por tipo de pagamento: " . $e->getMessage());
        return [];
    }
}

    function buscarTotalVendasnoPeriodoRelatorio($startData = null, $endData = null)
    {
        global $pdo;

        // Validação básica das datas
        if ($startData === null || $endData === null)
        {
            throw new InvalidArgumentException("As datas de início e fim são obrigatórias");
        }

        // Verifica se as datas são válidas
        if (!strtotime($startData) || !strtotime($endData))
        {
            throw new InvalidArgumentException("Datas fornecidas são inválidas");
        }

        // Formata as datas corretamente
        $startDateFormatted = date('Y-m-d 00:00:00', strtotime($startData));
        $endDateExclusive = date('Y-m-d 00:00:00', strtotime($endData . ' +1 day'));
    
        $query = "
            SELECT COALESCE(SUM(vnd.total * (1 - COALESCE(vnd.desconto, 0) / 100.0)), 0) as total_vendas_periodo
            FROM vendas vnd 
            WHERE vnd.data_venda >= :startData 
            AND estornado = 'f'
            AND vnd.data_venda < :endDateExclusive
            ";
        try
        {
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':startData', $startDateFormatted, PDO::PARAM_STR);
            $stmt->bindParam(':endDateExclusive', $endDateExclusive, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn();
        }
        catch (PDOException $e)
        {
            error_log("Erro ao contar vendas: " . $e->getMessage());
            return 0;
        }
    }

    function buscarTotaisVendasPeriodoRelatorio($startData = null, $endData = null)
    {
        global $pdo;
        $startDateFormatted = date('Y-m-d 00:00:00', strtotime($startData));
        $endDateExclusive = date('Y-m-d 00:00:00', strtotime($endData . ' +1 day'));

        $query = "
            SELECT 
                COALESCE(SUM(total), 0) as total_bruto,
                COALESCE(SUM(total * (1 - COALESCE(desconto, 0) / 100.0)), 0) as total_liquido
            FROM vendas 
            WHERE data_venda >= :startData 
            AND estornado = 'f'
            AND data_venda < :endDateExclusive
        ";
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':startData', $startDateFormatted, PDO::PARAM_STR);
            $stmt->bindParam(':endDateExclusive', $endDateExclusive, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar totais de vendas: " . $e->getMessage());
            return ['total_bruto' => 0, 'total_liquido' => 0];
        }
    }

    function buscarDadosFluxoCaixa($startData, $endData)
    {
        global $pdo;
        
        $query = "
            SELECT 
                d.data,
                COALESCE(v.total_vendas, 0) as vendas,
                COALESCE(f.entradas, 0) as outras_entradas,
                COALESCE(f.saidas, 0) as despesas,
                (COALESCE(v.total_vendas, 0) + COALESCE(f.entradas, 0)) as total_entradas,
                COALESCE(f.saidas, 0) as total_saidas,
                ((COALESCE(v.total_vendas, 0) + COALESCE(f.entradas, 0)) - COALESCE(f.saidas, 0)) as saldo_dia
            FROM (
                SELECT generate_series(:start::date, :end::date, '1 day'::interval)::date as data
            ) d
            LEFT JOIN (
                SELECT data_venda::date as data, SUM(total * (1 - COALESCE(desconto, 0) / 100.0)) as total_vendas
                FROM vendas WHERE estornado = 'f' AND data_venda >= :start AND data_venda < :end_exclusive
                GROUP BY 1
            ) v ON v.data = d.data
            LEFT JOIN (
                SELECT data_lancamento::date as data, 
                       SUM(CASE WHEN tipo = 1 THEN valor ELSE 0 END) as entradas,
                       SUM(CASE WHEN tipo = 2 THEN valor ELSE 0 END) as saidas
                FROM financeiro WHERE data_lancamento >= :start AND data_lancamento < :end_exclusive
                GROUP BY 1
            ) f ON f.data = d.data
            ORDER BY d.data ASC
        ";

        try {
            $endExclusive = date('Y-m-d', strtotime($endData . ' +1 day'));
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':start', $startData);
            $stmt->bindValue(':end', $endData);
            $stmt->bindValue(':end_exclusive', $endExclusive);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados de fluxo de caixa: " . $e->getMessage());
            return [];
        }
    }

    

    function buscarTotalemEstoqueRelatorio()
{
    global $pdo;
    
    $query = "
        SELECT COALESCE(SUM(quantidade), 0) AS total_estoque 
        FROM produtos
    ";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total_estoque'];
    } catch (PDOException $e) {
        error_log("Erro ao contar produtos: " . $e->getMessage());
        return 0; // Retorno consistente em caso de erro
    }
}

function buscarMediaVendaProdutosRelatorio()
{
    global $pdo;
    
    $query = "
        SELECT COALESCE(
            SUM(preco_venda * quantidade) / NULLIF(SUM(quantidade), 0), 
            0
        ) AS media_preco_venda
        FROM produtos
    ";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float)$result['media_preco_venda'];
    } catch (PDOException $e) {
        error_log("Erro ao calcular média de preço de venda: " . $e->getMessage());
        return 0.0; // Retorno consistente em caso de erro
    }
}

function calcularValorTotalEstoqueRelatorio()
{
    global $pdo;
    
    $query = "
        SELECT 
            COALESCE(SUM(preco_venda * quantidade), 0) AS total_venda,
            COALESCE(SUM(preco_custo * quantidade), 0) AS total_custo
        FROM produtos
    ";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_venda' => (float)$result['total_venda'],
            'total_custo' => (float)$result['total_custo']
        ];
        
    } catch (PDOException $e) {
        error_log("Erro ao calcular valor total do estoque: " . $e->getMessage());
        return [
            'total_venda' => 0.0,
            'total_custo' => 0.0
        ];
    }
}

    function buscarTotalVendasnoMes($mes = null, $ano = null)
{
    global $pdo;

    // Se mês ou ano estiverem vazios, usa o mês e ano atuais
    if (empty($mes) || empty($ano)) {
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-01', strtotime('+1 month'));
    } else {
        // Formata mês com 2 dígitos
        $mes = str_pad(intval($mes), 2, '0', STR_PAD_LEFT);

        // Data de início: primeiro dia do mês selecionado
        $data_inicio = "$ano-$mes-01";

        // Calcula o primeiro dia do próximo mês baseado na data selecionada
        $data_fim = date('Y-m-01', strtotime("$data_inicio +1 month"));
    }

    $query = "
        SELECT COALESCE(SUM(vnd.total * (1 - COALESCE(vnd.desconto, 0) / 100.0)), 0) as total_vendas_periodo
        FROM vendas vnd 
        WHERE vnd.data_venda >= :data_inicio 
        AND vnd.data_venda < :data_fim
        AND estornado = 'f'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();

    return $stmt->fetchColumn();
}


    function contarNumeroPorVendas($dia = null, $mes = null, $ano = null)
    {
        global $pdo;

        // Garante que os valores são numéricos e formata corretamente
        $dia = str_pad(intval($dia), 2, '0', STR_PAD_LEFT);
        $mes = str_pad(intval($mes), 2, '0', STR_PAD_LEFT);
        $data_inicio = "$ano-$mes-$dia";
        
    
        // Calcula a data final (dia seguinte)
        $data_fim = date('Y-m-d', strtotime($data_inicio . ' +1 day'));


        $query = "
            SELECT COUNT(*) FROM vendas
            WHERE data_venda >= :data_inicio 
            AND estornado = 'f'
            AND data_venda < :data_fim
        ";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    function contarNumeroVendasPeriodo($startData = null, $endData = null)
{
    global $pdo;

    // Validação básica das datas
    if ($startData === null || $endData === null) {
        throw new InvalidArgumentException("As datas de início e fim são obrigatórias");
    }

    // Verifica se as datas são válidas
    if (!strtotime($startData) || !strtotime($endData)) {
        throw new InvalidArgumentException("Datas fornecidas são inválidas");
    }

    // Formata as datas corretamente
    $startDateFormatted = date('Y-m-d 00:00:00', strtotime($startData));
    $endDateFormatted = date('Y-m-d 23:59:59', strtotime($endData));

    $query = "
        SELECT COUNT(*) as total_vendas 
        FROM vendas
        WHERE estornado = 'f'
        AND data_venda BETWEEN :startData AND :endData
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':startData', $startDateFormatted, PDO::PARAM_STR);
        $stmt->bindParam(':endData', $endDateFormatted, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log do erro
        error_log("Erro ao contar vendas: " . $e->getMessage());
        return 0; // Ou lançar a exceção novamente dependendo da sua estratégia
    }
}

    // Função para buscar contas com paginação
    function buscarTabelaVendas($tabela = '', $filtro = '', $valor = '', $limite = 10, $offset = 0, $orderby = 'DESC', $dia = '', $mes = '', $ano = '')
    {
    global $pdo;

   $query = "
    SELECT
        vnd.*,
        u.username AS usuariovendedor,
        STRING_AGG(tppgmt.nome, ', ') AS tipos_pagamento
    FROM
        vendas vnd
    JOIN usuarios u ON vnd.vendedor = u.ID
    JOIN pagamentos_venda pgmtvnd ON vnd.ID = pgmtvnd.venda_id
    JOIN tipopagamento tppgmt ON pgmtvnd.forma_pagamento_id = tppgmt.ID
";

// Filtros de data
$where = [];
$params = [];

if (!empty($ano)) {
    if (!empty($mes)) {
        if (!empty($dia)) {
            // Filtro por dia específico
            $data_inicio = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
            $data_fim = date('Y-m-d', strtotime($data_inicio . ' +1 day'));
            $where[] = "vnd.data_venda >= :data_inicio AND vnd.data_venda < :data_fim";
            $params[':data_inicio'] = $data_inicio;
            $params[':data_fim'] = $data_fim;
        } else {
            // Filtro por mês
            $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
            $where[] = "EXTRACT(YEAR FROM vnd.data_venda) = :ano AND EXTRACT(MONTH FROM vnd.data_venda) = :mes";
            $params[':ano'] = $ano;
            $params[':mes'] = $mes;
        }
    } else {
        // Filtro apenas por ano
        $where[] = "EXTRACT(YEAR FROM vnd.data_venda) = :ano";
        $params[':ano'] = $ano;
    }
}

// Adiciona WHERE se houver filtros
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Aqui vem o GROUP BY — após o WHERE
$query .= " GROUP BY vnd.ID, u.username";

// Ordenação
$query .= " ORDER BY vnd.id " . ($orderby === 'ASC' ? 'ASC' : 'DESC');

// Paginação
if ($limite > 0) {
    $query .= " LIMIT :limite OFFSET :offset";
}

    // Prepara a consulta
    $stmt = $pdo->prepare($query);

    // Bind dos parâmetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    // Bind dos parâmetros de paginação
    if ($limite > 0) {
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarTabelaVendasRelatorio($tabela = '', $filtro = '', $valor = '', $limite = 10, $offset = 0, $orderby = 'DESC', $startData = null, $endData = null)
{
    global $pdo;

    $query = "
        WITH vendas_filtradas AS (
            SELECT
                vnd.id,
                vnd.total,
                vnd.desconto,
                vnd.data_venda,
                vnd.estornado,
                vnd.vendedor
            FROM vendas vnd
            WHERE vnd.estornado = 'f'
    ";

    $where = [];
    $params = [];

    if ($startData !== null && $endData !== null) {
        $query .= " AND vnd.data_venda >= :startData AND vnd.data_venda < :endDateExclusive";
        $params[':startData'] = $startData . ' 00:00:00';
        $params[':endDateExclusive'] = date('Y-m-d 00:00:00', strtotime($endData . ' +1 day'));
    }

    $query .= "
        )
        SELECT
            vf.id,
            vf.total,
            vf.desconto,
            (vf.total * (1 - COALESCE(vf.desconto, 0) / 100.0)) AS total_liquido,
            vf.data_venda,
            vf.estornado,
            u.username AS usuariovendedor,
            COALESCE(pg.tipos_pagamento, 'Sem tipo') AS tipos_pagamento
        FROM vendas_filtradas vf
        JOIN usuarios u ON vf.vendedor = u.ID
        LEFT JOIN (
            SELECT
                pgmtvnd.venda_id,
                STRING_AGG(DISTINCT tppgmt.nome, ', ' ORDER BY tppgmt.nome) AS tipos_pagamento
            FROM pagamentos_venda pgmtvnd
            JOIN tipopagamento tppgmt ON pgmtvnd.forma_pagamento_id = tppgmt.ID
            JOIN vendas_filtradas vf2 ON vf2.id = pgmtvnd.venda_id
            GROUP BY pgmtvnd.venda_id
        ) pg ON pg.venda_id = vf.id
    ";

    // Filtros adicionais (se necessário)
    if (!empty($filtro) && !empty($valor)) {
        $where[] = "vf.$filtro = :valor";
        $params[':valor'] = $valor;
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    // Ordenação por data (mais útil para relatórios)
    $query .= " ORDER BY vf.data_venda " . ($orderby === 'ASC' ? 'ASC' : 'DESC');

    // Paginação
    if ($limite > 0) {
        $query .= " LIMIT :limite OFFSET :offset";
        $params[':limite'] = $limite;
        $params[':offset'] = $offset;
    }

    $stmt = $pdo->prepare($query);
    
    // Bind dos parâmetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarTotaisPorTipoPagamento($dia = null, $mes = null, $ano = null) {
    global $pdo;
    $sql = "SELECT tipos_pagamento, SUM(total) as total 
            FROM vendas 
            WHERE 1=1";

    $params = [];

    if ($dia) {
        $sql .= " AND EXTRACT(DAY FROM data_venda) = :dia";
        $params[':dia'] = $dia;
    }
    if ($mes) {
        $sql .= " AND EXTRACT(MONTH FROM data_venda) = :mes";
        $params[':mes'] = $mes;
    }
    if ($ano) {
        $sql .= " AND EXTRACT(YEAR FROM data_venda) = :ano";
        $params[':ano'] = $ano;
    }

    $sql .= " GROUP BY tipos_pagamento";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    function buscarDadosUsuario($username)
    {
        global $pdo;

        $sql = "
            SELECT u.username, u.password, c.nome, c.cpf
            FROM usuarios u
            JOIN colaboradores c ON u.id = c.idusuario
            WHERE u.username = :username
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
}


    function buscarVendasporId($valor = '')
    {
        global $pdo;
    
        $sql = "SELECT * FROM vendas WHERE id = :id ORDER BY id ASC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $valor, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function buscarVendasComLimite()
    {
        global $pdo;
    
        $sql = "SELECT * FROM vendas ORDER BY id DESC LIMIT 7";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    // Função para buscar contas com paginação
    function buscarContasFinanceiro($valor = '')
    {
        global $pdo;
    
        $sql = "SELECT * FROM contas WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $valor, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function buscarFinanceiroEntradasnodia($dia = null)
    {
        global $pdo;
        
        if ($dia === null) {
            $dia = date('Y-m-d');
        }
    
        $sql = "SELECT SUM(valor) AS total
                FROM financeiro 
                WHERE tipo = 1 
                AND data_lancamento::date = :dia";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dia', $dia, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function buscarFinanceiroEstornosnodia($dia = null)
    {
        global $pdo;
        
        if ($dia === null) {
            $dia = date('Y-m-d');
        }
    
        $sql = "SELECT SUM(valor) AS total
                FROM financeiro 
                WHERE tipo = 3
                AND data_lancamento::date = :dia";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dia', $dia, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function buscarFinanceiroSaidasnodia($dia = null)
    {
        global $pdo;
        
        if ($dia === null) {
            $dia = date('Y-m-d');
        }
    
        $sql = "SELECT SUM(valor) AS total
                FROM financeiro 
                WHERE tipo = 2 
                AND data_lancamento::date = :dia";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dia', $dia, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para buscar contas com paginação
    function buscarTodasContasFinanceiro()
    {
        global $pdo;
    
        $sql = "SELECT * FROM contas ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


function buscarFinanceiro($filtro = '', $valor = '', $limite = 10, $offset = 0, $tipo_lancamento = '') {
    global $pdo;
    
    $query = "
    SELECT
        fin.*,
        cont.nome AS nome_conta
    FROM
        financeiro fin
    LEFT JOIN 
        contas cont 
    ON 
        fin.conta = cont.id";
    
    $whereClauses = [];
    $params = [];
    
    // Filtro por tipo de lançamento
    if (!empty($tipo_lancamento)) {
        $whereClauses[] = "fin.tipo = :tipo";
        $params[':tipo'] = $tipo_lancamento;
    }
    
    // Filtro adicional
    if ($filtro && $valor) {
        if ($filtro === 'valor') {
            $whereClauses[] = "CAST(fin.valor AS TEXT) LIKE :valor";
            $params[':valor'] = "%$valor%";
        } else {
            // Adiciona prefixo 'fin.' para evitar ambiguidade
            $whereClauses[] = "fin.$filtro ILIKE :valor";
            $params[':valor'] = "%$valor%";
        }
    }
    
    // Construir a cláusula WHERE se houver filtros
    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    // Ordenação e paginação
    $query .= " ORDER BY fin.id DESC LIMIT :limite OFFSET :offset";
    $params[':limite'] = $limite;
    $params[':offset'] = $offset;
    
    try {
        $stmt = $pdo->prepare($query);
        
        // Bind dos parâmetros
        foreach ($params as $key => $val) {
            if ($key === ':limite' || $key === ':offset') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        // Log do erro ou tratamento adequado
        error_log("Erro ao buscar financeiro: " . $e->getMessage());
        return false;
    }
}

function buscarTransferencias($limite = 10, $offset = 0)
{
    global $pdo;
    
    $query = "
    SELECT 
        trans.id,
        trans.valor,
        trans.data_lancamento,
        trans.id_conta_origem,
        cont_origem.nome AS nome_conta_origem,
        trans.id_conta_destino,
        cont_destino.nome AS nome_conta_destino
    FROM 
        transferencias trans
    INNER JOIN 
        contas cont_origem ON trans.id_conta_origem = cont_origem.id
    INNER JOIN 
        contas cont_destino ON trans.id_conta_destino = cont_destino.id
        ";
    
    // Ordenação e paginação
    $query .= " ORDER BY trans.id DESC LIMIT :limite OFFSET :offset";
    $params[':limite'] = $limite;
    $params[':offset'] = $offset;
    
    try {
        $stmt = $pdo->prepare($query);
        
        // Bind dos parâmetros
        foreach ($params as $key => $val) {
            if ($key === ':limite' || $key === ':offset') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        // Log do erro ou tratamento adequado
        error_log("Erro ao buscar transferencias: " . $e->getMessage());
        return false;
    }
}

function buscarFechamentos($limite = 10, $offset = 0)
{
    global $pdo;
    
    $query = "
    SELECT 
        fech.id,
        fech.dia_fechamento,
        fech.saldo as saldo_total,
        fech.entrada,
        fech.saida,
        fech.usuario,
        fech.created_at
    FROM 
        fechamentos fech
    ORDER BY 
        fech.dia_fechamento DESC
    LIMIT :limite OFFSET :offset
    ";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar fechamentos: " . $e->getMessage());
        return false;
    }
}


    // Função para buscar Despesas Fixas com paginação
    function buscarDespesasFixas($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT * FROM despesasfixas ORDER BY id ASC";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $query .= " LIMIT :limite OFFSET :offset";
    
        $stmt = $pdo->prepare($query);

        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%", ':limite' => $limite, ':offset' => $offset]);
        }
        else
        {
            $stmt->execute([':limite' => $limite, ':offset' => $offset]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function buscaNomeConta($valor)
    {
        if ($valor == 2) {
            echo "oi";
        }
        echo "oi";
    }

    // Função para buscar contas com paginação
    function buscarFinanceiroaa($filtro = '', $valor = '', $limite = 10, $offset = 0)
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT * FROM financeiro ORDER BY id ASC";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $query .= " LIMIT :limite OFFSET :offset";
    
        $stmt = $pdo->prepare($query);

        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%", ':limite' => $limite, ':offset' => $offset]);
        }
        else
        {
            $stmt->execute([':limite' => $limite, ':offset' => $offset]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para contar o total de produtos
    function contarProdutos($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT COUNT(*) FROM produtos";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    // Função para contar o total de clientes
    function contarClientes($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT COUNT(*) FROM clientes";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    // Função para contar o total de contas
    function contarContas($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT COUNT(*) FROM contas";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    function contarTipoPagamento($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT COUNT(*) FROM tipopagamento";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

function contarFinanceiro($filtro = '', $valor = '', $tipo_lancamento = '')
{
    global $pdo;
    $query = "SELECT COUNT(*) FROM financeiro";
    $whereClauses = [];
    $params = [];
    
    // Filtro por tipo de lançamento
    if (!empty($tipo_lancamento)) {
        $whereClauses[] = "tipo = :tipo";
        $params[':tipo'] = $tipo_lancamento;
    }
    
    // Filtro adicional
    if ($filtro && $valor) {
        if ($filtro === 'valor') {
            $whereClauses[] = "CAST(valor AS TEXT) LIKE :valor";
            $params[':valor'] = "%$valor%";
        } else {
            $whereClauses[] = "$filtro ILIKE :valor";
            $params[':valor'] = "%$valor%";
        }
    }
    
    // Construir a cláusula WHERE se houver filtros
    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    $stmt = $pdo->prepare($query);
    
    // Bind dos parâmetros
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    
    $stmt->execute();
    return $stmt->fetchColumn();
}

function contarTransferencias()
{
    global $pdo;
    $query = "SELECT COUNT(*) FROM transferencias";
    
    $stmt = $pdo->prepare($query);
    
    $stmt->execute();
    return $stmt->fetchColumn();
}

function contarFechamentos()
{
    global $pdo;
    $query = "SELECT COUNT(*) FROM fechamentos";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro ao contar fechamentos: " . $e->getMessage());
        return 0;
    }
}

// Adicione esta função no arquivo funcoes.php
function existeFechamentoDoDia($data = null)
{
    global $pdo;
    
    if ($data === null) {
        $data = date('Y-m-d');
    }
    
    $query = "SELECT COUNT(*) FROM fechamentos WHERE dia_fechamento = :data";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':data' => $data]);
    
    return $stmt->fetchColumn() > 0;
}

// Função para buscar fechamento do dia (se precisar dos dados)
function buscarFechamentoDoDia($data = null)
{
    global $pdo;
    
    if ($data === null) {
        $data = date('Y-m-d');
    }
    
    $query = "SELECT * FROM fechamentos WHERE dia_fechamento = :data";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':data' => $data]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    // Função para contar o total de despesas fixas
    function contarDespesasFixas($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT COUNT(*) FROM despesasfixas";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    // Função para contar o total de despesas fixas
    function contarNumeroPorTabela($tabela = '', $filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT COUNT(*) FROM ". $tabela . "";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    // Função para contar o total de despesas fixas
    function BuscarSomaPorTabela($tabela = '', $coluna = '', $filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT SUM(".$coluna.") FROM ".$tabela."";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    function BuscarporTabela($tabela = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        // Ordena pelo id para garantir listagens consistentes (ex.: PDV)
        $query = "SELECT * FROM " . $tabela . " ORDER BY id ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function BuscarLucroMedioProdutos($tabela = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT SUM((preco_venda - preco_custo) * quantidade) / SUM(quantidade) AS lucro_medio FROM ".$tabela."";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    function BuscarCustoMedioProdutos($tabela = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT SUM(preco_custo * quantidade) / SUM(quantidade) AS custo_medio FROM ".$tabela."";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Função para contar o total de despesas fixas
    function BuscarSomaSalarioColaboradores($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT SUM(salario) FROM colaboradores";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    function BuscarSomaSaldoContas($filtro = '', $valor = '')
    {
        global $pdo; // Usa o PDO configurado no `config.php`
        $query = "SELECT SUM(saldo) FROM contas";

        if ($filtro && $valor)
        {
            $query .= " WHERE " . $filtro . " ILIKE :valor";
        }
    
        $stmt = $pdo->prepare($query);
    
        if ($filtro && $valor)
        {
            $stmt->execute([':valor' => "%$valor%"]);
        }
        else
        {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    function formatarCNPJ($cnpj) {
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj);
}

function formatarTelefone($telefone) {
    if (strlen($telefone) === 10) {
        return preg_replace("/(\d{2})(\d{4})(\d{4})/", "(\$1) \$2-\$3", $telefone);
    } elseif (strlen($telefone) === 11) {
        return preg_replace("/(\d{2})(\d{5})(\d{4})/", "(\$1) \$2-\$3", $telefone);
    }
    return $telefone;
}

function contarNumeroPorVendasCustom($data_inicio, $data_fim) {
    global $pdo;
    
    $query = "SELECT COUNT(*) as total FROM vendas 
              WHERE data_venda BETWEEN :data_inicio AND :data_fim";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'] ?? 0;
}

function buscarTotalVendasnoPeriodoCustom($data_inicio, $data_fim) {
    global $pdo;
    
    $query = "SELECT SUM(total) as total FROM vendas 
              WHERE data_venda BETWEEN :data_inicio AND :data_fim";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'] ?? 0;
}

function buscarTabelaVendasCustom($tabela, $data_inicio, $data_fim, $order = 'DESC') {
    global $pdo;
    
    $query = "SELECT * FROM $tabela 
              WHERE data_venda BETWEEN :data_inicio AND :data_fim
              ORDER BY data_venda $order";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
