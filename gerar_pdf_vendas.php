<?php
require_once 'dompdf/dompdf/vendor/autoload.php';
include("config.php");
include("funcoes.php");

require_once 'auth.php';
verificarSessao();

use Dompdf\Dompdf;
use Dompdf\Options;

$dia = $_GET['dia'] ?? null;
$mes = $_GET['mes'] ?? null;
$ano = $_GET['ano'] ?? null;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

$numeroVendasPeriodo = contarNumeroPorVendas($dia, $mes, $ano);
$totalnoperiodo = buscarTotalVendasnoPeriodo($dia, $mes, $ano);
$vendas = buscarTabelaVendas('vendas', '', '', 0, 0, 'DESC', $dia, $mes, $ano);

$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        h2 {
            text-align: center;
            font-size: 14px;
            margin-top: 5px;
            color: #7f8c8d;
        }
        .info {
            margin-top: 20px;
            padding: 10px 20px;
            border: 1px solid #ddd;
            background-color: #f4f6f7;
            border-radius: 5px;
        }
        .info p {
            margin: 4px 0;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 12px;
        }
        thead {
            background-color: #3498db;
            color: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .estornado {
            font-style: italic;
            text-decoration: line-through;
            background-color: #f8d7da !important;
        }
        .right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>Relatório de Vendas</h1>
    <h2>Emitido em ' . date('d/m/Y H:i') . '</h2>

    <div class="info">
        <p><strong>Período:</strong> ';

if ($dia) $html .= str_pad($dia, 2, '0', STR_PAD_LEFT) . '/';
if ($mes) $html .= str_pad($mes, 2, '0', STR_PAD_LEFT) . '/';
if ($ano) $html .= $ano;

$html .= '</p>
        <p><strong>Número de Vendas:</strong> ' . $numeroVendasPeriodo . '</p>
        <p><strong>Total de Vendas:</strong> R$ ' . number_format($totalnoperiodo, 2, ',', '.') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Total</th>
                <th>Tipo de Pagamento</th>
                <th>Vendedor</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>';

foreach ($vendas as $venda) {
    $style = $venda['estornado'] ? 'class="estornado"' : '';

    $html .= '<tr ' . $style . '>';
    $html .= '<td>' . $venda['id'] . '</td>';
    $html .= '<td>R$ ' . number_format($venda['total'], 2, ',', '.') . '</td>';
    $html .= '<td>' . htmlspecialchars($venda['tipos_pagamento']) . '</td>';
    $html .= '<td>' . htmlspecialchars($venda['usuariovendedor']) . '</td>';
    $html .= '<td>' . date('d/m/Y H:i:s', strtotime($venda['data_venda'])) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody>
    </table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_vendas_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
?>
