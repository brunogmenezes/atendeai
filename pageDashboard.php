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

        (SELECT SUM(total) 
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
    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total vendas no mês atual</p>
                                <h4 class="card-title"><?=$totalVendas;?></h4>
                                <p class="card-category">Total itens vendidos no mês atual</p>
                                <h4 class="card-title"><?=$totalItensVendidos;?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4" >
            <div class="card card-stats card-round">
                <div class="card-body">
                        <div class="col col-stats ms-4 ms-sm-5">
                            <div class="numbers">
                                <p class="card-category" style="text-align: center">Meta Mensal<br/>Desejada | Alcançada</p>
                                <h4 class="card-title" style="text-align: center">R$<?=number_format($MetaMensalDesejada, 2, ',', '.');?> | R$<?=number_format($totalValorVendas, 2, ',', '.');?></h4>
                                <h6 style="text-align: center">
                                <?php
                                    if ($totalValorVendas<$MetaMensalDesejada)
                                    {
                                        echo "😢 ainda falta: R$";
                                        echo number_format($MetaMensalDesejada - $totalValorVendas, 2, ',', '.');
                                    }
                                    elseif($totalValorVendas>$MetaMensalDesejada)
                                    {
                                        echo "🎉passou da meta: R$";
                                        echo number_format($totalValorVendas - $MetaMensalDesejada, 2, ',', '.');
                                    }
                                ?>
                            </h6>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-luggage-cart"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Produtos em Estoque</p>
                                <h4 class="card-title"><?=$totalProdutos;?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-luggage-cart"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Estoque saldo Critco</p>
                                <h4 class="card-title"><?=$totalCritico;?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-chart-area"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Despesas Mensais</p>
                                <h4 class="card-title">
                                    <?php
                                        
                                    ?>
                                    R$ <?=number_format($totalCustoMensal, 2, ',', '.');?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-donate"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Média Custo por Produto (simples)</p>
                                <h4 class="card-title">
                                    R$ <?= number_format($custoMedioProduto, 2, ',', '.'); ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-divide"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Média Lucro Bruto(simples)</p>
                                <h4 class="card-title">
                                    R$ <?=number_format($lucroMedio, 2, ',', '.');?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Unidades à vender para Cobrir Custos</p>
                                <h4 class="card-title">
                                    <?= $pecasAVender; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-money-check-alt"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Saldo em Contas</p>
                                <h4 class="card-title">R$ <?=number_format($totalSaldoContas, 2, ',', '.');?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">TOP 5 Produtos Mais Vendidos</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="donutChart" style="width: 50%; height: 50%"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">TOP Tipos de Pagamentos</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="donutChartTiposdePagamentos" style="width: 50%; height: 50%"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Vendas por Dia</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="BarDiaSemanaVendas" style="width: 50%; height: 50%"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Vendas por Hora (Mapa de Calor)</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="heatmapVendasHorario" style="width: 100%; height: 400px"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-sm-6 col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            Dados Entradas/Saídas
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="statisticsChart"></canvas>
                    </div>
                    <div id="myChartLegend"></div>
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

        <script>
            document.addEventListener('DOMContentLoaded', function()
            {
                fetch('endpointProdutosMaisVendidos.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('donutChart').getContext('2d');
                    
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                borderWidth: 0
                            }]
                        },
                        options:
                        {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend:
                            {
                                position: "bottom",
                                labels:
                                {
                                    fontColor: "rgb(154, 154, 154)",
                                    fontSize: 11,
                                    usePointStyle: true,
                                    padding: 20,
                                },
                        },
                        pieceLabel: {
                          render: "percentage",
                          fontColor: "white",
                          fontSize: 14,
                        },
                        tooltips: false,
                        layout: {
                          padding: {
                            left: 20,
                            right: 20,
                            top: 20,
                            bottom: 20,
                          },
                        },
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
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                borderWidth: 1
                            }]
                        },
                        options:
                        {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend:
                            {
                                position: "bottom",
                                labels:
                                {
                                    fontColor: "rgb(154, 154, 154)",
                                    fontSize: 11,
                                    usePointStyle: true,
                                    padding: 20,
                                },
                            },
                            pieceLabel:
                            {
                                render: "percentage",
                                fontColor: "white",
                                fontSize: 14,
                            },
                            tooltips: false,
                            layout:
                            {
                                padding:
                                {
                                    left: 20,
                                    right: 20,
                                    top: 20,
                                    bottom: 20,
                                },
                            },
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
                                label: 'Limpar',
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                borderWidth: 2
                            }]
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
                            const alpha = Math.min(0.9, Math.max(0.1, value / data.maxValue));
                            return `rgba(54, 162, 235, ${alpha})`;
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
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].raw.x + ' às ' + context[0].raw.y + 'h';
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
    var ctx = document.getElementById('statisticsChart').getContext('2d');

    // Fazer a requisição ao endpoint
    fetch('endpoint.php')
        .then(response => response.json())
        .then(data => {
            var statisticsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
                    datasets: [
                        {
                            label: "Saídas",
                            borderColor: '#f3545d',
                            pointBackgroundColor: 'rgba(243, 84, 93, 0.6)',
                            pointRadius: 0,
                            backgroundColor: 'rgba(243, 84, 93, 0.4)',
                            legendColor: '#f3545d',
                            fill: true,
                            borderWidth: 2,
                            data: data.saidas
                        }, 
                        {
                            label: "Entradas",
                            borderColor: '#fdaf4b',
                            pointBackgroundColor: 'rgba(253, 175, 75, 0.6)',
                            pointRadius: 0,
                            backgroundColor: 'rgba(253, 175, 75, 0.4)',
                            legendColor: '#fdaf4b',
                            fill: true,
                            borderWidth: 2,
                            data: data.entradas
                        },
                        {
                            label: "Saldo Acumulado",
                            borderColor: '#177dff',
                            pointBackgroundColor: 'rgba(23, 125, 255, 0.6)',
                            pointRadius: 3,
                            backgroundColor: 'transparent',
                            legendColor: '#177dff',
                            fill: false,
                            borderWidth: 3,
                            borderDash: [5, 5],
                            data: data.saldoAcumulado,
                            yAxisID: 'y-axis-saldo' // Eixo Y separado para o saldo
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        bodySpacing: 4,
                        mode: "nearest",
                        intersect: 0,
                        position: "nearest",
                        xPadding: 10,
                        yPadding: 10,
                        caretPadding: 10
                    },
                    layout: {
                        padding: { left: 5, right: 5, top: 15, bottom: 15 }
                    },
                    scales: {
                        yAxes: [
                            {
                                id: 'y-axis-1',
                                position: 'left',
                                ticks: {
                                    fontStyle: "500",
                                    beginAtZero: false,
                                    maxTicksLimit: 5,
                                    padding: 10
                                },
                                gridLines: {
                                    drawTicks: false,
                                    display: true
                                }
                            },
                            {
                                id: 'y-axis-saldo',
                                position: 'right',
                                ticks: {
                                    fontStyle: "500",
                                    beginAtZero: false,
                                    maxTicksLimit: 5,
                                    padding: 10
                                },
                                gridLines: {
                                    drawTicks: false,
                                    display: false
                                }
                            }
                        ],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 10,
                                fontStyle: "500"
                            }
                        }]
                    },
                    legendCallback: function(chart) {
                        var text = [];
                        text.push('<ul class="' + chart.id + '-legend html-legend">');
                        for (var i = 0; i < chart.data.datasets.length; i++) {
                            text.push('<li><span style="background-color:' + chart.data.datasets[i].legendColor + '"></span>');
                            if (chart.data.datasets[i].label) {
                                text.push(chart.data.datasets[i].label);
                            }
                            text.push('</li>');
                        }
                        text.push('</ul>');
                        return text.join('');
                    }
                }
            });

            // Adiciona a legenda manualmente (se necessário)
            document.getElementById('chart-legend').innerHTML = statisticsChart.generateLegend();
        })
        .catch(error => console.error('Erro ao carregar os dados:', error));
});
</script>