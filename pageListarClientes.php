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
				<h4 class="card-title">Listar Clientes</h4>
				<button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
					<i class="fa fa-plus"></i>
					Cadastrar Cliente
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
                            <th>Data de Nascimento</th>
                            <th>Contato</th>
                            <th style="width: 10%">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
                			$filtro = $_GET['filtro'] ?? '';
                			$valor = $_GET['valor'] ?? '';
                			$pagina = $_GET['pagina'] ?? 1;
                			$limite = 10;
                			$offset = ($pagina - 1) * $limite;
			
                			// Buscar clientes com paginação
                			$clientes = buscarClientes($filtro, $valor, $limite, $offset);
                			$totalClientes = contarClientes($filtro, $valor);
                			$totalPaginas = ceil($totalClientes / $limite);
			
                			if ($clientes)
                			{
                    			foreach ($clientes as $cliente)
                    			{
                    	?>
									<tr>
										<td><?=$cliente['id'];?></td>
										<td><?=$cliente['nome'];?></td>
										<td><?= date('d/m/Y', strtotime($cliente['data_nascimento'])) ?></td>
										<td><?=$cliente['telefone'];?></td>
										<td>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary open-edit-modal" data-id="<?=$cliente['id'];?>" data-nome="<?=$cliente['nome'];?>" data-data_nascimento="<?=$cliente['data_nascimento'];?>" data-telefone="<?=$cliente['telefone'];?>" data-bs-toggle="modal" data-bs-target="#editClienteModal">
													<i class="fa fa-edit"></i>
												</button>
												<button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$cliente['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
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
                <p>Tem certeza de que deseja excluir o cadastro desse cliente?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="excluirCliente.php" method="POST">
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
								<span class="fw-light"> Cliente </span>
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
                            <form action="cadastrarCliente.php" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Nome</label>
											<input id="nome" name="nome" type="text" class="form-control" required/>
										</div>
									</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>Data de Nascimento</label>
											<input id="data_nascimento" type="date" class="form-control" name="data_nascimento" />
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Contato</label>
											<input id="telefone" type="number" class="form-control" name="telefone" />
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
			<div class="modal fade" id="editClienteModal" tabindex="-1" role="dialog" aria-hidden="true">
    			<div class="modal-dialog" role="document">
        			<div class="modal-content">
            			<div class="modal-header border-0">
                			<h5 class="modal-title">
								<span class="fw-mediumbold"> Editar</span>
								<span class="fw-light"> Cliente </span>
							</h5>
                			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            			</div>
            			<div class="modal-body">
                			<form id="editForm" action="editarCliente.php" method="POST" enctype="multipart/form-data">
                				<div class="row">
                    				<input type="hidden" name="id" id="clienteIDEdit">
                    				<div class="col-sm-12">
                    					<div class="form-group form-group-default">
											<label>Nome</label>
                        					<input type="text" class="form-control" id="edit_nome" name="nome" required>
                    					</div>
                    				</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>Data de Nascimento</label>
											<input type="date" class="form-control" id="edit_data_nascimento" name="data_nascimento" required>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Contato</label>
											<input type="number" class="form-control" id="edit_telefone" name="telefone" required>
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
        const clienteIdEdit = document.getElementById('clienteIDEdit');
        const clienteNameEdit = document.getElementById('edit_nome');
        const clienteDataNascimento = document.getElementById('edit_data_nascimento');
        const clienteTelefone = document.getElementById('edit_telefone');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                clienteIdEdit.value = this.getAttribute('data-id');
                clienteNameEdit.value = this.getAttribute('data-nome');
                clienteDataNascimento.value = this.getAttribute('data-data_nascimento');
                clienteTelefone.value = this.getAttribute('data-telefone');
            });
        });
    });
</script>

