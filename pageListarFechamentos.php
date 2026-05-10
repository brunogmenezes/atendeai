<?php
	include("config.php");
	include("funcoes.php");
    require_once 'auth.php';
    verificarSessao();
?>
<style>
    .form-group
    {
        margin-bottom: 1rem;
    }
    .form-select, .form-control
    {
        height: calc(2.25rem + 8px);
    }
    .card-header
    {
        padding-bottom: 1.5rem;
    }
</style>
<div class="col-md-12">
	<div class="card">
		<div class="card-header">
            <div class="d-flex flex-column flex-sm-row align-items-center">
                <h4 class="card-title mb-2 mb-sm-0">Listar Fechamentos de caixa</h4>
                <button class="btn btn-primary btn-round ms-sm-auto w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
                    <i class="fa fa-plus"></i>
                    Cadastrar Fechamento
                </button>
            </div>
        </div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover table-mobile-cards">
					<thead>
						<tr>
							<th style="width: 5%">ID</th>
							<th>Usuário</th>
                             <th class="d-none d-md-table-cell" style="width: 15%">Entradas</th>
                            <th class="d-none d-md-table-cell" style="width: 15%">Saídas</th>
                            <th style="width: 15%">Saldo Total</th>
                            <th style="width: 15%">Data Fechamento</th>
                            <th style="width: 10%">Ações</th>
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
										<td data-label="ID">#<?=$fechamento['id'];?></td>
										<td data-label="Usuário"><?=$fechamento['usuario'];?></td>
										<td class="d-none d-md-table-cell" data-label="Entradas">R$ <?=number_format($fechamento['entrada'], 2, ',', '.');?></td>
										<td class="d-none d-md-table-cell" data-label="Saídas">R$ <?=number_format($fechamento['saida'], 2, ',', '.');?></td>
										<td data-label="Saldo Total">R$ <?=number_format($fechamento['saldo_total'], 2, ',', '.');?></td>
                                        <td data-label="Data"><?= date('d/m/Y', strtotime($fechamento['dia_fechamento'])) ?></td>
                                        <td data-label="Ação">
                                            <button type="button" class="btn btn-info btn-sm w-100" 
                                                    onclick="visualizarDetalhes(<?=$fechamento['id'];?>)">
                                                <i class="fa fa-eye"></i> Detalhes
                                            </button>
                                        </td>
									</tr>
						<?php
								}
							} else {
						?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum fechamento encontrado</td>
                            </tr>
                        <?php } ?>
					</tbody>
				</table>
			</div>
            
            <!-- Paginação -->
            <div class="col-md-12 mt-3">
                <div class="demo d-flex justify-content-center">
                    <ul class="pagination pg-primary">
                        <?php if ($totalPaginas > 1): ?>
                        <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=ListarFechamentos&pagina=1">&laquo;</a>
                        </li>
                
                        <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=ListarFechamentos&pagina=<?= max(1, $pagina - 1) ?>">&lsaquo;</a>
                        </li>
                
                        <?php 
                        $paginas_visiveis = 5;
                        $inicio = max(1, $pagina - floor($paginas_visiveis/2));
                        $fim = min($totalPaginas, $inicio + $paginas_visiveis - 1);
                        $inicio = max(1, $fim - $paginas_visiveis + 1);
                
                        for ($i = $inicio; $i <= $fim; $i++): 
                        ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=ListarFechamentos&pagina=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                
                        <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=ListarFechamentos&pagina=<?= min($totalPaginas, $pagina + 1) ?>">&rsaquo;</a>
                        </li>
                
                        <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=ListarFechamentos&pagina=<?= $totalPaginas ?>">&raquo;</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
		</div>
	</div>
</div>

<!-- Modal para detalhes -->
<div class="modal fade" id="detalhesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Fechamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalhesConteudo">
                <!-- Conteúdo carregado via AJAX -->
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
</script>

<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header border-0">
				<h5 class="modal-title">
					<span class="fw-mediumbold"> Fechamento</span>
					<span class="fw-light"> do dia <?=date('d/m/Y');?></span>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <?php
                // Verificar se já existe fechamento do dia
                $fechamento_existente = buscarFechamentoDoDia(date('Y-m-d'));
                
                if ($fechamento_existente): 
                ?>
                <div class="alert alert-warning">
                    <h6><i class="fa fa-exclamation-triangle"></i> Fechamento já realizado!</h6>
                    <p>Já existe um fechamento cadastrado para hoje (<strong><?=date('d/m/Y');?></strong>).</p>
                    <p><strong>Usuário:</strong> <?=$fechamento_existente['usuario'];?></p>
                    <p><strong>Saldo Total:</strong> R$ <?=number_format($fechamento_existente['saldo'], 2, ',', '.');?></p>
                    <p><strong>Horário:</strong> <?=date('H:i:s', strtotime($fechamento_existente['created_at']));?></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
                
                <?php else: ?>
                
                <form id="formFechamentos" action="cadastrarFechamentos.php" method="POST" enctype="multipart/form-data">
                <?php 
                    $entradasnodia = buscarFinanceiroEntradasnodia(date('Y-m-d'));
                    $total_Entrada = !empty($entradasnodia[0]['total']) ? $entradasnodia[0]['total'] : '0.00';

                    $estornosnodia = buscarFinanceiroEstornosnodia(date('Y-m-d'));
                    $totalEstornos = !empty($estornosnodia[0]['total']) ? $estornosnodia[0]['total'] : '0.00';

                    $totalEntrada = $total_Entrada-$totalEstornos;
                    
                    $saidasnodia = buscarFinanceiroSaidasnodia(date('Y-m-d'));
                    $totalSaida = !empty($saidasnodia[0]['total']) ? $saidasnodia[0]['total'] : '0.00';

                    $performance = $totalEntrada-$totalSaida;
                ?>
                <input type="hidden" name="valor_entrada" value="<?=$totalEntrada;?>"/>
                <input type="hidden" name="valor_saida" value="<?=$totalSaida;?>"/>
                
                <div class="table-responsive">
                    <table class="display table table-striped table-hover table-mobile-cards">
                        <thead>
                            <tr>
                                <th>Entradas</th>
                                <th>Saídas</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="Entradas">R$ <?=number_format($totalEntrada, 2, ',', '.'); ?></td>
                                <td data-label="Saídas">R$ <?= number_format($totalSaida, 2, ',', '.'); ?></td>
                                <td data-label="Performance">R$ <?= number_format($performance, 2, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

               <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo de Pagamento</th>
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

                  <div class="table-responsive mt-3">
                    <table class="display table table-striped table-hover table-mobile-cards">
                        <thead>
                            <tr>
                                <th style="width: 20%">Conta</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th class="d-none d-md-table-cell">Total:</th>
                                <th>
                                    <?php
                                        $somaSaldoContas = BuscarSomaSaldoContas();
                                    ?>
                                    R$ <?=$somaSaldoContas !== null ? number_format($somaSaldoContas, 2, ',', '.') : '0,00';?>
                                </th>
                                <input type="hidden" name="saldo_total" value="<?=$somaSaldoContas;?>"/>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                                $contas = buscarContas('', '', 100, 0); // Buscar todas as contas
                                if ($contas)
                                {
                                    foreach ($contas as $conta)
                                    {
                            ?>
                                <input type="hidden" name="ids_contas[]" value="<?=$conta['id'];?>"/>
                                <input type="hidden" name="saldos_contas[]" value="<?=$conta['saldo'];?>"/>
                                
                                        <tr>
                                            <td data-label="Conta"><?=$conta['nome'];?></td>
                                            <td data-label="Saldo">R$ <?=number_format($conta['saldo'], 2, ',', '.');?></td>
                                        </tr>
                            <?php
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
					
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary">Salvar Fechamento</button>
						<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
					</div>
				</form>
                <?php endif; ?>
			</div>
		</div>
	</div>
</div>


<script>
// Adicione este script para desabilitar o botão se já existir fechamento
$(document).ready(function() {
    // Verificar ao abrir o modal
    $('#addRowModal').on('show.bs.modal', function() {
        $.ajax({
            url: 'verificar_fechamento.php',
            type: 'GET',
            data: { data: '<?=date('Y-m-d');?>' },
            success: function(response) {
                if (response.existe) {
                    $('#btnSalvarFechamento').prop('disabled', true)
                        .html('<i class="fa fa-ban"></i> Fechamento já realizado');
                }
            }
        });
    });
});
</script>

