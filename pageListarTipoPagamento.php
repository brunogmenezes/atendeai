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
				<h4 class="card-title">Listar Tipo de Pagamento</h4>
				<button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
					<i class="fa fa-plus"></i>
					Cadastrar Tipo de Pagamento
				</button>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th>Nome</th>
                            <th style="width: 10%">Conta</th>
                            <th style="width: 20%">Última Alteração</th>
						</tr>
					</thead>
					<tbody>
						<?php
                			$filtro = $_GET['filtro'] ?? '';
                			$valor = $_GET['valor'] ?? '';
                			$pagina = $_GET['pagina'] ?? 1;
                			$limite = 10;
                			$offset = ($pagina - 1) * $limite;
			
                			// Buscar contas com paginação
                			$tipoPagamentos = buscarTipoPagamento($filtro, $valor, $limite, $offset);
                			$totalTipoPagamento = contarTipoPagamento($filtro, $valor);
                			$totalPaginas = ceil($totalTipoPagamento / $limite);
			
                			if ($tipoPagamentos)
                			{
                    			foreach ($tipoPagamentos as $tipoPagamento)
                    			{
                    	?>
									<tr>
										<td><?=$tipoPagamento['id'];?></td>
										<td><?=$tipoPagamento['nome'];?></td>
                                        <td>
                                            <?php
                                                $valor = $tipoPagamento['conta'];
                                                $resultado = buscarContasFinanceiro($valor);
                                                if (!empty($resultado))
                                                {
                                                    foreach ($resultado as $conta)
                                                    {
                                                        echo $conta['nome'];
                                                    }
                                                }
                                                else
                                                {
                                                    echo "Nenhuma conta encontrada com o ID informado.";
                                                }
                                            ?>
                                        </td>
										<td><?= date('d/m/Y H:i:s', strtotime($conta['data_atualizacao'])) ?></td>
										<td>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary open-edit-modal" data-id="<?=$tipoPagamento['id'];?>" data-nome="<?=$tipoPagamento['nome'];?>" data-conta="<?=$tipoPagamento['conta'];?>"  data-bs-toggle="modal" data-bs-target="#editContaModal">
													<i class="fa fa-edit"></i>
												</button>
												<button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$tipoPagamento['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
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
			<!-- Paginação 
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo $i; ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>-->
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
                <p>Tem certeza de que deseja excluir o cadastro desse tipo de pagamento?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="excluirTipoPagamento.php" method="POST">
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
								<span class="fw-mediumbold"> Novo</span>
								<span class="fw-light"> Tipo de Pagamento </span>
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
                            <form action="cadastrarTipoPagamento.php" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Nome</label>
											<input id="nome" name="nome" type="text" class="form-control" required/>
										</div>
									</div>
									<div class="col-md-6 pe-0">
                                        <div class="form-group form-group-default">
                                            <label>Conta</label>
                                            <select class="form-select" id="conta" name="conta" required="">
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
			<!-- Modal de Edição -->
			<div class="modal fade" id="editContaModal" tabindex="-1" role="dialog" aria-hidden="true">
    			<div class="modal-dialog" role="document">
        			<div class="modal-content">
            			<div class="modal-header border-0">
                			<h5 class="modal-title">
								<span class="fw-mediumbold"> Editar</span>
								<span class="fw-light"> Conta </span>
							</h5>
                			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            			</div>
            			<div class="modal-body">
                			<form id="editForm" action="editarTipoPagamento.php" method="POST" enctype="multipart/form-data">
                				<div class="row">
                    				<input type="hidden" name="id" id="contaIDEdit">
                    				<div class="col-sm-12">
                    					<div class="form-group form-group-default">
											<label>Nome</label>
                        					<input type="text" class="form-control" id="edit_nome" name="nome" required>
                    					</div>
                    				</div>
									<div class="col-md-6 pe-0">
                                        <div class="form-group form-group-default">
                                            <label>Conta</label>
                                            <select class="form-select" id="conta" name="conta" required="">
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
        const contaIdEdit = document.getElementById('contaIDEdit');
        const contaNameEdit = document.getElementById('edit_nome');
        const contaTipo = document.getElementById('edit_tipo');
        const contaSaldo = document.getElementById('edit_saldo');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                contaIdEdit.value = this.getAttribute('data-id');
                contaNameEdit.value = this.getAttribute('data-nome');
                contaTipo.value = this.getAttribute('data-tipo');
                contaSaldo.value = this.getAttribute('data-saldo');
            });
        });
    });
</script>

