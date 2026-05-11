<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

$filtro = $_GET['filtro'] ?? '';
$valor = $_GET['valor'] ?? '';
$pagina = $_GET['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Buscar produtos com paginação
$produtos = buscarProdutos($filtro, $valor, $limite, $offset);
$totalProdutos = contarProdutos($filtro, $valor);
$totalPaginas = ceil($totalProdutos / $limite);
?>

<div class="page-header">
    <h3 class="fw-bold mb-3">Gestão de Produtos</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="index.php">
                <i class="fa fa-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="fa fa-angle-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Estoque</a>
        </li>
        <li class="separator">
            <i class="fa fa-angle-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Listar Produtos</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row card-tools-still-right">
                    <div class="card-title">Catálogo de Produtos</div>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i> Novo Produto
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" style="width: 80px">ID</th>
                                <th scope="col" class="text-start">Produto</th>
                                <th scope="col" class="text-center">Estoque</th>
                                <th scope="col" class="text-end">Preço Custo</th>
                                <th scope="col" class="text-end">Preço Venda</th>
                                <th scope="col" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($produtos): ?>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td>#<?= $produto['id']; ?></td>
                                        <td class="text-start">
                                            <div class="d-flex align-items-center">
                                                <?php if($produto['imagem']): ?>
                                                    <img src="uploads/<?= $produto['imagem'] ?>" class="avatar-sm rounded-circle me-2" style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="avatar-sm rounded-circle me-2 bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fa fa-box text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <span class="fw-bold"><?= $produto['nome']; ?></span>
                                                    <div class="small text-muted"><?= substr($produto['descricao'], 0, 30); ?><?= strlen($produto['descricao']) > 30 ? '...' : '' ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $qtd = (int)$produto['quantidade'];
                                            $critico = (int)$produto['quantidade_critico'];
                                            if ($qtd <= 0) {
                                                echo '<span class="badge badge-danger">Sem Estoque</span>';
                                            } elseif ($qtd <= $critico) {
                                                echo '<span class="badge badge-warning">Estoque Crítico (' . $qtd . ')</span>';
                                            } else {
                                                echo '<span class="badge badge-success">' . $qtd . ' em unidades</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end fw-bold">R$ <?= number_format($produto['preco_custo'], 2, ',', '.'); ?></td>
                                        <td class="text-end fw-bold text-primary">R$ <?= number_format($produto['preco_venda'], 2, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info open-verFoto-modal" data-imagem="<?= $produto['imagem']; ?>" data-bs-toggle="modal" data-bs-target="#verFotoProductModal" title="Ver Foto">
                                                    <i class="fa fa-camera"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-primary open-edit-modal" 
                                                    data-id="<?= $produto['id']; ?>" 
                                                    data-nome="<?= $produto['nome']; ?>" 
                                                    data-descricao="<?= $produto['descricao']; ?>" 
                                                    data-preco_custo="<?= $produto['preco_custo']; ?>" 
                                                    data-preco_venda="<?= $produto['preco_venda']; ?>" 
                                                    data-quantidade="<?= $produto['quantidade']; ?>" 
                                                    data-quantidade_critico="<?= $produto['quantidade_critico']; ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#editProductModal" title="Editar">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?= $produto['id']; ?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" title="Excluir">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center p-5 text-muted">Nenhum produto encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($totalPaginas > 1): ?>
                <div class="card-footer">
                    <ul class="pagination pg-primary mb-0 justify-content-end">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?page=ListarProdutos&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modais (Cadastro, Edição, Exclusão, Foto) -->
<!-- Modal de ver Foto do Produto -->
<div class="modal fade" id="verFotoProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Foto do Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="fotoProduto" src="default-image.png" alt="Foto do Produto" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger fw-bold">Excluir Produto?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta ação não pode ser desfeita. Deseja continuar?</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" action="excluirProduto.php" method="POST">
                    <input type="hidden" name="id" id="productIdToDelete">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cadastro -->
<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cadastrar Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="cadastrarProduto.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome do Produto</label>
                            <input name="nome" type="text" class="form-control" required placeholder="Ex: Anel de Prata">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço de Venda (R$)</label>
                            <input name="preco_venda" type="number" step="0.01" class="form-control" required placeholder="0,00">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="2" placeholder="Detalhes do produto..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preço de Custo</label>
                            <input name="preco_custo" type="number" step="0.01" class="form-control" required placeholder="0,00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Qtd Inicial</label>
                            <input name="quantidade" type="number" class="form-control" required value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Qtd Crítica</label>
                            <input name="quantidade_critico" type="number" class="form-control" required value="2">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Imagem</label>
                            <input type="file" class="form-control" name="imagem" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0 pb-0 mt-4">
                        <button type="submit" class="btn btn-primary btn-round">Salvar Produto</button>
                        <button type="button" class="btn btn-light btn-round" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="editarProduto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="productIdToEdit">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço de Venda (R$)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_preco_venda" name="preco_venda" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" id="edit_descricao" name="descricao" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preço de Custo</label>
                            <input type="number" step="0.01" class="form-control" id="edit_preco_custo" name="preco_custo" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estoque Atual</label>
                            <input type="number" class="form-control" id="edit_quantidade" name="quantidade" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estoque Crítico</label>
                            <input type="number" class="form-control" id="edit_quantidade_critico" name="quantidade_critico" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nova Imagem</label>
                            <input type="file" class="form-control" name="imagem" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0 pb-0 mt-4">
                        <button type="submit" class="btn btn-primary btn-round">Atualizar Produto</button>
                        <button type="button" class="btn btn-light btn-round" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Foto
    const verFotoButtons = document.querySelectorAll('.open-verFoto-modal');
    const fotoProduto = document.getElementById('fotoProduto');
    verFotoButtons.forEach(button => {
        button.addEventListener('click', function () {
            const caminhoImagem = this.getAttribute('data-imagem');
            fotoProduto.src = caminhoImagem ? 'uploads/' + encodeURIComponent(caminhoImagem) : 'default-image.png';
        });
    });

    // Exclusão
    const deleteButtons = document.querySelectorAll('.open-delete-modal');
    const productIdInput = document.getElementById('productIdToDelete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            productIdInput.value = this.getAttribute('data-id');
        });
    });

    // Edição
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
