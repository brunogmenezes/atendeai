<?php
    ini_set('memory_limit', '512M');
    require_once 'dompdf/dompdf/vendor/autoload.php';
    include("config.php");
    include("funcoes.php");

    use Dompdf\Dompdf;
    use Dompdf\Options;

    $startData = $_GET['start'] ?? null;
    $endData = $_GET['end'] ?? null;

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($options);

    $html = '
            <!DOCTYPE html>
                <html>
                    <head>
                    <meta charset="UTF-8">
                    <title>Relatório</title>
                    <style>
                        body
                        {
                            font-family: Arial, sans-serif;
                            margin: 40px;
                            color: #333;
                        }
                        h1
                        {
                            text-align: center;
                            margin-bottom: 0;
                            font-size: 24px;
                            color: #2c3e50;
                        }
                        h2
                        {
                            text-align: center;
                            font-size: 14px;
                            margin-top: 5px;
                            color: #7f8c8d;
                        }
                        .info
                        {
                            margin-top: 20px;
                            padding: 10px 20px;
                            border: 1px solid #ddd;
                            background-color: #f4f6f7;
                            border-radius: 5px;
                        }
                        .info p
                        {
                            margin: 4px 0;
                            font-size: 13px;
                        }
                        table
                        {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 25px;
                            font-size: 12px;
                        }
                        thead
                        {
                            background-color: #3498db;
                            color: white;
                        }
                        th, td
                        {
                            border: 1px solid #ccc;
                            padding: 8px;
                            text-align: left;
                        }
                        tbody tr:nth-child(even)
                        {
                            background-color: #f9f9f9;
                        }
                        .estornado
                        {
                            font-style: italic;
                            text-decoration: line-through;
                            background-color: #f8d7da !important;
                        }
                        .right
                        {
                            text-align: right;
                        }
                        .footer
                        {
                            margin-top: 30px;
                            text-align: center;
                            font-size: 10px;
                            color: #999;
                        }
                        .resumo-pagamentos
                        {
                            margin-top: 20px;
                            padding: 15px;
                            background-color: #f8f9fa;
                            border-radius: 5px;
                            border: 1px solid #dee2e6;
                        }

                        .resumo-pagamentos h3
                        {
                            margin-top: 0;
                            color: #495057;
                            border-bottom: 1px solid #dee2e6;
                            padding-bottom: 8px;
                        }

                        .dados-pagamentos
                        {
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                            gap: 10px;
                        }

                        .dados-pagamentos p
                        {
                            margin: 5px 0;
                            padding: 8px;
                            background-color: white;
                            border-radius: 4px;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        }
                            .resumo-pagamentos {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .pagamento-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
                    </style>
                </head>
                ';

                if($_GET['tipo']=='estoque')
                {
                    $totalItensEstoque = buscarTotalemEstoqueRelatorio();
                    $mediaCustoItemEstoque = BuscarCustoMedioProdutos('produtos') ?? 0;
                    $mediaVendaItemEstoque = buscarMediaVendaProdutosRelatorio();
                    $lucroMedioItemEstoque = BuscarLucroMedioProdutos('produtos', 'salario') ?? 0;
                    $valoresEstoque = calcularValorTotalEstoqueRelatorio();

                $html .='
                <body>
                    <h1>Relatório de Estoque</h1>
                    <h2>Emitido em ' . date('d/m/Y H:i') . '</h2>

                    <div class="info">
                        <div class="resumo-estoque">
                            <h3>Resumo do Estoque</h3>
                            <div class="dados-estoque">
                                <p><strong>Total de Itens no Estoque:</strong> ' . number_format($totalItensEstoque, 0, ',', '.') . '</p>
                                <p><strong>Média Preço Custo por Item:</strong> R$ ' . number_format($mediaCustoItemEstoque, 2, ',', '.') . '</p>
                                <p><strong>Média Preço Venda por Item:</strong> R$ ' . number_format($mediaVendaItemEstoque, 2, ',', '.') . '</p>
                                <p><strong>Média Lucro por Item:</strong> R$ ' . number_format($lucroMedioItemEstoque, 2, ',', '.') . ' ('. number_format(($mediaVendaItemEstoque - $mediaCustoItemEstoque) / $mediaCustoItemEstoque * 100, 2, ',', '.') .'%)</p>
                                <p><strong>Valor total em Estoque (Custo):</strong> R$ ' . number_format($valoresEstoque['total_custo'], 2, ',', '.') . '</p>
                                <p><strong>Valor total em Estoque (Venda):</strong> R$ ' . number_format($valoresEstoque['total_venda'], 2, ',', '.') . '</p>
                                <p><strong>Lucro Potencial Total:</strong> R$ ' . number_format(($valoresEstoque['total_venda'] - $valoresEstoque['total_custo']), 2, ',', '.') . ' ('. number_format(($valoresEstoque['total_venda'] - $valoresEstoque['total_custo']) / $valoresEstoque['total_custo'] * 100, 2, ',', '.') .')%</p>
                            </div>
                        </div>

    <div class="tabela-vendas">
        <h3>Últimas Vendas</h3>
        <table class="tabela-dados">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>QNT em Estoque</th>
                    <th>Preço de Custo</th>
                    <th>Preço de Venda</th>
                </tr>
            </thead>
            <tbody>';

            $produtos = buscarProdutosRelatorios();
foreach ($produtos as $produto) {
    $style = $produto['estornado'] ? 'class="estornado"' : '';

    $html .= '<tr ' . $style . '>';
    $html .= '<td>' . $produto['id'] . '</td>';
    $html .= '<td>' . $produto['nome'] . '</td>';
    $html .= '<td>' . $produto['quantidade'] . '</td>';
    $html .= '<td>' . number_format($produto['preco_custo'], 2, ',', '.') . '</td>';
    $html .= '<td>' . number_format($produto['preco_venda'], 2, ',', '.') . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody>
    </table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_estoque_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
}

if($_GET['tipo']=='financeiro-mensal')
{
$numeroVendasPeriodo = contarNumeroVendasPeriodo($startData, $endData);
$totalnoperiodo = buscarTotalVendasnoPeriodoRelatorio($startData, $endData);
$totaisPorPagamento = buscarTotalVendasPorTipoPagamentoPeriodo($startData, $endData);
$vendas = buscarTabelaVendasRelatorio('vendas', '', '', 0, 0, 'DESC', $startData, $endData);
$lucroMedio = BuscarLucroMedioProdutos('produtos', 'salario') ?? 0;


$html .='
<body>
    <h1>Relatório de Vendas no período</h1>
    <h2>Emitido em ' . date('d/m/Y H:i') . '</h2>

    <div class="info">
        <p><strong>Período:</strong> ';

$html .= 'De ';
$html .= date('d/m/Y', strtotime($startData));
$html .= ' até ';
$html .= date('d/m/Y', strtotime($endData));

$html .= '</p>
        <p><strong>Número de Vendas:</strong> ' . $numeroVendasPeriodo . '</p>
        <p><strong>Total Faturamento Bruto:</strong> R$ ' . number_format($totalnoperiodo, 2, ',', '.') . '</p>
        <p><strong>Lucro médio geral:</strong> R$ ' . number_format($lucroMedio, 2, ',', '.') . '</p>
        <div class="resumo-pagamentos">
            <h3>Distribuição por Tipo de Pagamento</h3>';

// Adiciona os tipos de pagamento
if (!empty($totaisPorPagamento)) {
    foreach ($totaisPorPagamento as $pagamento) {
        $html .= '<div class="pagamento-item">
            <span>' . htmlspecialchars($pagamento['tipo_pagamento']) . '</span>
            <span>R$ ' . number_format($pagamento['total_vendas'], 2, ',', '.') . '</span>
        </div>';
    }
} else {
    $html .= '<p>Nenhum pagamento registrado neste período</p>';
}

$html .= '</div>
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
}
?>
