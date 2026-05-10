<?php
    include('config.php');
    include('funcoes.php');

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

        (SELECT COUNT(*) FROM clientes) AS total_clientes,

        (SELECT SUM(quantidade) FROM produtos) AS total_produtos,

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
        
        $totalVendas = $results['total_vendas'] ?? 0;
        $totalValorVendas = $results['total_valor_vendas'] ?? 0;
        $totalItensVendidos = $results['total_itens_vendidos'] ?? 0;
        $totalClientes = $results['total_clientes'] ?? 0;
        $totalProdutos = $results['total_produtos'] ?? 0;
        $totalMediaPrecoVenda = $results['media_preco_venda'] ?? 0;
        $totalCritico = $results['total_critico'] ?? 0;
        $totalDespesasFixas = $results['total_despesasfixas'] ?? 0;
        $totalSaldoContas = $results['total_saldo_contas'] ?? 0;
    }
    catch (PDOException $e)
    {
        error_log("Erro ao buscar dados do dashboard: " . $e->getMessage());
        // Exibir uma mensagem genérica para o usuário
        echo "Erro ao carregar dados. Por favor, tente novamente mais tarde.";
    }

    $somaSalarioColaboradores = BuscarSomaPorTabela('colaboradores', 'salario');
    $somaDespesasFixas = BuscarSomaPorTabela('despesasfixas', 'valor');
    $totalCustoMensal = $somaSalarioColaboradores+$somaDespesasFixas;

    $custoMedioProduto = BuscarCustoMedioProdutos('produtos') ?? 0;

    $lucroMedio = BuscarLucroMedioProdutos('produtos', 'salario') ?? 0;

    // Verifica se as variáveis estão definidas e se $lucroMedio não é zero
    if (isset($totalDespesasFixas, $somaSalarioColaboradores, $lucroMedio) && $lucroMedio != 0)
    {
        $pecasAVender = ceil(($totalDespesasFixas + $somaSalarioColaboradores) / $lucroMedio);
    }
    else
    {
        $pecasAVender = 0; // Valor padrão caso as condições não sejam atendidas
    }

    $MetaMensalDesejada = $pecasAVender*$totalMediaPrecoVenda ?? 0;
?>
<?php
if($user['isAdmin']==true)
{
?>
    <div class="page-inner">
        <!-- Seção KPIs Principais -->
        <div class="dashboard-header mb-4">
            <h2 class="page-title">Dashboard de Vendas</h2>
            <p class="text-muted">Acompanhe os indicadores do seu negócio</p>
        </div>

        <!-- Row 1: Principais Métricas -->
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
                                    <span class="card-subtitle">Este mês</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valor Total Vendido -->
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
                                    <span class="card-subtitle">Mês atual</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itens Vendidos -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round gradient-orange">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-warning bubble-shadow-small">
                                    <i class="fas fa-boxes"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category">Itens Vendidos</p>
                                    <h4 class="card-title"><?=$totalItensVendidos;?></h4>
                                    <span class="card-subtitle">Unidades</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Saldo em Contas -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round gradient-purple">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-info bubble-shadow-small">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category">Saldo em Contas</p>
                                    <h4 class="card-title">R$ <?=number_format($totalSaldoContas, 2, ',', '.');?></h4>
                                    <span class="card-subtitle">Total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Meta e Status -->
        <div class="row mb-3">
            <div class="col-lg-6">
                <div class="card card-round">
                    <div class="card-header">
                        <h5 class="card-title">Meta Mensal</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress-container">
                            <div class="progress-info mb-3">
                                <div class="progress-label">
                                    <span>Progresso da Meta</span>
                                </div>
                                <div class="progress-value">
                                    <span><?=number_format(($totalValorVendas / max($MetaMensalDesejada, 1)) * 100, 1, ',', '.');?>%</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?=min(100, ($totalValorVendas / max($MetaMensalDesejada, 1)) * 100);?>%;" 
                                     aria-valuenow="<?=min(100, ($totalValorVendas / max($MetaMensalDesejada, 1)) * 100);?>" 
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="meta-info mt-4">
                            <div class="meta-item">
                                <span class="meta-label">Meta Desejada:</span>
                                <span class="meta-value">R$ <?=number_format($MetaMensalDesejada, 2, ',', '.');?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Alcançado:</span>
                                <span class="meta-value text-success">R$ <?=number_format($totalValorVendas, 2, ',', '.');?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Diferença:</span>
                                <span class="meta-value <?= $totalValorVendas >= $MetaMensalDesejada ? 'text-success' : 'text-danger'; ?>">
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

            <!-- Estoque e Produtos -->
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-round">
                            <div class="card-body text-center">
                                <div class="stat-icon mb-3">
                                    <i class="fas fa-box fa-2x text-primary"></i>
                                </div>
                                <h5 class="card-title">Estoque Total</h5>
                                <h2 class="stat-value"><?=$totalProdutos;?></h2>
                                <p class="text-muted small">Unidades em estoque</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-round">
                            <div class="card-body text-center <?= $totalCritico > 0 ? 'bg-light-danger' : ''; ?>">
                                <div class="stat-icon mb-3">
                                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                </div>
                                <h5 class="card-title">Nível Crítico</h5>
                                <h2 class="stat-value text-danger"><?=$totalCritico;?></h2>
                                <p class="text-muted small">Produtos abaixo do limite</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Análise Financeira -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-money-bill-wave fa-lg text-warning"></i>
                        </div>
                        <p class="card-category">Total Despesas</p>
                        <h4 class="card-title">R$ <?=number_format($totalCustoMensal, 2, ',', '.');?></h4>
                        <p class="text-muted small">Salários + Despesas</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-tags fa-lg text-info"></i>
                        </div>
                        <p class="card-category">Custo Médio</p>
                        <h4 class="card-title">R$ <?= number_format($custoMedioProduto, 2, ',', '.'); ?></h4>
                        <p class="text-muted small">Por unidade</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-arrow-up fa-lg text-success"></i>
                        </div>
                        <p class="card-category">Lucro Médio</p>
                        <h4 class="card-title">R$ <?=number_format($lucroMedio, 2, ',', '.');?></h4>
                        <p class="text-muted small">Por unidade</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-round">
                    <div class="card-body">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-shopping-cart fa-lg text-danger"></i>
                        </div>
                        <p class="card-category">Unidades Break-Even</p>
                        <h4 class="card-title"><?= $pecasAVender; ?></h4>
                        <p class="text-muted small">Para cobrir custos</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row 4: Gráficos de Análise de Vendas -->
        <div class="row mb-3">
            <div class="col-lg-6">
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

            <div class="col-lg-6">
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
                                        const alpha = Math.min(0.9, Math.max(0.2, value / data.maxValue));
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
                    if (!legendContainer || !chart || !chart.legend) {
                        return;
                    }

                    const items = chart.legend.legendItems || [];
                    let legendHtml = '<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 10px 16px;">';
                    items.forEach(item => {
                        const color = item.strokeStyle || item.fillStyle || '#999';
                        legendHtml += `<li style="display:flex; align-items:center;"><span style="display:inline-block; width:12px; height:12px; background:${color}; border-radius:2px; margin-right:8px;"></span>${item.text}</li>`;
                    });
                    legendHtml += '</ul>';
                    legendContainer.innerHTML = legendHtml;
                }

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