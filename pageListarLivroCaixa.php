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
            <div class="d-flex align-items-center">
                <h4 class="card-title">Listar Livro Caixa</h4>
                <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
                    <i class="fa fa-plus"></i>
                    Cadastrar Transferência
                </button>
            </div>
            
        </div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover">
					<thead>
						<tr>
							<th style="width: 5%">ID</th>
							<th>Nome</th>
                            <th style="width: 20%">Valor</th>
                            <th style="width: 10%">Data Lançamento</th>
                            <th style="width: 5%"></th>
						</tr>
					</thead>
					<tbody>
						<?php
                            $pagina = $_GET['pagina'] ?? 1;
                            $limite = 10;
                            $offset = ($pagina - 1) * $limite;
                            
                            // Buscar os dados com os filtros
                            $querys = buscarTransferencias($limite, $offset);
                            $totalQuerys = contarTransferencias();
                            $totalPaginas = ceil($totalQuerys / $limite);
			
                			if ($querys)
                			{
                    			foreach ($querys as $query)
                    			{
                    	?>
									<tr>
										<td>#<?=$query['id'];?></td>
										<td><?=$query['nome_conta_origem'];?> <i class="fas fa-arrow-right text-warning me-3"></i> <?=$query['nome_conta_destino'];?></td>
										<td>R$ <?=number_format($query['valor'], 2, ',', '.');?></td>
                                        <td><?= date('d/m/Y', strtotime($query['data_lancamento'])) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$query['id'];?>" data-id-conta-origem="<?=$query['id_conta_origem'];?>" data-id-conta-destino="<?=$query['id_conta_destino'];?>" data-valor="<?=$query['valor'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
										<!--
                                        <td>
											<div class="form-button-action">
                                                
												<button type="button" class="btn btn-link btn-primary open-edit-modal" data-id="<?=$query['id'];?>" data-tipo="<?=$query['tipo'];?>" data-descricao="<?=$query['descricao'];?>" data-valor="<?=$query['valor'];?>" data-data_lancamento="<?=$query['data_lancamento'];?>" data-conta="<?=$query['conta'];?>" data-bs-toggle="modal" data-bs-target="#editModal">
													<i class="fa fa-edit"></i>
												</button>
                                            -->
												
                                            
											</div>
										</td>
									</tr>
						<?php
								}
							}
						?>
					</tbody>
				</table>
			</div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="demo">
                            <ul class="pagination pg-primary">
                                <?php if ($totalPaginas > 1): ?>
                                <!-- Link para primeira página -->
                                <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=1&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
                                        &laquo;
                                    </a>
                                </li>
                        
                                <!-- Link para página anterior -->
                                <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina - 1) ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
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
                                    <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                        
                                <!-- Link para próxima página -->
                                <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina + 1) ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
                                        &rsaquo;
                                    </a>
                                </li>
                        
                                <!-- Link para última página -->
                                <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $totalPaginas ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
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
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir o cadastro desse lançamento?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="formExcluir.php" method="POST">
                    <input type="hidden" name="id" id="idTransferencia">
                    <input type="hidden" name="funcao" value="ExcluirTransferencia">
                    <input type="hidden" name="tabela" value="transferencias">
                    <input type="hidden" name="page" value="<?=$_GET['page'];?>">
                    <input type="hidden" name="id_conta_origem" id="id_conta_origem">
                    <input type="hidden" name="id_conta_destino" id="id_conta_destino">
                    <input type="hidden" name="valor" id="valor">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal de Cadastro -->
<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header border-0">
				<h5 class="modal-title">
					<span class="fw-mediumbold"> Nova</span>
					<span class="fw-light"> Transferência </span>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <form id="formTransferencia" action="cadastrarTransferencias.php" method="POST" enctype="multipart/form-data">
					<div class="row">
                        <div class="col-md-6 pe-0">
                            <div class="form-group form-group-default">
                                <label>Conta de Origem</label>
                                <select class="form-select" id="id_conta_origem" name="id_conta_origem" required="">
                                    <option value="">Selecione</option>
                                    <?php
                                        $resultado = buscarTodasContasFinanceiro();
                                        if (!empty($resultado))
                                        {
                                            foreach ($resultado as $conta)
                                            {
                                                echo "<option value='" . htmlspecialchars($conta['id']) . "'>" . htmlspecialchars($conta['nome']) . "</option>";
                                            }
                                        }
                                        else
                                        {
                                            echo "<option value=''>Nenhuma conta encontrada</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

						<div class="col-md-6">
							<div class="form-group form-group-default">
								<label>Valor R$</label>
								<input id="valor" type="number" step="0.01" class="form-control" name="valor" required="" />
							</div>
						</div>
                        <div class="col-md-6 pe-0">
                            <div class="form-group form-group-default">
                                <label>Conta de Destino</label>
                                <select class="form-select" id="id_conta_destino" name="id_conta_destino" required="">
                                    <option value="">Selecione</option>
                                    <?php
                                        $resultado = buscarTodasContasFinanceiro();
                                        if (!empty($resultado))
                                        {
                                            foreach ($resultado as $conta)
                                            {
                                                echo "<option value='" . htmlspecialchars($conta['id']) . "'>" . htmlspecialchars($conta['nome']) . "</option>";
                                            }
                                        }
                                        else
                                        {
                                            echo "<option value=''>Nenhuma conta encontrada</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
					</div>
					
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary">Salvar</button>
						<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function ()
    {
        const form = document.getElementById('formTransferencia');
        const origem = document.getElementById('id_conta_origem');
        const destino = document.getElementById('id_conta_destino');
        const valor = document.getElementById('valor');

        form.addEventListener('submit', function (e)
        {
            let erros = [];

            // Verifica se conta de origem e destino são iguais
            if (origem.value === destino.value)
            {
                erros.push("A conta de origem e destino não podem ser iguais.");
            }

            // Verifica se o valor é maior que zero
            if (parseFloat(valor.value) <= 0 || isNaN(parseFloat(valor.value)))
            {
                erros.push("O valor da transferência deve ser maior que zero.");
            }

            if (erros.length > 0)
            {
                e.preventDefault(); // Impede o envio
                alert(erros.join("\n"));
            }
        });
    
        //DELETAR    
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const idTransferenciaInput = document.getElementById('idTransferencia');
        const idContaOrigemInput = document.getElementById('id_conta_origem');
        const idContaDestinoInput = document.getElementById('id_conta_destino');
        const valorLancamentoInput = document.getElementById('valor');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const idTransferencia = this.getAttribute('data-id');
                const idContaOrigem = this.getAttribute('data-id-conta-origem');
                const idContaDestino = this.getAttribute('data-id-conta-destino');
                const valorTransferencia = this.getAttribute('data-valor');

                idTransferenciaInput.value = idTransferencia;
                idContaOrigemInput.value = idContaOrigem;
                idContaDestinoInput.value = idContaDestino;
                valorLancamentoInput.value = valorTransferencia;
            });
        });
    });
</script>

