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
				<h4 class="card-title">Listar Produtos</h4>
				<button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
					<i class="fa fa-plus"></i>
					Cadastrar Produto
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
                            <th>QNT Estoque</th>
                            <th>QNT Estoque Crítico</th>
                            <th>Preço de Custo</th>
                            <th>Preço de Venda</th>
                            <th style="width: 10%">Ação</th>
						</tr>
					</thead>
					<tbody>
						<?php
                			$filtro = $_GET['filtro'] ?? '';
                			$valor = $_GET['valor'] ?? '';
                			$pagina = $_GET['pagina'] ?? 1;
                			$limite = 10;
                			$offset = ($pagina - 1) * $limite;
			
                			// Buscar produtos com paginação
                			$produtos = buscarProdutos($filtro, $valor, $limite, $offset);
                			$totalProdutos = contarProdutos($filtro, $valor);
                			$totalPaginas = ceil($totalProdutos / $limite);
			
                			if ($produtos)
                			{
                    			foreach ($produtos as $produto)
                    			{
                    	?>
									<tr>
										<td><?=$produto['id'];?><div></td>
										<td><?=$produto['nome'];?></td>
										<td><?=$produto['quantidade'];?></td>
										<td><?=$produto['quantidade_critico'];?></td>
										<td>R$ <?=number_format($produto['preco_custo'], 2, ',', '.');?></td>
										<td>R$ <?=number_format($produto['preco_venda'], 2, ',', '.');?></td>
										<td>
											<div class="form-button-action">
												<button type="button" class="btn btn-link btn-primary open-verFoto-modal" data-imagem="<?=$produto['imagem'];?>" data-bs-toggle="modal" data-bs-target="#verFotoProductModal">
													<i class="fa fa-camera"></i>
												</button>
												<button type="button" class="btn btn-link btn-primary open-edit-modal" data-id="<?=$produto['id'];?>" data-nome="<?=$produto['nome'];?>" data-descricao="<?=$produto['descricao'];?>" data-preco_custo="<?=$produto['preco_custo'];?>" data-preco_venda="<?=$produto['preco_venda'];?>" data-quantidade="<?=$produto['quantidade'];?>" data-quantidade_critico="<?=$produto['quantidade_critico'];?>" data-bs-toggle="modal" data-bs-target="#editProductModal">
													<i class="fa fa-edit"></i>
												</button>
												<button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?=$produto['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
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
<!-- Modal de ver Foto do Produto -->
<div class="modal fade" id="verFotoProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ver Foto do Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="fotoProduto" src="default-image.png" alt="Foto do Produto" style="width: 100%">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
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
                <p>Tem certeza de que deseja excluir este produto?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="excluirProduto.php" method="POST">
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
								<span class="fw-light"> Produto </span>
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
                            <form action="cadastrarProduto.php" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Nome</label>
											<input id="nome" name="nome" type="text" class="form-control" required/>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Descrição</label>
											<input id="descricao" type="text" class="form-control" name="descricao"/>
										</div>
									</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>QNT Estoque</label>
											<input id="quantidade" type="number" class="form-control" name="quantidade" required/>
										</div>
									</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>QNT Estoque Crítico</label>
											<input id="quantidade_critico" type="number" class="form-control" name="quantidade_critico" required/>
										</div>
									</div>
									<div class="col-md-6 pe-0" style="display: none;">
    									<div class="form-group form-group-default">
        									<label>Combo?</label>
        									<input id="combo" type="checkbox" class="form-check-input" name="combo" onchange="toggleComboFields()"/>
    									</div>
									</div>
									<div class="col-md-6" id="quantidade-itens-combo" style="display: none;">
                						<div class="form-group form-group-default">
                    						<label>Quantidade de Itens no combo</label>
                    						<input id="qtd_itens_combo" type="number" class="form-control" name="qtd_itens_combo"/>
                						</div>
            						</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>Preço de custo</label>
											<input id="preco_custo" type="number" step="0.01" class="form-control" name="preco_custo" required/>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Preço de venda</label>
											<input id="preco_venda" type="number" step="0.01" class="form-control" name="preco_venda" required/>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Imagem</label>
											<input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
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
			<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    			<div class="modal-dialog" role="document">
        			<div class="modal-content">
            			<div class="modal-header border-0">
                			<h5 class="modal-title">
								<span class="fw-mediumbold"> Editar</span>
								<span class="fw-light"> Produto </span>
							</h5>
                			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            			</div>
            			<div class="modal-body">
                			<form id="editForm" action="editarProduto.php" method="POST" enctype="multipart/form-data">
                				<div class="row">
                    				<input type="hidden" name="id" id="productIdToEdit">
                    				<div class="col-sm-12">
                    					<div class="form-group form-group-default">
											<label>Nome</label>
                        					<input type="text" class="form-control" id="edit_nome" name="nome" required>
                    					</div>
                    				</div>
                    				<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Descrição</label>
											<input type="text" class="form-control" id="edit_descricao" name="descricao">
										</div>
									</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>QNT Estoque</label>
											<input type="number" class="form-control" id="edit_quantidade" name="quantidade" required>
										</div>
									</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>QNT Estoque Crítico</label>
											<input type="number" class="form-control" id="edit_quantidade_critico" name="quantidade_critico" required>
										</div>
									</div>
									<div class="col-md-6 pe-0">
										<div class="form-group form-group-default">
											<label>Preço de custo</label>
											<input type="number" step="0.01" class="form-control" id="edit_preco_custo" name="preco_custo" required>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group form-group-default">
											<label>Preço de venda</label>
											<input type="number" step="0.01" class="form-control" id="edit_preco_venda" name="preco_venda" required>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group form-group-default">
											<label>Imagem</label>
											<input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
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

	function toggleComboFields() {
    const comboCheckbox = document.getElementById('combo');
    const quantidadeItensDiv = document.getElementById('quantidade-itens-combo');
    
    if(comboCheckbox.checked) {
        quantidadeItensDiv.style.display = 'block';
        document.getElementById('qtd_itens_combo').setAttribute('required', '');
    } else {
        quantidadeItensDiv.style.display = 'none';
        document.getElementById('qtd_itens_combo').removeAttribute('required');
    }
}

// Chamar a função ao carregar a página para definir o estado inicial
document.addEventListener('DOMContentLoaded', toggleComboFields);
	// Abrir o modal e definir o ID do produto
    document.addEventListener('DOMContentLoaded', function () {
        const verFotoButtons = document.querySelectorAll('.open-verFoto-modal');
        const fotoProduto = document.getElementById('fotoProduto');

        // Verificar se os elementos existem antes de continuar
        if (verFotoButtons.length > 0 && fotoProduto) {
            verFotoButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const caminhoImagem = this.getAttribute('data-imagem');

                    if (caminhoImagem) {
                        fotoProduto.src = 'uploads/' + encodeURIComponent(caminhoImagem); // Escapar o caminho da imagem
                    } else {
                        console.error('Atributo data-imagem não encontrado ou está vazio.');
                        fotoProduto.src = 'default-image.png'; // Substitua pelo caminho da imagem padrão
                    }
                });
            });
        } else {
            console.error('Botões ou elemento da imagem não foram encontrados.');
        }
    });

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
</script>
<script>
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
        const productIdEdit = document.getElementById('productIdToEdit');
        const productNameEdit = document.getElementById('edit_nome');
        const productDescEdit = document.getElementById('edit_descricao');
        const productQuantityEdit = document.getElementById('edit_quantidade');
        const productCriticalEdit = document.getElementById('edit_quantidade_critico');
        const productCostEdit = document.getElementById('edit_preco_custo');
        const productSaleEdit = document.getElementById('edit_preco_venda');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                productIdEdit.value = this.getAttribute('data-id');
                productNameEdit.value = this.getAttribute('data-nome');
                productDescEdit.value = this.getAttribute('data-descricao');
                productQuantityEdit.value = this.getAttribute('data-quantidade');
                productCriticalEdit.value = this.getAttribute('data-quantidade_critico');
                productCostEdit.value = this.getAttribute('data-preco_custo');
                productSaleEdit.value = this.getAttribute('data-preco_venda');
            });
        });
    });
</script>

