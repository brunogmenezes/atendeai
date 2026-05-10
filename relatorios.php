<?php
ini_set('memory_limit', '512M');
require_once 'dompdf/dompdf/vendor/autoload.php';
include("config.php");
include("funcoes.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Validação de parâmetros
$tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], ['estoque', 'financeiro-mensal', 'fluxo-caixa']) ? $_GET['tipo'] : null;
$startData = isset($_GET['start']) && strtotime($_GET['start']) ? $_GET['start'] : null;
$endData = isset($_GET['end']) && strtotime($_GET['end']) ? $_GET['end'] : null;

if (!$tipo) {
    die('❌ Tipo de relatório inválido');
}

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Helvetica');
$options->set('isFontSubsettingEnabled', true);
$options->set('dpi', 72);

$dompdf = new Dompdf($options);


$html = '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; color: #2c3e50; line-height: 1.6; background-color: #fff; }
        .header { background: #667eea; color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; font-weight: 700; }
        .header p { font-size: 13px; opacity: 0.9; }
        .info-section { background: #f5f7fa; padding: 25px; border-left: 4px solid #667eea; border-radius: 6px; margin-bottom: 30px; }
        .info-section h3 { font-size: 16px; margin-bottom: 15px; color: #2c3e50; font-weight: 700; }
        .dados-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .dados-item { background: white; padding: 12px; border-radius: 4px; border: 1px solid #e9ecef; }
        .dados-item label { display: block; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; font-weight: 600; }
        .dados-item .value { font-size: 16px; font-weight: 700; color: #667eea; }
        .resumo-pagamentos { background: white; padding: 15px; border-radius: 4px; }
        .pagamento-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eee; font-size: 12px; }
        .pagamento-item:last-child { border-bottom: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border-radius: 6px; overflow: hidden; table-layout: auto; }
        thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        th { padding: 8px 10px; text-align: left; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; font-size: 9px; }
        td { padding: 6px 10px; border-bottom: 1px solid #ecf0f1; }
        tbody tr:nth-child(even) { background-color: #f8f9fa; }
        tbody tr:hover { background-color: #f0f2f5; }
        .estornado { opacity: 0.6; font-style: italic; text-decoration: line-through; background-color: #ffebee !important; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .legend { background: #fdfdfd; padding: 10px; border: 1px solid #eee; border-radius: 4px; margin-bottom: 15px; font-size: 9px; color: #7f8c8d; line-height: 1.4; }
        .legend-title { font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 10px; text-transform: uppercase; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #ecf0f1; text-align: center; font-size: 10px; color: #95a5a6; }
        @media print { body { margin: 0; padding: 0; } .header { margin-bottom: 20px; } }
    </style>
</head>';

                if($tipo=='estoque')
                {
                    $totalItensEstoque = buscarTotalemEstoqueRelatorio();
                    $mediaCustoItemEstoque = BuscarCustoMedioProdutos('produtos') ?? 0;
                    $mediaVendaItemEstoque = buscarMediaVendaProdutosRelatorio();
                    $lucroMedioItemEstoque = BuscarLucroMedioProdutos('produtos', 'salario') ?? 0;
                    $valoresEstoque = calcularValorTotalEstoqueRelatorio();

                $html .='
                <body>
                    <div class="header">
                        <h1>Relatório de Estoque</h1>
                        <p>Emitido em ' . date('d/m/Y H:i:s') . '</p>
                    </div>

                    <div class="info-section">
                        <h3>Resumo do Estoque</h3>
                        <div class="dados-grid">
                            <div class="dados-item">
                                <label>Total de Itens</label>
                                <div class="value">' . number_format($totalItensEstoque, 0, ',', '.') . ' un</div>
                            </div>
                            <div class="dados-item">
                                <label>Preço Médio Custo</label>
                                <div class="value">R$ ' . number_format($mediaCustoItemEstoque, 2, ',', '.') . '</div>
                            </div>
                            <div class="dados-item">
                                <label>Preço Médio Venda</label>
                                <div class="value">R$ ' . number_format($mediaVendaItemEstoque, 2, ',', '.') . '</div>
                            </div>
                            <div class="dados-item">
                                <label>Lucro Médio por Item</label>
                                <div class="value">R$ ' . number_format($lucroMedioItemEstoque, 2, ',', '.') . '</div>
                            </div>
                            <div class="dados-item">
                                <label>Valor Estoque (Custo)</label>
                                <div class="value">R$ ' . number_format($valoresEstoque['total_custo'], 2, ',', '.') . '</div>
                            </div>
                            <div class="dados-item">
                                <label>Valor Estoque (Venda)</label>
                                <div class="value">R$ ' . number_format($valoresEstoque['total_venda'], 2, ',', '.') . '</div>
                            </div>
                            <div class="dados-item">
                                <label>Lucro Potencial Total</label>
                                <div class="value">R$ ' . number_format(($valoresEstoque['total_venda'] - $valoresEstoque['total_custo']), 2, ',', '.') . '</div>
                            </div>
                            <div class="dados-item">
                                <label>Margem Lucrativa</label>
                                <div class="value">' . ($valoresEstoque['total_custo'] > 0 ? number_format(($valoresEstoque['total_venda'] - $valoresEstoque['total_custo']) / $valoresEstoque['total_custo'] * 100, 2, ',', '.') : '0') . '%</div>
                            </div>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome do Produto</th>
                                <th class="text-center">Quantidade</th>
                                <th class="text-right">Preço Custo</th>
                                <th class="text-right">Preço Venda</th>
                            </tr>
                        </thead>
                        <tbody>';

            $produtos = buscarProdutosRelatorios();
            if (!empty($produtos)) {
                foreach ($produtos as $produto) {
                    $style = ($produto['estornado'] ?? false) ? 'class="estornado"' : '';
                    $html .= '<tr ' . $style . '>';
                    $html .= '<td class="text-center">#' . htmlspecialchars($produto['id']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($produto['nome']) . '</td>';
                    $html .= '<td class="text-center">' . intval($produto['quantidade']) . ' un</td>';
                    $html .= '<td class="text-right">R$ ' . number_format($produto['preco_custo'], 2, ',', '.') . '</td>';
                    $html .= '<td class="text-right">R$ ' . number_format($produto['preco_venda'], 2, ',', '.') . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr><td colspan="5" class="text-center">Nenhum produto encontrado</td></tr>';
            }


            $html .= '</tbody>
                    </table>

                    <div class="footer">
                        <p>Relatório gerado automaticamente pelo sistema AtendAI</p>
                        <p>Data de Emissão: ' . date('d/m/Y H:i:s') . '</p>
                    </div>
                </body>
            </html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Headers para melhor performance
            header('Cache-Control: public, max-age=3600');
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="relatorio_estoque_' . date('Y-m-d') . '.pdf"');

            $dompdf->stream("relatorio_estoque_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
            exit;
        }

else if($tipo=='financeiro-mensal')
{
    $numeroVendasPeriodo = contarNumeroVendasPeriodo($startData, $endData) ?? 0;
    
    // Busca totais do período completo via SQL para o resumo ser preciso
    $totaisPeriodo = buscarTotaisVendasPeriodoRelatorio($startData, $endData);
    $totalBruto = $totaisPeriodo['total_bruto'] ?? 0;
    $totalLiquido = $totaisPeriodo['total_liquido'] ?? 0;
    
    $totaisPorPagamento = buscarTotalVendasPorTipoPagamentoPeriodo($startData, $endData) ?? [];
    $LIMITE_VENDAS_PDF = 1000;
    $vendas = buscarTabelaVendasRelatorio('vendas', '', '', 0, $LIMITE_VENDAS_PDF, 'DESC', $startData, $endData) ?? [];
    $lucroMedio = BuscarLucroMedioProdutos('produtos', 'salario') ?? 0;

    $html .='
    <body>
        <div class="header">
            <h1>Relatório de Vendas no Período</h1>
            <p>Emitido em ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <div class="info-section">
            <h3>Resumo do Período</h3>
            <div class="dados-grid">
                <div class="dados-item">
                    <label>Período</label>
                    <div class="value">De ' . date('d/m/Y', strtotime($startData)) . ' até ' . date('d/m/Y', strtotime($endData)) . '</div>
                </div>
                <div class="dados-item">
                    <label>Total de Transações</label>
                    <div class="value">' . $numeroVendasPeriodo . '</div>
                </div>
                <div class="dados-item">
                    <label>Total Faturamento Bruto</label>
                    <div class="value">R$ ' . number_format($totalBruto, 2, ',', '.') . '</div>
                </div>
                <div class="dados-item">
                    <label>Total Faturamento Líquido</label>
                    <div class="value">R$ ' . number_format($totalLiquido, 2, ',', '.') . '</div>
                </div>
                <div class="dados-item">
                    <label>Ticket Médio</label>
                    <div class="value">R$ ' . ($numeroVendasPeriodo > 0 ? number_format($totalLiquido / $numeroVendasPeriodo, 2, ',', '.') : '0,00') . '</div>
                </div>
                <div class="dados-item">
                    <label>Lucro Médio Geral</label>
                    <div class="value">R$ ' . number_format($lucroMedio, 2, ',', '.') . '</div>
                </div>
            </div>
        </div>';
// Distribuição por tipo de pagamento
if (!empty($totaisPorPagamento)) {
    $html .= '
        <div class="info-section">
            <h3>Distribuição por Tipo de Pagamento</h3>
            <div class="resumo-pagamentos">';
    
    foreach ($totaisPorPagamento as $pagamento) {
        $html .= '<div class="pagamento-item">
            <span>' . htmlspecialchars($pagamento['tipo_pagamento']) . '</span>
            <span>R$ ' . number_format($pagamento['total_vendas'], 2, ',', '.') . '</span>
        </div>';
    }
    
    $html .= '</div>
        </div>';
}


    if ($numeroVendasPeriodo > $LIMITE_VENDAS_PDF) {
        $html .= '<div style="color: #b00; font-weight: bold; margin-bottom: 10px;">* Volume de vendas grande (' . $numeroVendasPeriodo . '). Exibindo apenas as primeiras ' . $LIMITE_VENDAS_PDF . ' vendas no detalhamento.</div>';
    }

    $html .= '<table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-right">Valor Bruto</th>
                    <th class="text-center">Desconto (%)</th>
                    <th class="text-right">Valor Líquido</th>
                    <th>Tipo Pagamento</th>
                    <th>Vendedor</th>
                    <th class="text-center">Data/Hora</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background: #f1f4f9; font-style: italic; font-size: 8px; color: #7f8c8d;">
                    <td class="text-center">Nº Venda</td>
                    <td class="text-right">Valor Bruto</td>
                    <td class="text-center">%Desconto</td>
                    <td class="text-right">Valor Líquido</td>
                    <td>Forma de Pagto</td>
                    <td>Vendedor</td>
                    <td class="text-center">Dia e Horário</td>
                </tr>';

        foreach ($vendas as $venda) {
            $style = $venda['estornado'] ? 'class="estornado"' : '';
            $html .= '<tr ' . $style . '>';
            $html .= '<td class="text-center">#' . htmlspecialchars($venda['id']) . '</td>';
            $html .= '<td class="text-right">R$ ' . number_format($venda['total'], 2, ',', '.') . '</td>';
            $html .= '<td class="text-center">' . number_format($venda['desconto'] ?? 0, 1, ',', '.') . '%</td>';
            $html .= '<td class="text-right"><strong>R$ ' . number_format($venda['total_liquido'] ?? $venda['total'], 2, ',', '.') . '</strong></td>';
            $html .= '<td>' . htmlspecialchars($venda['tipos_pagamento']) . '</td>';
            $html .= '<td>' . htmlspecialchars($venda['usuariovendedor']) . '</td>';
            $html .= '<td class="text-center">' . date('d/m/Y H:i', strtotime($venda['data_venda'])) . '</td>';
            $html .= '</tr>';
        }

        if (empty($vendas)) {
            $html .= '<tr><td colspan="7" class="text-center">Nenhuma venda encontrada para o período</td></tr>';
        }

        $html .= '</tbody>
                    </table>';

    $html .= '<div class="footer">
        <p>Relatório gerado automaticamente pelo sistema AtendAI</p>
        <p>Data de Emissão: ' . date('d/m/Y H:i:s') . '</p>
    </div>
</body>
</html>';


    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Headers para melhor performance
    header('Cache-Control: public, max-age=3600');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="relatorio_vendas_' . date('Y-m-d') . '.pdf"');


    $dompdf->stream("relatorio_vendas_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
    exit;
}
else if($tipo=='fluxo-caixa')
{
    $fluxo = buscarDadosFluxoCaixa($startData, $endData) ?? [];
    
    $totalEntradas = 0;
    $totalSaidas = 0;
    $vendasTotal = 0;
    $outrasEntradasTotal = 0;

    foreach ($fluxo as $dia) {
        $totalEntradas += $dia['total_entradas'];
        $totalSaidas += $dia['total_saidas'];
        $vendasTotal += $dia['vendas'];
        $outrasEntradasTotal += $dia['outras_entradas'];
    }
    $saldoFinal = $totalEntradas - $totalSaidas;

    $html .='
    <body>
        <div class="header">
            <h1>Relatório de Fluxo de Caixa</h1>
            <p>Emitido em ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <div class="info-section">
            <h3>Visão Geral do Período (' . date('d/m/Y', strtotime($startData)) . ' - ' . date('d/m/Y', strtotime($endData)) . ')</h3>
            <div class="dados-grid">
                <div class="dados-item">
                    <label>Total de Entradas</label>
                    <div class="value">R$ ' . number_format($totalEntradas, 2, ',', '.') . '</div>
                </div>
                <div class="dados-item">
                    <label>Total de Saídas</label>
                    <div class="value">R$ ' . number_format($totalSaidas, 2, ',', '.') . '</div>
                </div>
                <div class="dados-item">
                    <label>Saldo Líquido</label>
                    <div class="value">R$ ' . number_format($saldoFinal, 2, ',', '.') . '</div>
                </div>
                <div class="dados-item">
                    <label>Total em Vendas</label>
                    <div class="value">R$ ' . number_format($vendasTotal, 2, ',', '.') . '</div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>Composição das Entradas</h3>
            <div class="resumo-pagamentos">
                <div class="pagamento-item">
                    <span>Vendas Diretas (PDV)</span>
                    <span>R$ ' . number_format($vendasTotal, 2, ',', '.') . '</span>
                </div>
                <div class="pagamento-item">
                    <span>Outros Lançamentos</span>
                    <span>R$ ' . number_format($outrasEntradasTotal, 2, ',', '.') . '</span>
                </div>
            </div>
        </div>

        <div class="legend">
            <div class="legend-title">Legenda das Colunas:</div>
            <strong>Vendas:</strong> Faturamento do PDV no dia | 
            <strong>Outras Entr.:</strong> Entradas manuais no financeiro | 
            <strong>Saídas:</strong> Total de despesas do dia | 
            <strong>Saldo Dia:</strong> Resultado líquido diário
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th class="text-right">Vendas</th>
                    <th class="text-right">Outras Entr.</th>
                    <th class="text-right">Saídas</th>
                    <th class="text-right">Saldo Dia</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background: #f1f4f9; font-style: italic; font-size: 8px; color: #7f8c8d;">
                    <td>Data do Mov.</td>
                    <td class="text-right">Entradas PDV</td>
                    <td class="text-right">Outros Créditos</td>
                    <td class="text-right">Débitos totais</td>
                    <td class="text-right">Líquido Diário</td>
                </tr>';

    foreach ($fluxo as $dia) {
        if ($dia['total_entradas'] == 0 && $dia['total_saidas'] == 0) continue; // Pula dias sem movimento

        $html .= '<tr>';
        $html .= '<td>' . date('d/m/Y', strtotime($dia['data'])) . '</td>';
        $html .= '<td class="text-right">R$ ' . number_format($dia['vendas'], 2, ',', '.') . '</td>';
        $html .= '<td class="text-right">R$ ' . number_format($dia['outras_entradas'], 2, ',', '.') . '</td>';
        $html .= '<td class="text-right">R$ ' . number_format($dia['total_saidas'], 2, ',', '.') . '</td>';
        $html .= '<td class="text-right" style="font-weight: bold;">R$ ' . number_format($dia['saldo_dia'], 2, ',', '.') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>
        </table>

        <div class="footer">
            <p>Relatório de Fluxo de Caixa - AtendAI</p>
            <p>Data de Emissão: ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    header('Cache-Control: public, max-age=3600');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="fluxo_caixa_' . date('Y-m-d') . '.pdf"');
    $dompdf->stream("fluxo_caixa_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
    exit;
}

?>
