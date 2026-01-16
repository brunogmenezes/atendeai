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
				<h4 class="card-title">Listar Colaboradores</h4>
                
				<button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
					<i class="fa fa-plus"></i>
					Cadastrar Colaborador
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
                            <th style="width: 20%"></th>
                            <th style="width: 20%">Salário</th>
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
                                    $somaSalarioColaboradores = BuscarSomaSalarioColaboradores();
                                ?>
                                R$ <?=$somaSalarioColaboradores !== null ? number_format($somaSalarioColaboradores, 2, ',', '.') : '0,00';?>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
					<tbody>
						<?php
                			$filtro = $_GET['filtro'] ?? '';
                			$valor = $_GET['valor'] ?? '';
                            $tabela = 'colaboradores';
                            $tabela2 = 'usuarios';
                			$pagina = $_GET['pagina'] ?? 1;
                			$limite = 10;
                			$offset = ($pagina - 1) * $limite;
			
                			// Buscar Financeiro com paginação
                			$colaboradores = buscarTabela($tabela, $filtro, $valor, $limite, $offset);
                			$totalColaboradores = contarNumeroPorTabela($tabela, $filtro, $valor);
                			$totalPaginas = ceil($totalColaboradores / $limite);
			
                			if ($colaboradores)
                			{
                    			foreach ($colaboradores as $colaborador)
                    			{
                    	?>
									<tr>
										<td><?=$colaborador['id'];?></td>
										<td><?=$colaborador['nome'];?></td>
                                        <td></td>
										<td>R$ <?=number_format($colaborador['salario'], 2, ',', '.');?></td>
										<td>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary open-edit-modal" data-id="<?=$colaborador['id'];?>" data-nome="<?=$colaborador['nome'];?>" data-salario="<?=$colaborador['salario'];?>" data-data_contratacao="<?=date('Y-m-d', strtotime($colaborador['data_contratacao']));?>" data-bs-toggle="modal" data-bs-target="#editColaboradorFixaModal">
													<i class="fa fa-edit"></i>
												</button>
												<button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$colaborador['id'];?>" data-idusuario="<?=$colaborador['idusuario'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
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
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo $i; ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
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
                <p>Tem certeza de que deseja excluir o cadastro desse Colaborador?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="formExcluir.php" method="POST">
                    <input type="hidden" name="tabela" value="<?=$tabela;?>">
                    <input type="hidden" name="tabela2" value="<?=$tabela2;?>">
                    <input type="hidden" name="funcao" value="ExcluirColaborador">
                    <input type="hidden" name="page" value="<?=$_GET['page'];?>">
                    <input type="hidden" name="id" id="productIdToDelete">
                    <input type="hidden" name="idusuario" id="productIdUsuarioToDelete">
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
								<span class="fw-light"> Colaborador </span>
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
                            <form action="formCadastrar.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="funcao" value="CadastrarColaborador">
                                <input type="hidden" name="page" value="<?=$_GET['page'];?>">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Nome</label>
											<input id="nome" name="nome" type="text" class="form-control" required/>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Salário R$</label>
											<input id="salario" type="number" step="0.01" class="form-control" name="salario" required />
										</div>
									</div>
                                    <div class="col-md-6">
                                        <div class="form-group form-group-default">
                                            <label>Data Contratação</label>
                                            <input id="data_contratacao" type="date" class="form-control" name="data_contratacao" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-default">
                                            <label>Usuário</label>
                                            <input id="usuario" name="usuario" type="text" class="form-control" required/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-default">
                                            <label>Senha</label>
                                            <input id="senha" name="senha" type="password" class="form-control" required/>
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
			<div class="modal fade" id="editColaboradorFixaModal" tabindex="-1" role="dialog" aria-hidden="true">
    			<div class="modal-dialog" role="document">
        			<div class="modal-content">
            			<div class="modal-header border-0">
                			<h5 class="modal-title">
								<span class="fw-mediumbold"> Editar</span>
								<span class="fw-light"> Colaborador </span>
							</h5>
                			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            			</div>
            			<div class="modal-body">
                			<form id="editForm" action="formEditar.php" method="POST" enctype="multipart/form-data">
                				<input type="hidden" name="funcao" value="EditarColaborador">
                                <input type="hidden" name="page" value="<?=$_GET['page'];?>">
                                <input type="hidden" name="id" id="colaboradorIDEdit">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group form-group-default">
                                            <label>Nome</label>
                                            <input id="edit_nome" name="nome" type="text" class="form-control" required/>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group form-group-default">
                                            <label>Salário R$</label>
                                            <input id="edit_salario" type="number" step="0.01" class="form-control" name="salario" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group form-group-default">
                                            <label>Data Contratação</label>
                                            <input id="edit_data_contratacao" type="date" class="form-control" name="data_contratacao" />
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
        const productIdUsuarioInput = document.getElementById('productIdUsuarioToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-imagem');
                productIdInput.value = productId;
                productIdUsuarioInput.value = productId;
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function ()
    {
        // Script para exclusão
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const productIdInput = document.getElementById('productIdToDelete');
        const productIdUsuarioInput = document.getElementById('productIdUsuarioToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                const productIdUsuario = this.getAttribute('data-idusuario');
                productIdInput.value = productId;
                productIdUsuarioInput.value = productIdUsuario;
            });
        });

        // Script para edição
        const editButtons = document.querySelectorAll('.open-edit-modal');
        const colaboradorIDEdit = document.getElementById('colaboradorIDEdit');
        const colaboradorNome = document.getElementById('edit_nome');
        const colaboradorSalario = document.getElementById('edit_salario');
        const colaboradorDataContratacao = document.getElementById('edit_data_contratacao');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                colaboradorIDEdit.value = this.getAttribute('data-id');
                colaboradorNome.value = this.getAttribute('data-nome');
                colaboradorSalario.value = this.getAttribute('data-salario');
                colaboradorDataContratacao.value = this.getAttribute('data-data_contratacao');
            });
        });
    });
</script>

