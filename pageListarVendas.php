<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

// Validação de parâmetros GET
$dia = isset($_GET['dia']) && is_numeric($_GET['dia']) ? (int)$_GET['dia'] : null;
$mes = isset($_GET['mes']) && is_numeric($_GET['mes']) ? (int)$_GET['mes'] : null;
$ano = isset($_GET['ano']) && is_numeric($_GET['ano']) ? (int)$_GET['ano'] : null;
?>
<div class="row">
    
    <div class="col-md-6">
            <div class="alert alert-light border">
                <h6 class="alert-heading"><i class="fas fa-chart-bar"></i> Resumo do Dia</h6>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Total de Vendas no dia:</span>
                    <strong><?= contarNumeroPorVendas($dia, $mes, $ano) ?? 0; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total em Vendas no dia:</span>
                    <strong class="text-success">R$ <?= number_format(buscarTotalVendasnoPeriodo($dia, $mes, $ano) ?? 0, 2, ',', '.'); ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total em Vendas no mês:</span>
                    <strong class="text-primary">R$ <?= number_format(buscarTotalVendasnoMes($mes, $ano) ?? 0, 2, ',', '.'); ?></strong>
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
            <a href="gerar_pdf_vendas.php?dia=<?= $dia ?? '' ?>&mes=<?= $mes ?? '' ?>&ano=<?= $ano ?? '' ?>" class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Gerar PDF
            </a>
        </div>
        <form action="index.php?page=ListarVendas" method="GET" class="row g-2 mb-4">
            <input type="hidden" name="page" value="ListarVendas">
            <div class="col-12 col-sm-6 col-md-3">
                <label for="dia" class="form-label">Dia</label>
                <select name="dia" id="dia" class="form-select">
                    <option value="">Selecionar...</option>
                    <?php for ($i = 1; $i <= 31; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $dia) ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label for="mes" class="form-label">Mês</label>
                <select name="mes" id="mes" class="form-select">
                    <option value="">Selecionar...</option>
                    <?php
                        $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                        foreach ($meses as $num => $nome): ?>
                            <option value="<?= $num ?>" <?= ($num == $mes) ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label for="ano" class="form-label">Ano</label>
                <select name="ano" id="ano" class="form-select">
                    <option value="">Selecionar...</option>
                    <?php for ($i = 2025; $i <= 2030; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $ano) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
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
                            <th>Data</th>
                            <th class="text-center" style="width: 80px;">Ações</th>
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
                                        $rowClass = 'class="table-danger text-muted"';
                                    }
                                    else
                                    {
                                        $rowClass = '';
                                    }
                    	?>
									<tr <?=$rowClass;?>>
										<td><?=$retorno['id'];?></td>
										<td><strong>R$ <?=number_format($retorno['total'], 2, ',', '.');?></strong></td>
                                        <td><?=$retorno['tipos_pagamento'];?></td>
                                        <td><?=$retorno['usuariovendedor'];?></td>
										<td><?=date('d/m/Y H:i:s', strtotime($retorno['data_venda']));?></td>
										<td>
											<div class="btn-group btn-group-sm">
												<button type="button" class="btn btn-link btn-primary" onclick="imprimir(<?=htmlspecialchars($retorno['id'], ENT_QUOTES, 'UTF-8')?>);" title="Imprimir">
    												<i class="fa fa-print"></i>
												</button>
                                                <?php
                                                if($user['isAdmin']==true && $retorno['estornado'] == false)
                                                {
                                                ?>
												<button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$retorno['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" title="Estornar venda">
												    <i class="fa fa-retweet"></i>
												</button>
                                                <?php
                                                }
                                                ?>
											</div>
										</td>
									</tr>
						<?php
								}
							}
                            else
                            {
                                echo '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i> Nenhuma venda encontrada para o período selecionado</td></tr>';
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

	document.addEventListener('DOMContentLoaded', function () {
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

