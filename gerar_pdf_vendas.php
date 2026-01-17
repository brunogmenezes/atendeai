<?php
require_once 'dompdf/dompdf/vendor/autoload.php';
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

use Dompdf\Dompdf;
use Dompdf\Options;

// Validação de parâmetros
$dia = isset($_GET['dia']) && is_numeric($_GET['dia']) ? (int)$_GET['dia'] : null;
$mes = isset($_GET['mes']) && is_numeric($_GET['mes']) ? (int)$_GET['mes'] : null;
$ano = isset($_GET['ano']) && is_numeric($_GET['ano']) ? (int)$_GET['ano'] : null;

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Helvetica');
$options->set('dpi', 96);

$dompdf = new Dompdf($options);

// Buscar dados com validação
$numeroVendasPeriodo = contarNumeroPorVendas($dia, $mes, $ano) ?? 0;
$totalnoperiodo = buscarTotalVendasnoPeriodo($dia, $mes, $ano) ?? 0;
$vendas = buscarTabelaVendas('vendas', '', '', 0, 0, 'DESC', $dia, $mes, $ano) ?? [];

// Formatar período
$periodo = '';
if ($dia) $periodo .= str_pad($dia, 2, '0', STR_PAD_LEFT) . '/';
if ($mes) $periodo .= str_pad($mes, 2, '0', STR_PAD_LEFT) . '/';
if ($ano) $periodo .= $ano;
if (empty($periodo)) $periodo = 'Todos os períodos';

$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #2c3e50;
            line-height: 1.6;
            background-color: #fff;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }
        .header p {
            font-size: 13px;
            opacity: 0.9;
        }
        .container {
            max-width: 100%;
            padding: 0;
        }
        .info-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .info-card.highlight {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        }
        .info-card label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .info-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
        }
        .info-card.highlight .value {
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 6px;
            overflow: hidden;
        }
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 10px 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        tbody tr {
            transition: background-color 0.2s;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tbody tr:hover {
            background-color: #f0f2f5;
        }
        .estornado {
            opacity: 0.6;
            font-style: italic;
            text-decoration: line-through;
            background-color: #ffebee !important;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
        }
        .summary {
            background: #f8f9fa;
            padding: 15px;
            margin-top: 20px;
            border-radius: 6px;
            text-align: right;
            font-weight: 600;
            border-top: 2px solid #667eea;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .header {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Relatório de Vendas</h1>
            <p>Emitido em ' . date('d/m/Y \à\s H:i:s') . '</p>
        </div>

        <div class="info-box">
            <div class="info-card">
                <label>Período</label>
                <div class="value">' . $periodo . '</div>
            </div>
            <div class="info-card highlight">
                <label>Total de Vendas</label>
                <div class="value">R$ ' . number_format($totalnoperiodo, 2, ',', '.') . '</div>
            </div>
        </div>

        <div class="info-box">
            <div class="info-card">
                <label>Quantidade de Transações</label>
                <div class="value">' . $numeroVendasPeriodo . '</div>
            </div>
            <div class="info-card">
                <label>Ticket Médio</label>
                <div class="value">R$ ' . ($numeroVendasPeriodo > 0 ? number_format($totalnoperiodo / $numeroVendasPeriodo, 2, ',', '.') : '0,00') . '</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Pagamento</th>
                    <th>Vendedor</th>
                    <th>Data/Hora</th>
                </tr>
            </thead>
            <tbody>';

// Renderizar linhas de vendas
if (!empty($vendas)) {
    foreach ($vendas as $venda) {
        $style = $venda['estornado'] ? 'class="estornado"' : '';
        $html .= '<tr ' . $style . '>';
        $html .= '<td class="text-center">#' . htmlspecialchars($venda['id']) . '</td>';
        $html .= '<td class="text-right"><strong>R$ ' . number_format($venda['total'], 2, ',', '.') . '</strong></td>';
        $html .= '<td>' . htmlspecialchars($venda['tipos_pagamento']) . '</td>';
        $html .= '<td>' . htmlspecialchars($venda['usuariovendedor']) . '</td>';
        $html .= '<td>' . date('d/m/Y H:i', strtotime($venda['data_venda'])) . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="5" class="text-center"><em>Nenhuma venda encontrada para o período selecionado</em></td></tr>';
}

$html .= '</tbody>
            <tfoot class="summary">
                <tr>
                    <td colspan="2" class="text-right"><strong>TOTAL:</strong></td>
                    <td colspan="3" class="text-right"><strong>R$ ' . number_format($totalnoperiodo, 2, ',', '.') . '</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Relatório gerado automaticamente pelo sistema AtendAI</p>
            <p>Validade: Este relatório é válido apenas como comprovante de transações registradas no sistema</p>
        </div>
    </div>
</body>
</html>';

// Liberar memória
unset($vendas);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Headers para melhor performance
header('Cache-Control: public, max-age=3600');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="relatorio_vendas_' . date('Y-m-d') . '.pdf"');

$dompdf->stream("relatorio_vendas_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
?>
