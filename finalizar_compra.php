<?php
include("config.php");
include("funcoes.php");

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');

// Receber os dados do carrinho via POST
$dados = json_decode(file_get_contents('php://input'), true);

if (empty($dados)) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido.']);
    exit;
}

// Verificar se o usu�rio est� logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

try {
    // Iniciar uma transação
    $pdo->beginTransaction();

    // Validar itens e recalcular totais no servidor (Segurança)
    $totalCalculado = 0;
    $itensProcessados = [];
    
    foreach ($dados['itens'] as $item) {
        $stmtProd = $pdo->prepare("SELECT preco_venda, nome FROM produtos WHERE id = :id");
        $stmtProd->bindValue(':id', $item['id']);
        $stmtProd->execute();
        $produto = $stmtProd->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto) {
            throw new Exception("Produto ID " . $item['id'] . " não encontrado.");
        }
        
        $precoUnitario = (float)$produto['preco_venda'];
        $quantidade = (int)$item['qtd'];
        $subtotal = $precoUnitario * $quantidade;
        $totalCalculado += $subtotal;
        
        $itensProcessados[] = [
            'id' => $item['id'],
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario
        ];
    }

    $descontoPercentual = (float)($dados['desconto'] ?? 0);
    $totalLiquidoCalculado = $totalCalculado * (1 - ($descontoPercentual / 100));
    
    // Validar se o valor pago bate com o total calculado no servidor
    $totalPago = array_sum($dados['paymentAmounts']);
    if (abs($totalPago - $totalLiquidoCalculado) > 0.001) { 
        throw new Exception("O valor total pago (R$ $totalPago) não confere com o total calculado (R$ " . number_format($totalLiquidoCalculado, 2) . ").");
    }

    $tipoAtendimento = $dados['tipo_atendimento'] ?? 'presencial';

    // Inserir na tabela "vendas"
    $stmtVenda = $pdo->prepare("INSERT INTO vendas (total, vendedor, desconto, tipo_atendimento) VALUES (:total, :vendedor, :desconto, :tipo_atendimento) RETURNING id");
    $stmtVenda->bindValue(':total', $totalCalculado); // Salva o total BRUTO no banco, o desconto é uma coluna separada
    $stmtVenda->bindValue(':vendedor', $_SESSION['user_id']);
    $stmtVenda->bindValue(':desconto', $descontoPercentual);
    $stmtVenda->bindValue(':tipo_atendimento', $tipoAtendimento);
    $stmtVenda->execute();
    $vendaId = $stmtVenda->fetch(PDO::FETCH_ASSOC)['id'];

    // Inserir os pagamentos na tabela "pagamentos_venda" e atualizar contas
    foreach ($dados['paymentMethods'] as $index => $paymentMethodId) {
        $paymentAmount = $dados['paymentAmounts'][$index];
        
        // Inserir na tabela de pagamentos da venda
        $stmtPagamento = $pdo->prepare("INSERT INTO pagamentos_venda (venda_id, forma_pagamento_id, valor) VALUES (:venda_id, :forma_pagamento_id, :valor)");
        $stmtPagamento->bindValue(':venda_id', $vendaId);
        $stmtPagamento->bindValue(':forma_pagamento_id', $paymentMethodId);
        $stmtPagamento->bindValue(':valor', $paymentAmount);
        $stmtPagamento->execute();

        // Obter a conta associada ao tipo de pagamento
        $sqlGetFinanceiroId = "SELECT conta FROM tipopagamento WHERE id = :id_tipo_pagamento";
        $stmt = $pdo->prepare($sqlGetFinanceiroId);
        $stmt->bindParam(':id_tipo_pagamento', $paymentMethodId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['conta'])) {
            $conta_do_tipo_pagamento = $result['conta'];
            
            // Atualizar o saldo da conta
            $stmAtualizaConta = $pdo->prepare("UPDATE contas SET saldo = saldo + :valor, data_atualizacao = NOW() WHERE id = :conta_id");
            $stmAtualizaConta->bindValue(':valor', $paymentAmount);
            $stmAtualizaConta->bindValue(':conta_id', $conta_do_tipo_pagamento);
            $stmAtualizaConta->execute();
        }

        // Registrar no financeiro
        $stmtFinanceiro = $pdo->prepare("INSERT INTO financeiro (tipo, descricao, valor) VALUES (1, :descricao, :valor)");
        $stmtFinanceiro->bindValue(':descricao', "Venda id: ".$vendaId." (Parcela ".($index+1).")");
        $stmtFinanceiro->bindValue(':valor', $paymentAmount);
        $stmtFinanceiro->execute();
    }

    // Inserir os itens na tabela "itens_venda" e atualizar estoque
    $stmtItem = $pdo->prepare("INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (:venda_id, :produto_id, :quantidade, :preco_unitario)");
    $stmEstoque = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - :quantidadevenda WHERE id = :produto_id");

    foreach ($itensProcessados as $item) {
        $stmEstoque->bindValue(':quantidadevenda', $item['quantidade']);
        $stmEstoque->bindValue(':produto_id', $item['id']);
        $stmEstoque->execute();

        $stmtItem->bindValue(':venda_id', $vendaId);
        $stmtItem->bindValue(':produto_id', $item['id']);
        $stmtItem->bindValue(':quantidade', $item['quantidade']);
        $stmtItem->bindValue(':preco_unitario', $item['preco_unitario']);
        $stmtItem->execute();
    }

    // Commit da transa��o
    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Venda finalizada com sucesso',
        'venda_id' => $vendaId
    ]);
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Erro ao finalizar a venda: ' . $e->getMessage()]);
}