<?php

    require_once 'config.php';
    require_once 'funcoes.php';

    require_once 'auth.php';
    verificarSessao();

    require 'api_pix/vendor/autoload.php';

    use \App\Pix\Payload;
    use Mpdf\QrCode\QrCode;
    use Mpdf\QrCode\Output;

    // Função auxiliar para tratamento seguro de strings
    function safe_print($value, $default = '')
    {
        return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
    }

    // Receber os dados da venda via GET ou POST
    $venda_id = $_GET['id'] ?? null;

    if (!$venda_id)
    {
        die("ID da venda não especificado");
    }

    // Buscar dados da empresa
    $dadosEmpresa = buscarDadosEmpresa();

    // Buscar dados da venda
    $stmtVenda = $pdo->prepare("SELECT vnd.*, u.username as username FROM vendas vnd JOIN usuarios u ON vnd.vendedor = u.id WHERE vnd.id = ?");
    $stmtVenda->execute([$venda_id]);
    $venda = $stmtVenda->fetch(PDO::FETCH_ASSOC);

    if (!$venda)
    {
        die("Venda não encontrada");
    }

    // Buscar itens da venda
    $stmtItens = $pdo->prepare("
        SELECT iv.*, p.nome as produto_nome 
        FROM itens_venda iv
        JOIN produtos p ON iv.produto_id = p.id
        WHERE iv.venda_id = ?
    ");
    $stmtItens->execute([$venda_id]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    // Buscar formas de pagamento (agora suporta múltiplos pagamentos)
    $stmtPagamentos = $pdo->prepare("
        SELECT pv.valor, tp.nome as forma_pagamento 
        FROM pagamentos_venda pv
        JOIN tipopagamento tp ON pv.forma_pagamento_id = tp.id
        WHERE pv.venda_id = ?
    ");
    $stmtPagamentos->execute([$venda_id]);
    $pagamentos = $stmtPagamentos->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totais
    $subtotal = $venda['total'];
    $desconto = $venda['desconto'] ?? 0;
    $total = $subtotal * (1 - ($desconto / 100));
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Comprovante de Venda #<?= $venda_id ?></title>
        <style>
            /* Estilos otimizados para impressora térmica */
            body
            {
                font-family: 'Arial', sans-serif;
                font-size: 15px;
                font-weight: normal;
                padding: 2mm; 
                width: 80mm; 
                margin: 0 auto;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page 
            {
                size: 80mm auto;
                margin: 0;
                margin-bottom: 5mm;
            }
            .header
            {
                text-align: center; 
                margin-bottom: 3px; 
                padding-bottom: 3px;
                border-bottom: 1px dashed #000;
            }
            .title
            {
                font-size: 18px; 
                font-weight: bold;
                text-transform: uppercase;
            }
            .dadosEmpresa
            {
                font-size: 10px; 
                font-weight: bold;
                text-transform: uppercase;
            }
            .dadosPagamento
            {
                font-size: 10px; 
                font-weight: bold;
                text-transform: uppercase;
            }
            .info
            {
                margin: 5px 0; 
                font-size: 9px;
                line-height: 1.3;
            }
            .table
            {
                width: 100%; 
                border-collapse: collapse; 
                margin: 5px 0;
                font-size: 9px;
            }
            .table th
            {
                text-align: left; 
                padding: 1px 0; 
                border-bottom: 1px dashed #000; 
            }
            .table td
            {
                padding: 1px 0; 
                border-bottom: 1px dashed #ccc; 
                vertical-align: top;
            }
            .text-right
            {
                text-align: right;
            }
            .text-center
            {
                text-align: center;
            }
            .footer
            {
                margin-top: 8px; 
                text-align: center; 
                font-size: 9px;
                border-top: 1px dashed #000;
                padding-top: 5px;
            }
            .qrcode
            {
                margin-top: 8px; 
		font-weight: normal;
                text-align: center; 
                font-size: 6px;
                border-top: 1px dashed #000;
                padding-top: 5px;
            }
            .cut
            {
                margin-top: 10px;
                text-align: center;
                font-size: 12px;
                display: block;
                page-break-after: always;
                break-after: page;
                position: relative;
                top: -5mm;
            }
            .pagamentos
            {
                font-size: 10px; 
                font-weight: bold;
                text-transform: uppercase;
                margin-top: 5px;
                border-top: 1px dashed #000;
                padding-top: 5px;
            }
            .pagamento-item
            {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
            }
            @media print
            {
                .no-print
                {
                    display: none !important;
                }
                body
                {
                    padding: 0; margin: 0;
                }
                .cut
                {
                    display: block;
                    page-break-after: always;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title"><?= safe_print($dadosEmpresa['nome'] ?? 'MINHA EMPRESA') ?></div>
            <div class="dadosEmpresa"><?= safe_print($dadosEmpresa['endereco'] ?? 'ENDEREÇO NÃO CADASTRADO') ?></div>
            <div class="dadosEmpresa">CNPJ: <?= safe_print($dadosEmpresa['cnpj'] ?? '00.000.000/0000-00') ?></div>
            <?php if (!empty($dadosEmpresa['telefone'])): ?>
                <div class="dadosEmpresa">TEL: <?= safe_print($dadosEmpresa['telefone']) ?></div>
            <?php endif; ?>
        </div>
    
        <div class="info">
            <div><strong>CUPOM NÃO FISCAL</strong></div>
            <div><strong>DATA:</strong> <?= date('d/m/Y H:i:s', strtotime($venda['data_venda'] ?? 'now')) ?></div>
            <div><strong>ATENDENTE:</strong> <?= safe_print($venda['username'] ?? 'OPERADOR') ?></div>
            <div><strong>VENDA #:</strong> <?= str_pad($venda_id, 6, '0', STR_PAD_LEFT) ?></div>
        </div>
    
        <table class="table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>QTD</th>
                    <th class="text-right">VALOR</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= safe_print(mb_strtoupper($item['produto_nome'])) ?></td>
                        <td><?= $item['quantidade'] ?> UN</td>
                        <td class="text-right">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                        <td class="text-right">R$ <?= number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    
        <div style="margin: 8px 0; font-size: 10px;">
            <div><strong>SUBTOTAL:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?></div>
            <?php if ($desconto > 0): ?>
                <div><strong>DESCONTO (<?= $desconto ?>%):</strong> R$ <?= number_format($subtotal * $desconto / 100, 2, ',', '.') ?></div>
            <?php endif; ?>
            <div style="font-weight: bold; border-top: 1px dashed #000; padding-top: 2px;">
                <strong>TOTAL:</strong> R$ <?= number_format($total, 2, ',', '.') ?>
            </div>
        </div>
    
        <!-- Seção de pagamentos -->
        <div class="pagamentos">
            <div style="font-weight: bold; margin-bottom: 3px;">FORMAS DE PAGAMENTO:</div>
            <?php foreach ($pagamentos as $pagamento): ?>
                <div class="pagamento-item">
                    <span><?= safe_print($pagamento['forma_pagamento']) ?>:</span>
                    <span>
                        R$ <?= number_format($pagamento['valor'], 2, ',', '.') ?>
                        
                    </span>
                </div>
                <?php
                if($pagamento['forma_pagamento']=='PIX')
                {
                ?>
                    <div class="qrcode">
                        <?php
                        
                        //INSTANCIA PRINCIPAL DO PAYLOAD PIX
                        $obPayload = (new Payload)->setPixKey($dadosEmpresa['chave_pix'])
                                                    ->setDescription('#Pagamento do pedido '.$venda_id.'#')
                                                    ->setMerchantName('BIJU20')
                                                    ->setMerchantCity('IMPERATRIZ')
                                                    ->setAmount($pagamento['valor'])
                                                    ->setTxid('pedido'.$venda_id.'');

                        //CODIGO DE PAGAMENTO PIX
                        $payloadQrCode = $obPayload->getPayload();

                        //QR CODE
                        $obQrCode = new QrCode($payloadQrCode);

                        //IMAGEM DO QRCODE
                        $image = (new Output\Png)->output($obQrCode,130);
                        ?>
                        <img src="data:image/png;base64, <?=base64_encode($image)?>">
                        <br>
                        <strong><?=$payloadQrCode?></strong>
                        <br>
                    </div>
                    <br>
                <?php
                }
                ?>
            <?php endforeach; ?>
            
            <?php if (count($pagamentos) > 1): ?>
                <div class="pagamento-item" style="font-weight: bold; border-top: 1px dashed #000; padding-top: 2px;">
                    <span>TOTAL PAGO:</span>
                    <span>R$ <?= number_format(array_sum(array_column($pagamentos, 'valor')), 2, ',', '.') ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <div>********************************</div>
            <div>OBRIGADO PELA PREFERÊNCIA!</div>
            <div>VOLTE SEMPRE!</div>
            <div>********************************</div>
        </div>
    
        <div class="cut">
            ----------------------------
        </div>
    
        <div class="no-print" style="margin-top: 15px; text-align: center;">
            <button onclick="imprimir()" style="padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
                IMPRIMIR CUPOM
            </button>
            <button onclick="window.close()" style="padding: 5px 15px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; margin-left: 10px;">
                FECHAR JANELA
            </button>
        </div>
    
        <script>
            // Função otimizada para impressão térmica
            function imprimirTermica()
            {
                // Configurações específicas para impressão térmica
                const printSettings = `
                    <style>
                        @page
                        {
                            size: 80mm auto; 
                            margin: 0;
                            margin-bottom: 5mm;
                        }
                        body
                        {
                            width: 80mm !important; 
                            margin: 0 !important; 
                            padding: 2mm !important;
                            font-size: 10px !important;
                            padding-bottom: 10mm;
                        }
                        .no-print
                        {
                            display: none !important;
                        }
                        .cut
                        {
                            display: block;
                            page-break-after: always;
                            break-after: page;
                            margin-top: 15px;
                        }
                    </style>
                `;
            
                // Abrir janela de impressão
                const printWindow = window.open('', '_blank');
                printWindow.document.write(printSettings + document.body.innerHTML);
                printWindow.document.close();
            
                // Esperar o conteúdo carregar antes de imprimir
                printWindow.onload = function()
                {
                    setTimeout(() =>
                    {
                        printWindow.print();
                        printWindow.close();
                    }, 300);
                };
            }

            // Impressão automática ao carregar
            setTimeout(() =>
            {
                window.print();
            }, 500);

            function imprimir()
            {
                setTimeout(() =>
                {
                    window.print();
                }, 500);
            }
        </script>
    </body>
</html>
