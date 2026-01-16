<?php
	include("config.php");
	include("funcoes.php");
    require_once 'auth.php';
verificarSessao();
    
?>
<div class="row">
    
    <div class="col-md-6">
            <div class="alert alert-light border">
                <h6 class="alert-heading"><i class="fas fa-chart-bar"></i> Resumo do Dia</h6>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Total de Vendas no dia:</span>
                    <strong><?= contarNumeroPorVendas($_GET['dia'] ?? null, $_GET['mes'] ?? null, $_GET['ano'] ?? null) ?? 0; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total em Vendas no dia:</span>
                    <strong class="text-success">R$ <?= number_format(buscarTotalVendasnoPeriodo($_GET['dia'] ?? null, $_GET['mes'] ?? null, $_GET['ano'] ?? null) ?? 0, 2, ',', '.'); ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total em Vendas no mês:</span>
                    <strong class="text-primary">R$ <?= number_format(buscarTotalVendasnoMes( $_GET['mes'] ?? null, $_GET['ano'] ?? null) ?? 0, 2, ',', '.'); ?></strong>
                </div>
            </div>
        </div>
    

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
                    </div>
                </div>
            </div>
        </div>
</div>
<div class="col-md-12">
	<div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Listar Vendas</h4>
            <a href="gerar_pdf_vendas.php?dia=<?=$_GET['dia'] ?? ''?>&mes=<?=$_GET['mes'] ?? ''?>&ano=<?=$_GET['ano'] ?? ''?>" class="btn btn-primary btn-round ml-auto" target="_blank">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </a>
        </div>
        <form action="index.php?page=ListarVendas" method="_GET" class="row g-2 mb-4">
            <input type="hidden" name="page" value="ListarVendas">
            <div class="col-auto">
                <label for="dia">Dia</label>
                <select name="dia" class="form-select">
                    <?php for ($i = 1; $i <= 31; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == ($_GET['dia'] ?? '')) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="mes">Mês</label>
                <select name="mes" class="form-select">
                    <?php
                        $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                        foreach ($meses as $num => $nome): ?>
                            <option value="<?= $num ?>" <?= ($num == ($_GET['mes'] ?? '')) ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="ano">Ano</label>
                <select name="ano" class="form-select">
                    <?php for ($i = 2025; $i <= 2030; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == ($_GET['ano'] ?? '')) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto align-self-end">
                <button type="submit" class="btn btn-success">Filtrar</button>
            </div>
        </form>
	</div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover">
					<thead>
						<tr>
							<th style="width: 5%">ID</th>
							<th>Total</th>
                            <th>Tipo de Pagamento</th>
                            <th>Vendedor</th>
                            <th style="width: 20%">Data</th>
                            <th style="width: 10%"></th>
						</tr>
					</thead>
					<tbody>
						<?php
                			$filtro = $_GET['filtro'] ?? '';
                			$valor = $_GET['valor'] ?? '';
                            $tabela = 'vendas';
                			$pagina = $_GET['pagina'] ?? 1;
                			$limite = 10;
                			$offset = ($pagina - 1) * $limite;
                			$orderby = "DESC";
			
                			$query = buscarTabelaVendas($tabela, $filtro, $valor, $limite, $offset, $orderby, $_GET['dia'], $_GET['mes'], $_GET['ano']);

                			$totalQuery = contarNumeroPorVendas($_GET['dia'] ?? null, $_GET['mes'] ?? null, $_GET['ano'] ?? null);
                			$totalPaginas = ceil($totalQuery / $limite);
			
                			if ($query)
                			{
                    			foreach ($query as $retorno)
                    			{
                                    if($retorno['estornado'] == true)
                                    {
                                        $style = 'style="font-style: italic; text-decoration: line-through; background: #ffbbbb;"';
                                    }
                                    else
                                    {
                                        $style = '';
                                    }
                    	?>
									<tr <?=$style;?>>
										<td <?=$style;?>><?=$retorno['id'];?></td>
										<td <?=$style;?>>R$ <?=number_format($retorno['total'], 2, ',', '.');?></td>
                                        <td <?=$style;?>><?=$retorno['tipos_pagamento'];?></td>
                                        <td <?=$style;?>><?=$retorno['usuariovendedor'];?></td>
										<td <?=$style;?>><?=date('d/m/Y H:i:s', strtotime($retorno['data_venda']));?></td>
										<td <?=$style;?>>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary" onclick="imprimir(<?=htmlspecialchars($retorno['id'], ENT_QUOTES, 'UTF-8')?>);"  data-id="<?=htmlspecialchars($retorno['id'], ENT_QUOTES, 'UTF-8')?>">
    												<i class="fa fa-print"></i>
												</button>
                                                <?php
                                                if($user['isAdmin']==true)
                                                {
                                                    if($retorno['estornado'] == false)
                                                    {
                                                    ?>
												        <button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$retorno['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
												    	   <i class="fa fa-retweet"></i>
												        </button>
                                                    <?php
                                                    }
                                                }
                                                ?>
											</div>
										</td>
									</tr>
						<?php
								}
							}
						?>
					</tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
				</table>
			</div>
			<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="demo">
                <ul class="pagination pg-primary">
                    <?php if ($totalPaginas > 1): ?>
                        <!-- Link para primeira página -->
                        <li class="page-item <?php echo ($pagina == 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=1&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>&dia=<?=$_GET['dia'];?>&mes=<?=$_GET['mes'];?>&ano=<?=$_GET['ano'];?>">
                                &laquo;
                            </a>
                        </li>
                        
                        <!-- Link para página anterior -->
                        <li class="page-item <?php echo ($pagina == 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo ($pagina - 1); ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>&dia=<?=$_GET['dia'];?>&mes=<?=$_GET['mes'];?>&ano=<?=$_GET['ano'];?>">
                                &lsaquo;
                            </a>
                        </li>
                        
                        <?php 
                        // Definir quantas páginas mostrar antes e depois da atual
                        $paginas_visiveis = 5;
                        $inicio = max(1, $pagina - floor($paginas_visiveis/2));
                        $fim = min($totalPaginas, $inicio + $paginas_visiveis - 1);
                        
                        // Ajustar se estiver no final
                        $inicio = max(1, $fim - $paginas_visiveis + 1);
                        
                        // Mostrar páginas próximas
                        for ($i = $inicio; $i <= $fim; $i++): 
                        ?>
                            <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo $i; ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>&dia=<?=$_GET['dia'];?>&mes=<?=$_GET['mes'];?>&ano=<?=$_GET['ano'];?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Link para próxima página -->
                        <li class="page-item <?php echo ($pagina == $totalPaginas) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo ($pagina + 1); ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>&dia=<?=$_GET['dia'];?>&mes=<?=$_GET['mes'];?>&ano=<?=$_GET['ano'];?>">
                                &rsaquo;
                            </a>
                        </li>
                        
                        <!-- Link para última página -->
                        <li class="page-item <?php echo ($pagina == $totalPaginas) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo $totalPaginas; ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>&dia=<?=$_GET['dia'];?>&mes=<?=$_GET['mes'];?>&ano=<?=$_GET['ano'];?>">
                                &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
		</div>
	</div>
</div>
<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Estorno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja estornar a venda?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="formExcluir.php" method="POST">
                    <input type="hidden" name="tabela" value="<?=$tabela;?>">
                    <input type="hidden" name="funcao" value="EstornarVenda">
                    <input type="hidden" name="page" value="<?=$_GET['page'];?>">
                    <input type="hidden" name="id" id="productIdToDelete">
                    <button type="submit" class="btn btn-danger">Estornar</button>
                </form>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>


<script>

	function imprimir(id) {
    // Validar o ID
    if (!id || isNaN(id)) {
        console.error('ID inválido');
        return;
    }
    
    // Abrir janela de impressão segura
    const url = `imprimirVenda.php?id=${encodeURIComponent(id)}`;
    window.open(url, '_blank');
}
    // Abrir o modal e definir o ID do produto
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const productIdInput = document.getElementById('productIdToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-imagem');
                productIdInput.value = productId; // Definir o ID no campo oculto
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function ()
    {
        // Script para exclusão
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const productIdInput = document.getElementById('productIdToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                productIdInput.value = productId;
            });
        });

    });
</script>

