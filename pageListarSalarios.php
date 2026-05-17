<?php
	include("config.php");
	include("funcoes.php");
    require_once 'auth.php';
verificarSessao(); $stmtPerfis = $pdo->query("SELECT id, nome FROM perfis ORDER BY nome ASC"); $perfis = $stmtPerfis->fetchAll(PDO::FETCH_ASSOC);
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
                            <th style="width: 25%">Perfil / Cargo</th>
                            <th style="width: 20%">Salário</th>
                            <th style="width: 10%">Ações</th>
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
			
                			// Buscar com paginação e JOIN nas permissões/usuarios
                            $sqlColabs = "
                                SELECT c.*, u.username, u.\"isAdmin\", u.perfil_id, p.nome as perfil_nome
                                FROM colaboradores c
                                LEFT JOIN usuarios u ON c.idusuario = u.id
                                LEFT JOIN perfis p ON u.perfil_id = p.id
                            ";
                            if (!empty($filtro) && !empty($valor)) {
                                $sqlColabs .= " WHERE c." . $filtro . " ILIKE :valor";
                            }
                            $sqlColabs .= " ORDER BY c.id ASC LIMIT :limite OFFSET :offset";

                            $stmtColabs = $pdo->prepare($sqlColabs);
                            if (!empty($filtro) && !empty($valor)) {
                                $stmtColabs->bindValue(':valor', "%$valor%", PDO::PARAM_STR);
                            }
                            $stmtColabs->bindValue(':limite', $limite, PDO::PARAM_INT);
                            $stmtColabs->bindValue(':offset', $offset, PDO::PARAM_INT);
                            $stmtColabs->execute();
                            $colaboradores = $stmtColabs->fetchAll(PDO::FETCH_ASSOC);

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
                                        <td>
                                            <?php
                                                $isAdmin = isset($colaborador['isAdmin']) ? $colaborador['isAdmin'] : ($colaborador['isadmin'] ?? false);
                                                if ($isAdmin) {
                                                    echo '<span class="badge badge-success"><i class="fas fa-user-shield me-1"></i>Administrador</span>';
                                                } else if ($colaborador['perfil_nome']) {
                                                    echo '<span class="badge badge-info">' . htmlspecialchars($colaborador['perfil_nome']) . '</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary">Sem perfil de acesso</span>';
                                                }
                                            ?>
                                        </td>
										<td>R$ <?=number_format($colaborador['salario'], 2, ',', '.');?></td>
										<td>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary open-edit-modal" 
                                                        data-id="<?=$colaborador['id'];?>" 
                                                        data-nome="<?=htmlspecialchars($colaborador['nome']);?>" 
                                                        data-username="<?=htmlspecialchars($colaborador['username'] ?? '');?>" 
                                                        data-salario="<?=$colaborador['salario'];?>" 
                                                        data-data_contratacao="<?=date('Y-m-d', strtotime($colaborador['data_contratacao']));?>" 
                                                        data-perfil_id="<?=$colaborador['perfil_id'] ?? '';?>" 
                                                        data-is_admin="<?=$isAdmin ? '1' : '0';?>" 
                                                        data-bs-toggle="modal" data-bs-target="#editColaboradorFixaModal">
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
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-default">
                                            <label>Perfil de Permissão</label>
                                            <select id="perfil_id" name="perfil_id" class="form-control">
                                                <option value="">Nenhum (Sem Acesso)</option>
                                                <?php foreach ($perfis as $p): ?>
                                                    <option value="<?=$p['id'];?>"><?=htmlspecialchars($p['nome']);?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 d-flex align-items-center ps-4">
                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1">
                                            <label class="form-check-label fw-bold text-success" for="is_admin">
                                                <i class="fas fa-shield-alt me-1"></i>Administrador (Total)
                                            </label>
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
                                <input type="hidden" name="id" id="colaboradorIDEdit" value="">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-default">
                                            <label>Nome</label>
                                            <input id="edit_nome" name="nome" type="text" class="form-control" required/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-default">
                                            <label>Usuário / Username</label>
                                            <input id="edit_username" name="username" type="text" class="form-control" required/>
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
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-default">
                                            <label>Perfil de Permissão</label>
                                            <select id="edit_perfil_id" name="perfil_id" class="form-control">
                                                <option value="">Nenhum (Sem Acesso)</option>
                                                <?php foreach ($perfis as $p): ?>
                                                    <option value="<?=$p['id'];?>"><?=htmlspecialchars($p['nome']);?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 d-flex align-items-center ps-4">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="is_admin" id="edit_is_admin" value="1">
                                            <label class="form-check-label fw-bold text-success" for="edit_is_admin" style="margin-top: 10px;">
                                                <i class="fas fa-shield-alt me-1"></i>Administrador (Total)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group form-group-default">
                                            <label>Nova Senha (Deixe em branco para manter a atual)</label>
                                            <input id="edit_senha" name="senha" type="password" class="form-control" placeholder="Preencha apenas se desejar alterar a senha deste colaborador" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pb-0 pe-0">
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
        const colaboradorUsername = document.getElementById('edit_username');
        const colaboradorSalario = document.getElementById('edit_salario');
        const colaboradorDataContratacao = document.getElementById('edit_data_contratacao');
        const colaboradorPerfil = document.getElementById('edit_perfil_id');
        const colaboradorIsAdmin = document.getElementById('edit_is_admin');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                colaboradorIDEdit.value = this.getAttribute('data-id');
                colaboradorNome.value = this.getAttribute('data-nome');
                colaboradorUsername.value = this.getAttribute('data-username') ? this.getAttribute('data-username') : '';
                colaboradorSalario.value = this.getAttribute('data-salario');
                colaboradorDataContratacao.value = this.getAttribute('data-data_contratacao');
                
                // Limpar campo de senha
                document.getElementById('edit_senha').value = '';
                
                // Definir campos de permissão no modal de edição
                const perfilId = this.getAttribute('data-perfil_id');
                const isAdmin = this.getAttribute('data-is_admin');
                
                colaboradorPerfil.value = perfilId ? perfilId : '';
                colaboradorIsAdmin.checked = (isAdmin === '1');
            });
        });
    });
</script>

