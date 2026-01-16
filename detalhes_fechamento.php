<?php
include 'config.php';
include 'funcoes.php';

$fechamento_id = $_GET['id'] ?? 0;

$query = "
SELECT 
    fech.id,
    fech.dia_fechamento,
    fech.saldo as saldo_total,
    fech.entrada,
    fech.saida,
    fech.usuario,
    fech.created_at,
    fechcont.id_conta,
    fechcont.saldo as saldo_conta,
    cont.nome as nome_conta,
    cont.tipo as tipo_conta
FROM 
    fechamentos fech
INNER JOIN 
    fechamentos_contas fechcont ON fech.id = fechcont.id_fechamento
INNER JOIN 
    contas cont ON fechcont.id_conta = cont.id
WHERE 
    fech.id = :fechamento_id
ORDER BY 
    cont.nome ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([':fechamento_id' => $fechamento_id]);
$detalhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($detalhes) {
    $fechamento = $detalhes[0];
    
    // Buscar totais por tipo de pagamento usando a data do fechamento
    $totaisPorPagamento = buscarTotalVendasPorTipoPagamento(
        date('d', strtotime($fechamento['dia_fechamento'])),
        date('m', strtotime($fechamento['dia_fechamento'])),
        date('Y', strtotime($fechamento['dia_fechamento']))
    );
    ?>
    
    <div class="row">
        <!-- Informações principais do fechamento -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informações do Fechamento</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>ID:</strong> #<?= $fechamento['id'] ?></p>
                            <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($fechamento['dia_fechamento'])) ?></p>
                            <p><strong>Usuário:</strong> <?= $fechamento['usuario'] ?></p>
                        </div>
                        <div class="col-6">
                            <p><strong>Horário:</strong> <?= date('H:i:s', strtotime($fechamento['created_at'])) ?></p>
                            <p><strong>Entradas:</strong><br> <span class="text-success">R$ <?= number_format($fechamento['entrada'], 2, ',', '.') ?></span></p>
                            <p><strong>Saídas:</strong><br> <span class="text-danger">R$ <?= number_format($fechamento['saida'], 2, ',', '.') ?></span></p>
                        </div>
                    </div>
                    <div class="text-center mt-3 p-2 bg-light rounded">
                        <h5 class="mb-0"><strong>Saldo Total:</strong> <span class="text-primary">R$ <?= number_format($fechamento['saldo_total'], 2, ',', '.') ?></span></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totais por tipo de pagamento -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-credit-card"></i> Vendas por Tipo de Pagamento</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo de Pagamento</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalVendas = 0;
                                foreach ($totaisPorPagamento as $pagamento): 
                                    $totalVendas += $pagamento['total_vendas'];
                                ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-money-bill-wave me-2 text-muted"></i>
                                        <?= htmlspecialchars($pagamento['tipo_pagamento']) ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        R$ <?= number_format($pagamento['total_vendas'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($totaisPorPagamento)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle me-2"></i>Nenhuma venda encontrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($totaisPorPagamento)): ?>
                            <tfoot class="table-primary">
                                <tr>
                                    <th class="text-end">Total:</th>
                                    <th class="text-end">R$ <?= number_format($totalVendas, 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contas do fechamento -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-wallet"></i> Contas do Fechamento</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Conta</th>
                            <th>Tipo</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalContas = 0;
                        foreach ($detalhes as $conta): 
                            $totalContas += $conta['saldo_conta'];
                        ?>
                        <tr>
                            <td>
                                <i class="fas fa-university me-2 text-muted"></i>
                                <?= $conta['nome_conta'] ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $conta['tipo_conta'] ?></span>
                            </td>
                            <td class="text-end fw-bold">
                                R$ <?= number_format($conta['saldo_conta'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-primary">
                        <tr>
                            <th colspan="2" class="text-end">Total das Contas:</th>
                            <th class="text-end">R$ <?= number_format($totalContas, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Resumo final -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="alert alert-light border">
                <h6 class="alert-heading"><i class="fas fa-chart-bar"></i> Resumo do Dia</h6>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Total de Contas:</span>
                    <strong><?= count($detalhes) ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total em Vendas:</span>
                    <strong class="text-success">R$ <?= number_format($totalVendas, 2, ',', '.') ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Saldo nas Contas:</span>
                    <strong class="text-primary">R$ <?= number_format($totalContas, 2, ',', '.') ?></strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-<?= $fechamento['saldo_total'] >= 0 ? 'success' : 'warning' ?>">
                <h6 class="alert-heading"><i class="fas fa-balance-scale"></i> Resultado Final</h6>
                <hr>
                <h4 class="text-center mb-0">
                    R$ <?= number_format($fechamento['saldo_total'], 2, ',', '.') ?>
                </h4>
                <p class="text-center mb-0 small">
                    <?= $fechamento['saldo_total'] >= 0 ? 'Positivo' : 'Negativo' ?>
                </p>
            </div>
        </div>
    </div>

    <?php
} else {
    echo '
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
        <h5>Fechamento não encontrado</h5>
        <p class="mb-0">O fechamento solicitado não foi localizado em nosso sistema.</p>
    </div>';
}
?>