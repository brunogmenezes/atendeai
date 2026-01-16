<?php
	include("config.php");
	include("funcoes.php");
    require_once 'auth.php';
verificarSessao();
?>

<div class="col-md-12">
	<div class="card">
		<div class="card-header">
			<div class="d-flex align-items-center">
				<h4 class="card-title">Listar Despesas Fixas Mensais</h4>
                
				<button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
					<i class="fa fa-plus"></i>
					Cadastrar Despesa
				</button>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover">
					<thead>
						<tr>
							<th style="width: 5%">ID</th>
							<th>Descrição</th>
                            <th style="width: 20%"></th>
                            <th style="width: 20%">Valor</th>
                            <th style="width: 10%"></th>
						</tr>
					</thead>
                    <tfoot>
                        <tr>
                            <th style="width: 5%"></th>
                            <th></th>
                            <th style="width: 20%">Total:</th>
                            <th>
                                <?php
                                    $somaDespesasFixas = BuscarSomaPorTabela('despesasfixas', 'valor');
                                ?>
                                R$ <?=$somaDespesasFixas !== null ? number_format($somaDespesasFixas, 2, ',', '.') : '0,00';?>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
					<tbody>
						<?php
                			$filtro = $_GET['filtro'] ?? '';
                			$valor = $_GET['valor'] ?? '';
                			$pagina = $_GET['pagina'] ?? 1;
                			$limite = 10;
                			$offset = ($pagina - 1) * $limite;
			
                			// Buscar Financeiro com paginação
                			$despesasFixas = buscarDespesasFixas($filtro, $valor, $limite, $offset);
                			$totalDespesasFixas = contarDespesasFixas($filtro, $valor);
                			$totalPaginas = ceil($totalDespesasFixas / $limite);
			
                			if ($despesasFixas)
                			{
                    			foreach ($despesasFixas as $despesas)
                    			{
                    	?>
									<tr>
										<td><?=$despesas['id'];?></td>
										<td><?=$despesas['descricao'];?></td>
                                        <td></td>
										<td>R$ <?=number_format($despesas['valor'], 2, ',', '.');?></td>
										<td>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary open-edit-modal" data-id="<?=$despesas['id'];?>" data-descricao="<?=$despesas['descricao'];?>" data-valor="<?=$despesas['valor'];?>" data-bs-toggle="modal" data-bs-target="#editDespesaFixaModal">
													<i class="fa fa-edit"></i>
												</button>
												<button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$despesas['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
													<i class="fa fa-times"></i>
												</button>
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
                        <!--<li class="page-item">
                          <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                          </a>
                        </li>
                    -->
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo $i; ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        <!--
                        <li class="page-item">
                          <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                          </a>
                        </li>
                    -->
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
                <p>Tem certeza de que deseja excluir o cadastro dessa despesa fixa?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="excluirDespesaFixa.php" method="POST">
                    <input type="hidden" name="id" id="productIdToDelete">
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
								<span class="fw-light"> Despesa </span>
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
                            <form action="cadastrarDespesaFixa.php" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Nome</label>
											<input id="nome" name="nome" type="text" class="form-control" required/>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Valor R$</label>
											<input id="valor" type="number" step="0.01" class="form-control" name="valor" />
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
			<!-- Modal de Edição -->
			<div class="modal fade" id="editDespesaFixaModal" tabindex="-1" role="dialog" aria-hidden="true">
    			<div class="modal-dialog" role="document">
        			<div class="modal-content">
            			<div class="modal-header border-0">
                			<h5 class="modal-title">
								<span class="fw-mediumbold"> Editar</span>
								<span class="fw-light"> Despesa Fixa </span>
							</h5>
                			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            			</div>
            			<div class="modal-body">
                			<form id="editForm" action="editarDespesaFixa.php" method="POST" enctype="multipart/form-data">
                				<div class="row">
                    				<input type="hidden" name="id" id="despesaIDEdit">
                    				<div class="col-sm-12">
                    					<div class="form-group form-group-default">
											<label>Descrição</label>
                        					<input type="text" class="form-control" id="edit_descricao" name="descricao" required focus>
                    					</div>
                    				</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Valor</label>
											<input type="number" step="0.01" class="form-control" id="edit_valor" name="valor" required>
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

    document.addEventListener('DOMContentLoaded', function () {
        // Script para exclusão
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const productIdInput = document.getElementById('productIdToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                productIdInput.value = productId;
            });
        });

        // Script para edição
        const editButtons = document.querySelectorAll('.open-edit-modal');
        const despesaIDEdit = document.getElementById('despesaIDEdit');
        const despesaFixaDescricao = document.getElementById('edit_descricao');
        const despesaFixaValor = document.getElementById('edit_valor');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                despesaIDEdit.value = this.getAttribute('data-id');
                despesaFixaDescricao.value = this.getAttribute('data-descricao');
                despesaFixaValor.value = this.getAttribute('data-valor');
            });
        });
    });
</script>

