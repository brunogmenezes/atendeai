<?php
include("config.php");
include("funcoes.php");

require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');

$compraId = $_GET['id'] ?? null;

if (!$compraId) {
    echo json_encode(['status' => 'error', 'message' => 'ID da compra não informado.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Buscar dados da compra antes de excluir
    $stmtCompra = $pdo->prepare("SELECT total, conta_id, fornecedor FROM compras WHERE id = :id");
    $stmtCompra->bindValue(':id', $compraId);
    $stmtCompra->execute();
    $compra = $stmtCompra->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        throw new Exception("Compra não encontrada.");
    }

    $valorEstorno = (float)$compra['total'];
    $contaId = $compra['conta_id'];

    // 2. Buscar itens para estornar o estoque e o custo
    $stmtItens = $pdo->prepare("SELECT produto_id, quantidade, preco_custo_anterior FROM itens_compra WHERE compra_id = :compra_id");
    $stmtItens->bindValue(':compra_id', $compraId);
    $stmtItens->execute();
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itens as $item) {
        // Subtrair a quantidade do estoque e VOLTAR O CUSTO ANTERIOR
        $stmtUpdateEstoque = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - :qtd, preco_custo = :custo_ant WHERE id = :id");
        $stmtUpdateEstoque->bindValue(':qtd', $item['quantidade']);
        $stmtUpdateEstoque->bindValue(':custo_ant', $item['preco_custo_anterior']);
        $stmtUpdateEstoque->bindValue(':id', $item['produto_id']);
        $stmtUpdateEstoque->execute();
    }

    // 3. Devolver saldo para as contas financeiras (usando pagamentos_compra)
    $stmtPagamentos = $pdo->prepare("SELECT conta_id, valor FROM pagamentos_compra WHERE compra_id = :compra_id");
    $stmtPagamentos->bindValue(':compra_id', $compraId);
    $stmtPagamentos->execute();
    $pagamentos = $stmtPagamentos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pagamentos as $pag) {
        $stmtUpdateConta = $pdo->prepare("UPDATE contas SET saldo = saldo + :valor, data_atualizacao = NOW() WHERE id = :id");
        $stmtUpdateConta->bindValue(':valor', $pag['valor']);
        $stmtUpdateConta->bindValue(':id', $pag['conta_id']);
        $stmtUpdateConta->execute();
    }

    // 4. Remover lançamento do financeiro
    // Usamos um filtro na descrição para garantir que encontre o registro correto
    $descPattern = "Compra de Estoque (ID: $compraId)%";
    $stmtDeleteFin = $pdo->prepare("DELETE FROM financeiro WHERE (tipo = 2 OR tipo = 1) AND descricao LIKE :desc");
    $stmtDeleteFin->bindValue(':desc', $descPattern);
    $stmtDeleteFin->execute();

    // 5. Excluir a compra (isso apagará itens_compra devido ao ON DELETE CASCADE se configurado, ou fazemos manual)
    // Para garantir, vamos apagar os itens primeiro caso o FK não esteja com cascade
    $stmtDeleteItens = $pdo->prepare("DELETE FROM itens_compra WHERE compra_id = :compra_id");
    $stmtDeleteItens->bindValue(':compra_id', $compraId);
    $stmtDeleteItens->execute();

    $stmtDeleteCompra = $pdo->prepare("DELETE FROM compras WHERE id = :id");
    $stmtDeleteCompra->bindValue(':id', $compraId);
    $stmtDeleteCompra->execute();

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Compra estornada com sucesso! Estoque e saldo financeiro foram revertidos.'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Erro ao estornar compra: ' . $e->getMessage()]);
}
