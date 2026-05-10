<?php
	include("config.php");
	include("funcoes.php");
   require_once 'auth.php';
verificarSessao();
?>
<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .form-select, .form-control {
        height: calc(2.25rem + 8px);
    }
    .card-header {
        padding-bottom: 1.5rem;
    }
</style>
<div class="col-md-12">
	<div class="card">
		<div class="card-header">
            <div class="d-flex flex-column flex-sm-row align-items-center">
                <h4 class="card-title mb-2 mb-sm-0">Listar Lançamentos</h4>
                <button class="btn btn-primary btn-round ms-sm-auto w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
                    <i class="fa fa-plus"></i>
                    Cadastrar Lançamento
                </button>
            </div>
            <!-- Adicione este formulário de filtro -->
            <form method="GET" class="mt-3">
                <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? '') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Lançamento</label>
                            <select name="tipo_lancamento" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" <?= (isset($_GET['tipo_lancamento']) && $_GET['tipo_lancamento'] == '1' ? 'selected' : '' )?>>Entrada</option>
                                <option value="2" <?= (isset($_GET['tipo_lancamento']) && $_GET['tipo_lancamento'] == '2' ? 'selected' : '' )?>>Saída</option>
                                <option value="3" <?= (isset($_GET['tipo_lancamento']) && $_GET['tipo_lancamento'] == '3' ? 'selected' : '' )?>>Estorno</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Filtrar por</label>
                            <select name="filtro" class="form-select">
                                <option value="">Selecione</option>
                                <option value="descricao" <?= (isset($_GET['filtro']) && $_GET['filtro'] == 'descricao' ? 'selected' : '' )?>>Descrição</option>
                                <option value="valor" <?= (isset($_GET['filtro']) && $_GET['filtro'] == 'valor' ? 'selected' : '' )?>>Valor</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor</label>
                            <input type="text" name="valor" class="form-control" value="<?= htmlspecialchars($_GET['valor'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2 mt-2 mt-md-0">
                        <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                        <a href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>" class="btn btn-secondary flex-fill">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover table-mobile-cards">
					<thead>
						<tr>
							<th class="d-none d-md-table-cell" style="width: 5%">ID</th>
                            <th style="width: 5%">Tipo</th>
							<th>Nome</th>
                            <th>Valor</th>
                            <th class="d-none d-sm-table-cell" style="width: 20%">Conta</th>
                            <th class="d-none d-md-table-cell" style="width: 10%">Vencimento</th>
                            <th style="width: 5%">Pago</th>
                            <th style="width: 5%">Ação</th>
						</tr>
					</thead>
					<tbody>
						<?php
                            $filtro = $_GET['filtro'] ?? '';
                            $valor = $_GET['valor'] ?? '';
                            $tipo_lancamento = $_GET['tipo_lancamento'] ?? '';
                            $pagina = $_GET['pagina'] ?? 1;
                            $limite = 10;
                            $offset = ($pagina - 1) * $limite;
                            
                            // Buscar os dados com os filtros
                            $financeiros = buscarFinanceiro($filtro, $valor, $limite, $offset, $tipo_lancamento);
                            $totalFinanceiro = contarFinanceiro($filtro, $valor, $tipo_lancamento);
                            $totalPaginas = ceil($totalFinanceiro / $limite);
			
                			if ($financeiros)
                			{
                    			foreach ($financeiros as $financeiro)
                    			{
                    	?>
									<tr>
									<tr>
										<td class="d-none d-md-table-cell" data-label="ID"><?=$financeiro['id'];?></td>
                                        <td data-label="Tipo">
                                            <i class=" 
                                                <?php 
                                                        echo ($financeiro['tipo'] == '1') ? 'fas fa-arrow-up text-success' : 
                                                        (($financeiro['tipo'] == '2') ? 'fas fa-arrow-down text-danger' : 
                                                        (($financeiro['tipo'] == '3') ? 'fas fa-arrow-left text-warning' : ''));
                                                ?>">
                                            </i>
                                            <span class="d-md-none ms-2">
                                                <?= ($financeiro['tipo'] == '1') ? 'Entrada' : (($financeiro['tipo'] == '2') ? 'Saída' : 'Estorno') ?>
                                            </span>
                                        </td>
										<td data-label="Descrição"><?=$financeiro['descricao'];?></td>
										<td data-label="Valor">R$ <?=number_format($financeiro['valor'], 2, ',', '.');?></td>
                                        <td class="d-none d-sm-table-cell" data-label="Conta"><?=$financeiro['nome_conta'];?></td>
                                        <td class="d-none d-md-table-cell" data-label="Vencimento"><?= date('d/m/Y', strtotime($financeiro['data_vencimento'])) ?></td>
                                        <td data-label="Pago"><?=$financeiro['pago']; ?></td>
                                        <td data-label="Ação">
                                            <?php if ($financeiro['criado_manual']==true): ?>
                                                <button type="button" class="btn btn-link btn-danger open-delete-modal p-0" data-id="<?=$financeiro['id'];?>" data-tipo="<?=$financeiro['tipo'];?>" data-conta="<?=$financeiro['conta'];?>" data-valor="<?=$financeiro['valor'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                                    <i class="fa fa-times"></i> Excluir
                                                </button>
                                            <?php endif; ?>
                                        </td>
									</tr>
						<?php
								}
							}
						?>
					</tbody>
				</table>
			</div>
<div class="col-md-12 mt-3">
    <div class="demo d-flex justify-content-center">
        <ul class="pagination pg-primary">
            <?php if ($totalPaginas > 1): ?>
                <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=1&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">&laquo;</a>
                </li>
                
                <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina - 1) ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">&lsaquo;</a>
                </li>
                
                <?php 
                $paginas_visiveis = 5;
                $inicio = max(1, $pagina - floor($paginas_visiveis/2));
                $fim = min($totalPaginas, $inicio + $paginas_visiveis - 1);
                $inicio = max(1, $fim - $paginas_visiveis + 1);
                
                for ($i = $inicio; $i <= $fim; $i++): 
                ?>
                    <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina + 1) ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">&rsaquo;</a>
                </li>
                
                <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $totalPaginas ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">&raquo;</a>
                </li>
            <?php endif; ?>
        </ul>
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
                    <input type="hidden" name="id" id="productIdToDelete">
                    <input type="hidden" name="funcao" value="ExcluirFinanceiro">
                    <input type="hidden" name="tabela" value="financeiro">
                    <input type="hidden" name="page" value="<?=$_GET['page'];?>">
                    <input type="hidden" name="tipo" id="tipoLancamento">
                    <input type="hidden" name="conta" id="contaLancamento">
                    <input type="hidden" name="valor" id="valorLancamento">
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
								<span class="fw-light"> Lançamento </span>
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
                            <form action="cadastrarFinanceiro.php" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Nome</label>
											<input id="nome" name="nome" type="text" class="form-control" required/>
										</div>
									</div>
									<div class="col-md-6 pe-0">
                                        <div class="form-group form-group-default">
                                            <label>Tipo</label>
                                            <select class="form-select" id="tipo" name="tipo" required="">
                                                <option value="">Selecione</option>
                                                <option value="1">Entrada</option>
                                                <option value="2">Saída</option>
                                                <!--
                                                <option value="3">Transferência</option>
                                                -->
                                            </select>
                                        </div>
                                    </div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Valor R$</label>
											<input id="valor" type="number" step="0.01" class="form-control" name="valor" />
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
                                    <div class="col-md-6">
                                        <div class="form-group form-group-default">
                                            <label>Data Vencimento</label>
                                            <input id="data_vencimento" type="date" class="form-control" name="data_vencimento" />
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
			<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    			<div class="modal-dialog" role="document">
        			<div class="modal-content">
            			<div class="modal-header border-0">
                			<h5 class="modal-title">
								<span class="fw-mediumbold"> Editar</span>
								<span class="fw-light"> Lançamento </span>
							</h5>
                			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            			</div>
            			<div class="modal-body">
                			<form id="editForm" action="formEditar.php" method="POST" enctype="multipart/form-data">
                				<div class="row">
                    				<input type="hidden" name="id" id="idEdit">
                    				<div class="col-sm-12">
                    					<div class="form-group form-group-default">
											<label>Nome</label>
                        					<input type="text" class="form-control" id="edit_descricao" name="descricao" required>
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
    document.addEventListener('DOMContentLoaded', function ()
    {
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const productIdInput = document.getElementById('productIdToDelete');
        const tipoLancamentoInput = document.getElementById('tipoLancamento');
        const contaLancamentoInput = document.getElementById('contaLancamento');
        const valorLancamentoInput = document.getElementById('valorLancamento');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                const valortipoLancamento = this.getAttribute('data-tipo');
                const idContaLancamento = this.getAttribute('data-conta');
                const valorContaLancamento = this.getAttribute('data-valor');

                productIdInput.value = productId;
                tipoLancamentoInput.value = valortipoLancamento;
                contaLancamentoInput.value = idContaLancamento;
                valorLancamentoInput.value = valorContaLancamento;
            });
        });

        // Script para edição
        const editButtons = document.querySelectorAll('.open-edit-modal');
        const idEdit = document.getElementById('idEdit');
        const clienteNameEdit = document.getElementById('edit_descricao');
        const clienteDataNascimento = document.getElementById('edit_data_nascimento');
        const clienteTelefone = document.getElementById('edit_telefone');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                idEdit.value = this.getAttribute('data-id');
                clienteNameEdit.value = this.getAttribute('data-descricao');
                clienteDataNascimento.value = this.getAttribute('data-data_nascimento');
                clienteTelefone.value = this.getAttribute('data-telefone');
            });
        });
    });
</script>

