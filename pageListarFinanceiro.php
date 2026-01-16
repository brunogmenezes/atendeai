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
            <div class="d-flex align-items-center">
                <h4 class="card-title">Listar Lançamentos</h4>
                <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>" class="btn btn-secondary ms-2">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover">
					<thead>
						<tr>
							<th style="width: 5%">ID</th>
                            <th style="width: 5%">Tipo</th>
							<th>Nome</th>
                            <th style="width: 20%">Valor</th>
                            <th style="width: 20%">Conta</th>
                            <th style="width: 10%">Data Vencimento</th>
                            <th style="width: 5%"></th>
                            <th style="width: 5%"></th>
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
										<td><?=$financeiro['id'];?></td>
                                        <td>
                                            <i class=" 
                                                <?php 
                                                        echo ($financeiro['tipo'] == '1') ? 'fas fa-arrow-up text-success me-3' : 
                                                        (($financeiro['tipo'] == '2') ? 'fas fa-arrow-down text-danger me-3' : 
                                                        (($financeiro['tipo'] == '3') ? 'fas fa-arrow-left text-warning me-3' : ''));
                                                ?>">
                                            </i>
                                        </td>
										<td><?=$financeiro['descricao'];?></td>
										<td>R$ <?=number_format($financeiro['valor'], 2, ',', '.');?></td>
                                        <td><?=$financeiro['nome_conta'];?></td>
                                        <td><?= date('d/m/Y', strtotime($financeiro['data_vencimento'])) ?></td>
                                        <td><?=$financeiro['pago']; ?></td>
                                        <td>
                                            <?php
                                                if ($financeiro['criado_manual']==true)
                                                {
                                                 
                                            ?>
                                                    <button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$financeiro['id'];?>" data-tipo="<?=$financeiro['tipo'];?>" data-conta="<?=$financeiro['conta'];?>" data-valor="<?=$financeiro['valor'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                            <?php
                                                }
                                            ?>
                                        </td>
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
                            <a class="page-link" 
                               href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=1&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
                                &laquo;
                            </a>
                        </li>
                        
                        <!-- Link para página anterior -->
                        <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina - 1) ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
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
                                <a class="page-link" 
                                   href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Link para próxima página -->
                        <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina + 1) ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
                                &rsaquo;
                            </a>
                        </li>
                        
                        <!-- Link para última página -->
                        <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $totalPaginas ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>">
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
    // Abrir o modal e definir o ID do produto
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

