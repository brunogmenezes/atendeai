<?php
    require_once 'config.php';
    require_once 'funcoes.php';

    require_once 'auth.php';
verificarSessao();
    
    global $pdo;
function fetchDashboardData($pdo)
{
    // Obter datas corretamente dentro da função
    $anoHoje = date('Y');
    $mesHoje = date('m');
    $mesSeguinte = ($mesHoje == 12) ? 1 : $mesHoje + 1; // Tratamento para dezembro
    $anoSeguinte = ($mesHoje == 12) ? $anoHoje + 1 : $anoHoje; // Ajuste do ano se for dezembro
    
    // Formatar com zero à esquerda para meses < 10
    $mesSeguinteFormatado = str_pad($mesSeguinte, 2, '0', STR_PAD_LEFT);
       $query = "
    SELECT 
        (SELECT COUNT(*) 
         FROM vendas 
         WHERE estornado = 'f' 
           AND data_venda >= :inicio_mes 
           AND data_venda < :inicio_mes_seguinte) AS total_vendas,

        (SELECT SUM(total * (1 - COALESCE(desconto, 0) / 100.0)) 
         FROM vendas 
         WHERE estornado = 'f' 
           AND data_venda >= :inicio_mes 
           AND data_venda < :inicio_mes_seguinte) AS total_valor_vendas,

        (SELECT SUM(itmvnd.quantidade) 
         FROM vendas vnd 
         LEFT JOIN itens_venda itmvnd ON vnd.id = itmvnd.venda_id 
         WHERE vnd.estornado = 'f' 
           AND vnd.data_venda >= :inicio_mes 
           AND vnd.data_venda < :inicio_mes_seguinte) AS total_itens_vendidos,

        (SELECT SUM(itmvnd.quantidade * COALESCE(prd.preco_custo, 0))
         FROM vendas vnd
         JOIN itens_venda itmvnd ON vnd.id = itmvnd.venda_id
         JOIN produtos prd ON itmvnd.produto_id = prd.id
         WHERE vnd.estornado = 'f'
           AND vnd.data_venda >= :inicio_mes
           AND vnd.data_venda < :inicio_mes_seguinte) AS total_cmv_mes,

        (SELECT COUNT(*) FROM clientes) AS total_clientes,

        (SELECT SUM(quantidade) FROM produtos) AS total_produtos,

        (SELECT SUM(preco_custo * quantidade) FROM produtos) AS capital_imobilizado_estoque,

        (SELECT SUM(preco_venda * quantidade) / NULLIF(SUM(quantidade), 0) 
         FROM produtos) AS media_preco_venda,

        (SELECT COUNT(*) 
         FROM produtos 
         WHERE quantidade <= quantidade_critico) AS total_critico,

        (SELECT SUM(valor) FROM despesasfixas) AS total_despesasfixas,

        (SELECT SUM(saldo) FROM contas) AS total_saldo_contas;
    ";

    $stmt = $pdo->prepare($query);
    
    // Usando parâmetros nomeados para segurança
    $stmt->execute([
        ':inicio_mes' => "$anoHoje-$mesHoje-01",
        ':inicio_mes_seguinte' => "$anoSeguinte-$mesSeguinteFormatado-01"
    ]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    
    try
    {
        $results = fetchDashboardData($pdo);
        
        $totalVendas = (int)($results['total_vendas'] ?? 0);
        $totalValorVendas = (float)($results['total_valor_vendas'] ?? 0);
        $totalItensVendidos = (int)($results['total_itens_vendidos'] ?? 0);
        $totalCmvMes = (float)($results['total_cmv_mes'] ?? 0);
        $totalClientes = (int)($results['total_clientes'] ?? 0);
        $totalProdutos = (int)($results['total_produtos'] ?? 0);
        $capitalImobilizado = (float)($results['capital_imobilizado_estoque'] ?? 0);
        $totalMediaPrecoVenda = (float)($results['media_preco_venda'] ?? 0);
        $totalCritico = (int)($results['total_critico'] ?? 0);
        $totalDespesasFixas = (float)($results['total_despesasfixas'] ?? 0);
        $totalSaldoContas = (float)($results['total_saldo_contas'] ?? 0);
    }
    catch (PDOException $e)
    {
        error_log("Erro ao buscar dados do dashboard: " . $e->getMessage());
        // Exibir uma mensagem genérica para o usuário
        echo "Erro ao carregar dados. Por favor, tente novamente mais tarde.";
    }

    $somaSalarioColaboradores = (float)BuscarSomaPorTabela('colaboradores', 'salario');
    $somaDespesasFixas = (float)BuscarSomaPorTabela('despesasfixas', 'valor');
    $totalCustoMensal = $somaSalarioColaboradores + $somaDespesasFixas;

    $custoMedioProduto = (float)(BuscarCustoMedioProdutos('produtos') ?? 0);
    $lucroMedio = (float)(BuscarLucroMedioProdutos('produtos', 'salario') ?? 0);

    // Verifica se as variáveis estão definidas e se $lucroMedio não é zero
    if ($lucroMedio > 0)
    {
        $pecasAVender = (int)ceil($totalCustoMensal / $lucroMedio);
    }
    else
    {
        $pecasAVender = 0; // Valor padrão caso as condições não sejam atendidas
    }

    $MetaMensalDesejada = $pecasAVender * $totalMediaPrecoVenda;

    // 1. Métricas Comerciais
    $ticketMedio = ($totalVendas > 0) ? ($totalValorVendas / $totalVendas) : 0;
    $itensPorVenda = ($totalVendas > 0) ? ($totalItensVendidos / $totalVendas) : 0;

    // 2. Rentabilidade Real do Mês
    $lucroBrutoMes = $totalValorVendas - $totalCmvMes;
    $margemBrutaPercentual = ($totalValorVendas > 0) ? (($lucroBrutoMes / $totalValorVendas) * 100) : 0;
    $lucroLiquidoMes = $lucroBrutoMes - $totalCustoMensal;

    // 3. Projeção & Giro
    $diaHoje = (int)date('j');
    $diasNoMes = (int)date('t');
    $projecaoFaturamento = ($diaHoje > 0) ? (($totalValorVendas / $diaHoje) * $diasNoMes) : 0;
    $mediaItensPorDia = ($diaHoje > 0) ? ($totalItensVendidos / $diaHoje) : 0;
    $diasCoberturaEstoque = ($mediaItensPorDia > 0) ? (int)round($totalProdutos / $mediaItensPorDia) : ($totalProdutos > 0 ? 999 : 0);
?>
<?php
if($user['isAdmin']==true)
{
?>
    <div class="page-inner">
        <!-- Seção KPIs Principais -->
        <div class="dashboard-header mb-4">
            <h2 class="page-title">Dashboard Executivo & Inteligência de Negócio</h2>
            <p class="text-muted">Acompanhe vendas, rentabilidade real, metas e gestão patrimonial</p>
        </div>

        <!-- Row 1: Eficiência Comercial e Vendas -->
        <div class="row mb-3">
            <!-- Total de Vendas -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round gradient-blue">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category">Total de Vendas</p>
                                    <h4 class="card-title"><?=$totalVendas;?></h4>
                                    <span class="card-subtitle"><i class="fas fa-calculator me-1"></i>Pedidos no Mês</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faturamento -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round gradient-green">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category">Faturamento</p>
                                    <h4 class="card-title">R$ <?=number_format($totalValorVendas, 2, ',', '.');?></h4>
                                    <span class="card-subtitle"><i class="fas fa-calculator me-1"></i>Total Líquido Vendido</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Médio -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round gradient-orange">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-warning bubble-shadow-small">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category">Ticket Médio</p>
                                    <h4 class="card-title">R$ <?=number_format($ticketMedio, 2, ',', '.');?></h4>
                                    <span class="card-subtitle" title="Faturamento Total ÷ Total de Vendas"><i class="fas fa-calculator me-1"></i>Faturamento ÷ Vendas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itens por Venda (PA) -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round gradient-purple">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-info bubble-shadow-small">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category">Peças / Venda (PA)</p>
                                    <h4 class="card-title"><?=number_format($itensPorVenda, 1, ',', '.');?> <small style="font-size:0.8rem;">un</small></h4>
                                    <span class="card-subtitle" title="Total Itens Vendidos ÷ Total de Vendas"><i class="fas fa-calculator me-1"></i>Itens ÷ Vendas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Rentabilidade Real do Mês -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-truck-loading fa-lg text-warning"></i>
                        </div>
                        <p class="card-category">Custo das Vendas (CMV)</p>
                        <h4 class="card-title">R$ <?=number_format($totalCmvMes, 2, ',', '.');?></h4>
                        <p class="text-muted small mb-0" title="Soma(Custo × Quantidade Vendida no Mês)"><i class="fas fa-calculator me-1"></i>Σ(Custo × Qtd Vendida)</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-coins fa-lg text-primary"></i>
                        </div>
                        <p class="card-category">Lucro Bruto Realizado</p>
                        <h4 class="card-title">R$ <?=number_format($lucroBrutoMes, 2, ',', '.');?></h4>
                        <p class="text-muted small mb-0" title="Faturamento Total - Custo das Mercadorias Vendidas"><i class="fas fa-calculator me-1"></i>Faturamento - CMV</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-percentage fa-lg text-info"></i>
                        </div>
                        <p class="card-category">Margem Bruta Real</p>
                        <h4 class="card-title"><?=number_format($margemBrutaPercentual, 1, ',', '.');?>%</h4>
                        <p class="text-muted small mb-0" title="(Lucro Bruto ÷ Faturamento) × 100"><i class="fas fa-calculator me-1"></i>(Lucro Bruto ÷ Fatur.) × 100</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round <?= $lucroLiquidoMes >= 0 ? 'border-success' : 'border-danger'; ?>">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-balance-scale fa-lg <?= $lucroLiquidoMes >= 0 ? 'text-success' : 'text-danger'; ?>"></i>
                        </div>
                        <p class="card-category">Lucro Líquido Real (Mês)</p>
                        <h4 class="card-title <?= $lucroLiquidoMes >= 0 ? 'text-success' : 'text-danger'; ?>">
                            R$ <?=number_format($lucroLiquidoMes, 2, ',', '.');?>
                        </h4>
                        <p class="text-muted small mb-0" title="Lucro Bruto - Total Despesas Fixas"><i class="fas fa-calculator me-1"></i>Lucro Bruto - Despesas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Meta e Planejamento -->
        <div class="row mb-3">
            <!-- Meta Mensal -->
            <div class="col-lg-6">
                <div class="card card-round h-100">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-bullseye me-2 text-primary"></i>Meta Mensal de Vendas</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="progress-container">
                            <div class="progress-info mb-3">
                                <div class="progress-label">
                                    <span>Progresso da Meta</span>
                                </div>
                                <div class="progress-value">
                                    <span><?=number_format(($totalValorVendas / max($MetaMensalDesejada, 1)) * 100, 1, ',', '.');?>%</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?=min(100, ($totalValorVendas / max($MetaMensalDesejada, 1)) * 100);?>%;" 
                                     aria-valuenow="<?=min(100, ($totalValorVendas / max($MetaMensalDesejada, 1)) * 100);?>" 
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="meta-info mt-4">
                            <div class="meta-item">
                                <span class="meta-label">Meta Desejada:</span>
                                <span class="meta-value fw-bold">R$ <?=number_format($MetaMensalDesejada, 2, ',', '.');?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Alcançado:</span>
                                <span class="meta-value text-success fw-bold">R$ <?=number_format($totalValorVendas, 2, ',', '.');?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Diferença:</span>
                                <span class="meta-value fw-bold <?= $totalValorVendas >= $MetaMensalDesejada ? 'text-success' : 'text-danger'; ?>">
                                    <?php
                                        if ($totalValorVendas < $MetaMensalDesejada) {
                                            echo "Faltam R$ " . number_format($MetaMensalDesejada - $totalValorVendas, 2, ',', '.');
                                        } else {
                                            echo "Excedido em R$ " . number_format($totalValorVendas - $MetaMensalDesejada, 2, ',', '.');
                                        }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projeção, Despesas, Break-even & Contas -->
            <div class="col-lg-6">
                <div class="row h-100">
                    <div class="col-md-6 mb-3">
                        <div class="card card-round h-100">
                            <div class="card-body">
                                <div class="stat-icon mb-2">
                                    <i class="fas fa-chart-line fa-lg text-primary"></i>
                                </div>
                                <p class="card-category">Projeção Mês (Run Rate)</p>
                                <h4 class="card-title">R$ <?=number_format($projecaoFaturamento, 2, ',', '.');?></h4>
                                <p class="text-muted small mb-0" title="(Faturamento Atual ÷ Dias Decorridos) × Dias do Mês"><i class="fas fa-calculator me-1"></i>(Fatur. ÷ Dia) × Dias Mês</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card card-round h-100">
                            <div class="card-body">
                                <div class="stat-icon mb-2">
                                    <i class="fas fa-money-bill-wave fa-lg text-warning"></i>
                                </div>
                                <p class="card-category">Total Despesas Fixas</p>
                                <h4 class="card-title">R$ <?=number_format($totalCustoMensal, 2, ',', '.');?></h4>
                                <p class="text-muted small mb-0" title="Salários + Despesas Fixas"><i class="fas fa-calculator me-1"></i>Salários + Despesas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-round h-100">
                            <div class="card-body">
                                <div class="stat-icon mb-2">
                                    <i class="fas fa-shopping-cart fa-lg text-danger"></i>
                                </div>
                                <p class="card-category">Unidades Break-Even</p>
                                <h4 class="card-title"><?= $pecasAVender; ?> un</h4>
                                <p class="text-muted small mb-0" title="Total Despesas ÷ Lucro Médio por Peça"><i class="fas fa-calculator me-1"></i>Total Despesas ÷ Lucro Médio</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-round h-100">
                            <div class="card-body">
                                <div class="stat-icon mb-2">
                                    <i class="fas fa-wallet fa-lg text-info"></i>
                                </div>
                                <p class="card-category">Saldo em Contas</p>
                                <h4 class="card-title">R$ <?=number_format($totalSaldoContas, 2, ',', '.');?></h4>
                                <p class="text-muted small mb-0"><i class="fas fa-university me-1"></i>Disponível em Bancos/Caixa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: Gestão de Estoque & Capital Imobilizado -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-boxes fa-lg text-primary"></i>
                        </div>
                        <p class="card-category">Estoque Total</p>
                        <h4 class="card-title"><?=$totalProdutos;?> <small style="font-size:0.8rem;">un</small></h4>
                        <p class="text-muted small mb-0"><i class="fas fa-calculator me-1"></i>Unidades Físicas</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-vault fa-lg text-warning"></i>
                        </div>
                        <p class="card-category">Capital em Estoque</p>
                        <h4 class="card-title">R$ <?=number_format($capitalImobilizado, 2, ',', '.');?></h4>
                        <p class="text-muted small mb-0" title="Soma(Preço de Custo × Quantidade em Estoque)"><i class="fas fa-calculator me-1"></i>Σ(Custo × Qtd Estoque)</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-calendar-alt fa-lg text-info"></i>
                        </div>
                        <p class="card-category">Cobertura de Estoque</p>
                        <h4 class="card-title"><?= ($diasCoberturaEstoque > 365 ? '> 365' : $diasCoberturaEstoque); ?> <small style="font-size:0.8rem;">dias</small></h4>
                        <p class="text-muted small mb-0" title="Estoque Total ÷ Média de Vendas Diárias"><i class="fas fa-calculator me-1"></i>Estoque ÷ Vendas/Dia</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round <?= $totalCritico > 0 ? 'border-danger' : ''; ?>">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                        </div>
                        <p class="card-category">Estoque Crítico</p>
                        <h4 class="card-title text-danger"><?=$totalCritico;?> <small style="font-size:0.8rem;">produtos</small></h4>
                        <p class="text-muted small mb-0"><i class="fas fa-shield-alt me-1"></i>Abaixo do Limite Mínimo</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row 5: Gráficos de Análise de Vendas -->
        <div class="row mb-3">
            <div class="col-lg-4">
                <div class="card card-round">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>Top 5 Produtos Mais Vendidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="donutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-round">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-credit-card me-2 text-success"></i>Formas de Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="donutChartTiposdePagamentos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-round">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-users me-2 text-info"></i>Tipo de Atendimento (Mês Atual)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="pieChartAtendimento"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 5: Gráficos de Padrões de Vendas -->
        <div class="row mb-3">
            <div class="col-lg-6">
                <div class="card card-round">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-bar me-2 text-warning"></i>Vendas por Dia da Semana
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="BarDiaSemanaVendas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-round">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-fire me-2 text-danger"></i>Mapa de Calor - Vendas por Hora
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="heatmapVendasHorario"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 6: Gráfico de Fluxo de Caixa -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="fas fa-chart-line me-2 text-info"></i>Fluxo de Caixa Anual - Entradas/Saídas/Saldo
                                </h5>
                                <p class="text-muted small mb-0">Acompanhe as tendências financeiras por ano e compare períodos</p>
                            </div>
                            <div class="cashflow-year-picker">
                                <label for="cashflow-year-select" class="form-label mb-1">Ano(s)</label>
                                <div class="cashflow-select-shell">
                                    <i class="fas fa-calendar-alt"></i>
                                    <select id="cashflow-year-select" class="form-select form-select-sm" multiple></select>
                                </div>
                                <small id="cashflow-year-helper" class="cashflow-year-helper">Selecione um ou mais anos para comparar</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="statisticsChart"></canvas>
                        </div>
                        <div id="myChartLegend" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
</div>
<?php
}
else
{
?>
<div class="alert alert-danger" role="alert">
  Você não tem permissão para acessar essa área.
</div>
<?php
}
?>

        <style>
            .cashflow-year-picker {
                min-width: 300px;
                max-width: 360px;
            }

            .cashflow-year-picker .form-label {
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                color: #4a5b72;
                text-transform: uppercase;
                margin-bottom: 0.35rem;
            }

            .cashflow-select-shell {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.45rem 0.65rem;
                border-radius: 12px;
                border: 1px solid #d6e0ef;
                background: linear-gradient(135deg, #f8fbff 0%, #eef4fb 100%);
                box-shadow: 0 8px 22px rgba(23, 125, 255, 0.08);
                transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            }

            .cashflow-select-shell:focus-within {
                border-color: #177dff;
                box-shadow: 0 10px 24px rgba(23, 125, 255, 0.18);
                transform: translateY(-1px);
            }

            .cashflow-select-shell i {
                color: #177dff;
                font-size: 0.9rem;
                opacity: 0.9;
            }

            #cashflow-year-select {
                border: 0;
                background: transparent;
                min-height: 68px;
                font-size: 0.92rem;
                font-weight: 600;
                color: #2f3c52;
                box-shadow: none !important;
                padding: 0.1rem 0.25rem;
            }

            #cashflow-year-select option {
                padding: 6px 8px;
                border-radius: 6px;
            }

            #cashflow-year-select option:checked {
                background: linear-gradient(135deg, #177dff 0%, #36a3ff 100%);
                color: #ffffff;
            }

            .cashflow-year-helper {
                display: block;
                margin-top: 0.4rem;
                color: #5b6f8a;
                font-size: 0.76rem;
                font-weight: 500;
            }

            @media (max-width: 768px) {
                .cashflow-year-picker {
                    min-width: 100%;
                    max-width: 100%;
                }

                #cashflow-year-select {
                    min-height: 62px;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function()
            {
                fetch('endpointProdutosMaisVendidos.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('donutChart').getContext('2d');
                    
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { size: 12 },
                                        padding: 15,
                                        usePointStyle: true,
                                        color: '#666'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.parsed + ' unid.';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Erro ao carregar dados:', error));
            });

            document.addEventListener('DOMContentLoaded', function()
            {
                fetch('endpointTiposdePagamentos.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('donutChartTiposdePagamentos').getContext('2d');
                    
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { size: 12 },
                                        padding: 15,
                                        usePointStyle: true,
                                        color: '#666'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.parsed + ' vendas';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Erro ao carregar dados:', error));
            });

            document.addEventListener('DOMContentLoaded', function()
            {
                fetch('endpointTipoAtendimento.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('pieChartAtendimento').getContext('2d');
                    const totalVendas = data.data.reduce((a, b) => a + b, 0);
                    
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { size: 12 },
                                        padding: 15,
                                        usePointStyle: true,
                                        color: '#666'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed;
                                            const percentage = totalVendas > 0 ? ((value / totalVendas) * 100).toFixed(1) : 0;
                                            return `${context.label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Erro ao carregar dados de tipo de atendimento:', error));
            });

            document.addEventListener('DOMContentLoaded', function()
            {
                fetch('endpointDiasVendas.php')
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('BarDiaSemanaVendas').getContext('2d');
                        
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Vendas por Dia',
                                    data: data.data,
                                    backgroundColor: 'rgba(255, 167, 38, 0.8)',
                                    borderColor: 'rgba(255, 167, 38, 1)',
                                    borderWidth: 2,
                                    borderRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(0,0,0,0.05)'
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Erro ao carregar dados:', error));
            });

            document.addEventListener('DOMContentLoaded', function() {
                fetch('endpointVendasPorHora.php')
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('heatmapVendasHorario').getContext('2d');
                        
                        new Chart(ctx, {
                            type: 'matrix',
                            data: {
                                datasets: [{
                                    label: 'Vendas por Hora',
                                    data: data.data,
                                    backgroundColor: function(context) {
                                        const value = context.dataset.data[context.dataIndex].v;
                                        const max = data.maxValue > 0 ? data.maxValue : 1;
                                        const alpha = Math.min(0.9, Math.max(0.2, value / max));
                                        return `rgba(255, 99, 132, ${alpha})`;
                                    },
                                    borderWidth: 1,
                                    borderColor: '#fff',
                                    width: function(context) {
                                        const chart = context.chart;
                                        const {chartArea} = chart;
                                        if (!chartArea) {
                                            return 0;
                                        }
                                        return (chartArea.right - chartArea.left) / 7 - 1;
                                    },
                                    height: function(context) {
                                        const chart = context.chart;
                                        const {chartArea} = chart;
                                        if (!chartArea) {
                                            return 0;
                                        }
                                        return (chartArea.bottom - chartArea.top) / 24 - 1;
                                    }
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            title: function(context) {
                                                const dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                                return dias[context[0].raw.x] + ' às ' + context[0].raw.y + ':00h';
                                            },
                                            label: function(context) {
                                                return 'Vendas: ' + context.raw.v;
                                            }
                                        }
                                    },
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    x: {
                                        type: 'category',
                                        labels: data.diasSemana,
                                        offset: true,
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        type: 'category',
                                        labels: data.horas,
                                        offset: true,
                                        reverse: true,
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Erro ao carregar dados:', error));
            });

            document.addEventListener("DOMContentLoaded", function () {
                const monthsLabels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];
                const yearSelect = document.getElementById('cashflow-year-select');
                const yearHelper = document.getElementById('cashflow-year-helper');
                const legendContainer = document.getElementById('myChartLegend');
                const ctx = document.getElementById('statisticsChart').getContext('2d');
                let statisticsChart = null;

                const entradaColors = ['#fdaf4b', '#f8961e', '#f9844a', '#f9c74f', '#f6bd60'];
                const saidaColors = ['#f3545d', '#d90429', '#ef476f', '#ff6b6b', '#c1121f'];
                const saldoColors = ['#177dff', '#4361ee', '#3a0ca3', '#00b4d8', '#0077b6'];

                function rgbaFromHex(hexColor, alpha) {
                    const hex = hexColor.replace('#', '');
                    const bigint = parseInt(hex, 16);
                    const r = (bigint >> 16) & 255;
                    const g = (bigint >> 8) & 255;
                    const b = bigint & 255;
                    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                }

                function renderLegend(chart) {
                    if (!legendContainer || !chart) {
                        return;
                    }

                    // For Chart.js v4, items might be in different places depending on plugin state
                    let items = [];
                    if (chart.legend && chart.legend.legendItems) {
                        items = chart.legend.legendItems;
                    } else if (chart.options.plugins.legend && chart.options.plugins.legend.labels && chart.options.plugins.legend.labels.generateLabels) {
                        items = chart.options.plugins.legend.labels.generateLabels(chart);
                    }

                    if (!items || items.length === 0) return;

                    let legendHtml = '<div style="display: flex; flex-wrap: wrap; justify-content: center; width: 100%; padding: 10px 0;">';
                    items.forEach(item => {
                        const color = item.strokeStyle || item.fillStyle || '#999';
                        legendHtml += `<div style="display: flex; align-items: center; margin: 8px 18px; cursor: pointer; white-space: nowrap; transition: opacity 0.2s;" onclick="toggleDataset(${item.datasetIndex})">` + 
                                      `<span style="display: inline-block; width: 14px; height: 14px; background: ${color}; border-radius: 3px; margin-right: 10px; opacity: ${item.hidden ? 0.3 : 1}; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></span>` +
                                      `<span style="text-decoration: ${item.hidden ? 'line-through' : 'none'}; opacity: ${item.hidden ? 0.6 : 1}; font-size: 13px; font-weight: 600; color: #4a5b72; user-select: none;">${item.text}</span>` +
                                      `</div>`;
                    });
                    legendHtml += '</div>';
                    legendContainer.innerHTML = legendHtml;
                }

                window.toggleDataset = function(index) {
                    if (!statisticsChart) return;
                    const isVisible = statisticsChart.isDatasetVisible(index);
                    if (isVisible) {
                        statisticsChart.hide(index);
                    } else {
                        statisticsChart.show(index);
                    }
                    renderLegend(statisticsChart);
                };

                function buildDatasets(series) {
                    const datasets = [];

                    series.forEach((item, index) => {
                        const entradaColor = entradaColors[index % entradaColors.length];
                        const saidaColor = saidaColors[index % saidaColors.length];
                        const saldoColor = saldoColors[index % saldoColors.length];
                        const ano = item.ano;

                        datasets.push({
                            label: `Entradas ${ano}`,
                            borderColor: entradaColor,
                            pointBackgroundColor: entradaColor,
                            pointBorderColor: '#fff',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            backgroundColor: rgbaFromHex(entradaColor, 0.12),
                            fill: false,
                            borderWidth: 2,
                            tension: 0.35,
                            data: item.entradas
                        });

                        datasets.push({
                            label: `Saídas ${ano}`,
                            borderColor: saidaColor,
                            pointBackgroundColor: saidaColor,
                            pointBorderColor: '#fff',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            backgroundColor: rgbaFromHex(saidaColor, 0.12),
                            fill: false,
                            borderWidth: 2,
                            tension: 0.35,
                            data: item.saidas
                        });

                        datasets.push({
                            label: `Saldo ${ano}`,
                            borderColor: saldoColor,
                            pointBackgroundColor: saldoColor,
                            pointBorderColor: '#fff',
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            backgroundColor: 'transparent',
                            fill: false,
                            borderWidth: 2,
                            borderDash: [6, 4],
                            tension: 0.35,
                            data: item.saldoAcumulado,
                            yAxisID: 'y-axis-saldo'
                        });
                    });

                    return datasets;
                }

                function renderChart(payload) {
                    const datasets = buildDatasets(payload.series || []);

                    if (statisticsChart) {
                        statisticsChart.destroy();
                    }

                    statisticsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthsLabels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    padding: 10,
                                    titleFont: { size: 12 },
                                    bodyFont: { size: 12 },
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.dataset.label}: R$ ${Number(context.parsed.y).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    position: 'left',
                                    ticks: {
                                        font: { size: 11 },
                                        callback: function(value) {
                                            return 'R$ ' + Number(value).toLocaleString('pt-BR');
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                'y-axis-saldo': {
                                    position: 'right',
                                    ticks: {
                                        font: { size: 11 },
                                        callback: function(value) {
                                            return 'R$ ' + Number(value).toLocaleString('pt-BR');
                                        }
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });

                    renderLegend(statisticsChart);
                }

                function getSelectedYears() {
                    return Array.from(yearSelect.selectedOptions).map(option => option.value).filter(Boolean);
                }

                function updateYearHelper(selectedYears) {
                    if (!yearHelper) {
                        return;
                    }

                    if (!selectedYears || selectedYears.length === 0) {
                        yearHelper.textContent = 'Selecione um ou mais anos para comparar';
                        return;
                    }

                    if (selectedYears.length === 1) {
                        yearHelper.textContent = `Ano selecionado: ${selectedYears[0]}`;
                        return;
                    }

                    yearHelper.textContent = `${selectedYears.length} anos selecionados: ${selectedYears.join(', ')}`;
                }

                function loadChartBySelectedYears() {
                    const years = getSelectedYears();
                    updateYearHelper(years);
                    const query = years.length ? `?years=${encodeURIComponent(years.join(','))}` : '';

                    fetch(`endpoint.php${query}`)
                        .then(response => response.json())
                        .then(payload => {
                            renderChart(payload);
                        })
                        .catch(error => console.error('Erro ao carregar os dados:', error));
                }

                fetch('endpoint.php')
                    .then(response => response.json())
                    .then(payload => {
                        const anos = payload.yearsAvailable || [];
                        const selecionados = new Set((payload.selectedYears || []).map(String));

                        yearSelect.innerHTML = '';
                        anos.forEach(ano => {
                            const option = document.createElement('option');
                            option.value = String(ano);
                            option.textContent = String(ano);
                            option.selected = selecionados.has(String(ano));
                            yearSelect.appendChild(option);
                        });

                        updateYearHelper(Array.from(selecionados));
                        renderChart(payload);
                    })
                    .catch(error => console.error('Erro ao carregar anos do fluxo de caixa:', error));

                yearSelect.addEventListener('change', function() {
                    if (yearSelect.selectedOptions.length === 0 && yearSelect.options.length > 0) {
                        yearSelect.options[0].selected = true;
                    }
                    updateYearHelper(getSelectedYears());
                    loadChartBySelectedYears();
                });
            });
        </script>