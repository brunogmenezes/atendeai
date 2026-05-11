<?php
    include("config.php");
    include("funcoes.php");
    require_once 'auth.php';
    verificarSessao();
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Fechamentos de Caixa</h3>
            <h6 class="op-7 mb-2">Gerencie e visualize o histórico de fechamentos do sistema</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addRowModal">
                <i class="fa fa-plus me-2"></i>
                Novo Fechamento
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Histórico de Fechamentos</div>
                        <div class="card-tools">
                            <button class="btn btn-label-info btn-round btn-sm me-2">
                                <span class="btn-label">
                                    <i class="fa fa-print"></i>
                                </span>
                                Imprimir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="add-row" class="display table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 80px">ID</th>
                                    <th>Usuário</th>
                                    <th>Entradas</th>
                                    <th>Saídas</th>
                                    <th>Saldo Total</th>
                                    <th>Data</th>
                                    <th style="width: 100px" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $pagina = $_GET['pagina'] ?? 1;
                                    $limite = 10;
                                    $offset = ($pagina - 1) * $limite;
                                    
                                    $fechamentos = buscarFechamentos($limite, $offset);
                                    $totalFechamentos = contarFechamentos();
                                    $totalPaginas = ceil($totalFechamentos / $limite);
                    
                                    if ($fechamentos)
                                    {
                                        foreach ($fechamentos as $fechamento)
                                        {
                                ?>
                                            <tr>
                                                <td class="fw-bold text-primary">#<?=$fechamento['id'];?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title rounded-circle border border-white bg-info"><?= strtoupper(substr($fechamento['usuario'], 0, 1)) ?></span>
                                                        </div>
                                                        <?=htmlspecialchars($fechamento['usuario']);?>
                                                    </div>
                                                </td>
                                                <td class="text-success fw-bold">R$ <?=number_format($fechamento['entrada'], 2, ',', '.');?></td>
                                                <td class="text-danger fw-bold">R$ <?=number_format($fechamento['saida'], 2, ',', '.');?></td>
                                                <td class="fw-bold">R$ <?=number_format($fechamento['saldo_total'], 2, ',', '.');?></td>
                                                <td>
                                                    <span class="badge badge-count"><?= date('d/m/Y', strtotime($fechamento['dia_fechamento'])) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-button-action">
                                                        <button type="button" data-bs-toggle="tooltip" title="Ver Detalhes" class="btn btn-link btn-primary btn-lg" 
                                                                onclick="visualizarDetalhes(<?=$fechamento['id'];?>)">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                <?php
                                        }
                                    } else {
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Nenhum fechamento encontrado</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($totalPaginas > 1): ?>
                    <div class="card-footer">
                        <ul class="pagination pg-primary mb-0 justify-content-end">
                            <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFechamentos&pagina=1" aria-label="Primeira">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=ListarFechamentos&pagina=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFechamentos&pagina=<?= $totalPaginas ?>" aria-label="Última">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes -->
<div class="modal fade" id="detalhesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content card-round">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Detalhes do Fechamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalhesConteudo">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Fechamento -->
<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content card-round">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <span class="fw-bold">Novo Fechamento</span>
                    <small class="text-muted ms-2"><?=date('d/m/Y');?></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                $fechamento_existente = buscarFechamentoDoDia(date('Y-m-d'));
                if ($fechamento_existente): 
                ?>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Fechamento já realizado!</h6>
                        <p class="mb-0">Já existe um registro para hoje (<strong><?=date('d/m/Y');?></strong>) realizado por <strong><?=$fechamento_existente['usuario'];?></strong>.</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-round" data-bs-dismiss="modal">Fechar</button>
                </div>
                
                <?php else: ?>
                
                <form id="formFechamentos" action="cadastrarFechamentos.php" method="POST" enctype="multipart/form-data">
                <?php 
                    $entradasnodia = buscarFinanceiroEntradasnodia(date('Y-m-d'));
                    $total_Entrada = !empty($entradasnodia[0]['total']) ? $entradasnodia[0]['total'] : '0.00';
                    $estornosnodia = buscarFinanceiroEstornosnodia(date('Y-m-d'));
                    $totalEstornos = !empty($estornosnodia[0]['total']) ? $estornosnodia[0]['total'] : '0.00';
                    $totalEntrada = $total_Entrada - $totalEstornos;
                    $saidasnodia = buscarFinanceiroSaidasnodia(date('Y-m-d'));
                    $totalSaida = !empty($saidasnodia[0]['total']) ? $saidasnodia[0]['total'] : '0.00';
                    $performance = $totalEntrada - $totalSaida;
                ?>
                <input type="hidden" name="valor_entrada" value="<?=$totalEntrada;?>"/>
                <input type="hidden" name="valor_saida" value="<?=$totalSaida;?>"/>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-stats card-round bg-success-gradient text-white">
                            <div class="card-body">
                                <p class="card-category text-white-50">Entradas</p>
                                <h4 class="card-title">R$ <?=number_format($totalEntrada, 2, ',', '.');?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stats card-round bg-danger-gradient text-white">
                            <div class="card-body">
                                <p class="card-category text-white-50">Saídas</p>
                                <h4 class="card-title">R$ <?=number_format($totalSaida, 2, ',', '.');?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stats card-round bg-info-gradient text-white">
                            <div class="card-body">
                                <p class="card-category text-white-50">Performance</p>
                                <h4 class="card-title">R$ <?=number_format($performance, 2, ',', '.');?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold mb-3">Vendas por Tipo de Pagamento</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Forma de Pagamento</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totaisPorPagamento = buscarTotalVendasPorTipoPagamento($_GET['dia'] ?? null, $_GET['mes'] ?? null, $_GET['ano'] ?? null);
                                    $totalVendas = 0;
                                    foreach ($totaisPorPagamento as $pagamento): 
                                        $totalVendas += $pagamento['total_vendas'];
                                    ?>
                                    <tr>
                                        <td><i class="fas fa-check-circle text-success me-2"></i><?= htmlspecialchars($pagamento['tipo_pagamento']) ?></td>
                                        <td class="text-end fw-bold">R$ <?= number_format($pagamento['total_vendas'], 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td class="text-end">Total de Vendas:</td>
                                        <td class="text-end text-primary">R$ <?= number_format($totalVendas, 2, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="fw-bold mb-3">Saldos Atuais em Contas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Conta</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $contas = buscarContas('', '', 100, 0);
                                        if ($contas) {
                                            foreach ($contas as $conta) {
                                    ?>
                                    <input type="hidden" name="ids_contas[]" value="<?=$conta['id'];?>"/>
                                    <input type="hidden" name="saldos_contas[]" value="<?=$conta['saldo'];?>"/>
                                    <tr>
                                        <td><i class="fas fa-university text-muted me-2"></i><?=$conta['nome'];?></td>
                                        <td class="text-end fw-bold">R$ <?=number_format($conta['saldo'], 2, ',', '.');?></td>
                                    </tr>
                                    <?php } } ?>
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <?php $somaSaldoContas = BuscarSomaSaldoContas(); ?>
                                        <td class="text-end">Saldo Total em Contas:</td>
                                        <td class="text-end text-success">R$ <?=number_format($somaSaldoContas, 2, ',', '.');?></td>
                                    </tr>
                                </tfoot>
                                <input type="hidden" name="saldo_total" value="<?=$somaSaldoContas;?>"/>
                            </table>
                        </div>
                    </div>
                </div>
                    
                <div class="modal-footer border-0 px-0">
                    <button type="button" class="btn btn-label-danger btn-round me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-round">Confirmar Fechamento</button>
                </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function visualizarDetalhes(fechamentoId) {
    $.ajax({
        url: 'detalhes_fechamento.php',
        type: 'GET',
        data: { id: fechamentoId },
        success: function(response) {
            $('#detalhesConteudo').html(response);
            $('#detalhesModal').modal('show');
        }
    });
}

$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

