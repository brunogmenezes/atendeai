<?php
include("config.php");
include("funcoes.php");

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');

// Receber os dados da compra via POST
$dados = json_decode(file_get_contents('php://input'), true);

if (empty($dados) || empty($dados['itens'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido ou itens vazios.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $fornecedor = $dados['fornecedor'] ?? 'Não informado';
    $pagamentos = $dados['pagamentos'] ?? [];
    $contaIdPrincipal = $pagamentos[0]['conta_id'] ?? null; // Usa a primeira conta como principal
    $dataCompra = $dados['data_compra'] ?? date('Y-m-d H:i:s');
    $totalGeral = (float)$dados['total_geral'];

    // 1. Inserir na tabela de compras
    $stmtCompra = $pdo->prepare("INSERT INTO compras (fornecedor, data_compra, total, usuario_id, conta_id) VALUES (:fornecedor, :data_compra, :total, :usuario_id, :conta_id) RETURNING id");
    $stmtCompra->bindValue(':fornecedor', $fornecedor);
    $stmtCompra->bindValue(':data_compra', $dataCompra);
    $stmtCompra->bindValue(':total', $totalGeral);
    $stmtCompra->bindValue(':usuario_id', $_SESSION['user_id']);
    $stmtCompra->bindValue(':conta_id', $contaIdPrincipal);
    $stmtCompra->execute();
    $compraId = $stmtCompra->fetch(PDO::FETCH_ASSOC)['id'];

    // 2. Processar itens, estoque e média de custo
    foreach ($dados['itens'] as $item) {
        $produtoId = $item['id'];
        $qtdComprada = (int)$item['qtd'];
        $custoDiluido = (float)$item['custo_diluido'];
        $custoBase = (float)$item['custo'];

        // Buscar dados atuais do produto para média ponderada
        $stmtProd = $pdo->prepare("SELECT quantidade, preco_custo FROM produtos WHERE id = :id");
        $stmtProd->bindValue(':id', $produtoId);
        $stmtProd->execute();
        $produtoAtual = $stmtProd->fetch(PDO::FETCH_ASSOC);

        if ($produtoAtual) {
            $qtdAtual = (int)$produtoAtual['quantidade'];
            $custoAtual = (float)$produtoAtual['preco_custo'];

            // Cálculo da Média Ponderada
            $qtdBaseParaMedia = max(0, $qtdAtual);
            $novaMediaCusto = (($qtdBaseParaMedia * $custoAtual) + ($qtdComprada * $custoDiluido)) / ($qtdBaseParaMedia + $qtdComprada);

            // Atualizar Produto: Novo Estoque e Novo Custo Médio
            $stmtUpdateProd = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + :qtd_nova, preco_custo = :novo_custo WHERE id = :id");
            $stmtUpdateProd->bindValue(':qtd_nova', $qtdComprada);
            $stmtUpdateProd->bindValue(':novo_custo', round($novaMediaCusto, 4));
            $stmtUpdateProd->bindValue(':id', $produtoId);
            $stmtUpdateProd->execute();
        }

        // Inserir item da compra (GRAVANDO O CUSTO ANTERIOR PARA POSSÍVEL ESTORNO)
        $stmtItem = $pdo->prepare("INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_custo, subtotal, preco_custo_anterior) VALUES (:compra_id, :produto_id, :quantidade, :preco_custo, :subtotal, :preco_custo_anterior)");
        $stmtItem->bindValue(':compra_id', $compraId);
        $stmtItem->bindValue(':produto_id', $produtoId);
        $stmtItem->bindValue(':quantidade', $qtdComprada);
        $stmtItem->bindValue(':preco_custo', $custoDiluido);
        $stmtItem->bindValue(':subtotal', $qtdComprada * $custoDiluido);
        $stmtItem->bindValue(':preco_custo_anterior', $custoAtual ?? 0);
        $stmtItem->execute();
    }

    // 3. Registrar Pagamentos e Saídas no Financeiro
    foreach ($pagamentos as $index => $pag) {
        $contaIdPag = $pag['conta_id'];
        $valorPag = (float)$pag['valor'];

        // a) Registrar na tabela pagamentos_compra
        $stmtPagComp = $pdo->prepare("INSERT INTO pagamentos_compra (compra_id, conta_id, valor) VALUES (:compra_id, :conta_id, :valor)");
        $stmtPagComp->bindValue(':compra_id', $compraId);
        $stmtPagComp->bindValue(':conta_id', $contaIdPag);
        $stmtPagComp->bindValue(':valor', $valorPag);
        $stmtPagComp->execute();

        // b) Registrar Saída no Financeiro
        $stmtFin = $pdo->prepare("INSERT INTO financeiro (tipo, descricao, valor, conta, data_lancamento) VALUES (2, :descricao, :valor, :conta, :data)");
        $stmtFin->bindValue(':descricao', "Compra de Estoque (ID: $compraId) - Fornecedor: $fornecedor (Parcela " . ($index + 1) . ")");
        $stmtFin->bindValue(':valor', $valorPag);
        $stmtFin->bindValue(':conta', $contaIdPag);
        $stmtFin->bindValue(':data', $dataCompra);
        $stmtFin->execute();

        // c) Atualizar Saldo da Conta
        $stmtConta = $pdo->prepare("UPDATE contas SET saldo = saldo - :valor, data_atualizacao = NOW() WHERE id = :conta_id");
        $stmtConta->bindValue(':valor', $valorPag);
        $stmtConta->bindValue(':conta_id', $contaIdPag);
        $stmtConta->execute();
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Compra registrada, estoque atualizado e média de custo recalculada com sucesso!'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Erro ao processar compra: ' . $e->getMessage()]);
}
