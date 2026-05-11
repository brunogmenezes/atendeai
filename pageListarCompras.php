<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

// Definir filtros e paginação
$pagina = $_GET['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Tentar buscar compras (Assumindo que a tabela existe ou será criada)
try {
    $compras = buscarTabela('compras', '', '', $limite, $offset, 'DESC');
    $totalCompras = contarNumeroPorTabela('compras');
    $totalPaginas = ceil($totalCompras / $limite);
} catch (Exception $e) {
    $compras = [];
    $totalPaginas = 0;
    $erro_db = "Tabela de compras não encontrada ou erro no banco: " . $e->getMessage();
}
?>

<div class="page-header">
    <h3 class="fw-bold mb-3">Gestão de Compras</h3>
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
            <a href="#">Compras</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row card-tools-still-right">
                    <div class="card-title">Listagem de Entradas / Compras</div>
                    <div class="card-tools">
                        <a href="index.php?page=AdicionarCompra" class="btn btn-primary btn-round">
                            <i class="fa fa-plus"></i> Nova Compra
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (isset($erro_db)): ?>
                    <div class="alert alert-warning m-3">
                        <i class="fa fa-exclamation-triangle me-2"></i> 
                        <?= $erro_db ?>
                        <br><br>
                        <button class="btn btn-sm btn-dark" onclick="window.location.reload()">Criar Tabela (Simulação)</button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col" class="text-start">Data</th>
                                <th scope="col" class="text-start">Fornecedor</th>
                                <th scope="col" class="text-end">Total</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($compras)): ?>
                                <tr>
                                    <td colspan="6" class="text-center p-5 text-muted">
                                        <i class="fa fa-shopping-cart fa-3x mb-3 d-block"></i>
                                        Nenhuma compra registrada até o momento.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($compras as $compra): ?>
                                    <tr>
                                        <td>#<?= $compra['id'] ?></td>
                                        <td class="text-start"><?= date('d/m/Y H:i', strtotime($compra['created_at'])) ?></td>
                                        <td class="text-start"><?= htmlspecialchars($compra['fornecedor'] ?? 'Não informado') ?></td>
                                        <td class="text-end fw-bold">R$ <?= number_format($compra['total'], 2, ',', '.') ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-success">Recebido</span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-icon btn-link btn-primary btn-view-compra" data-id="<?= $compra['id'] ?>">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button class="btn btn-icon btn-link btn-danger btn-delete-compra" data-id="<?= $compra['id'] ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
                            <a class="page-link" href="index.php?page=ListarCompras&pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para Detalhes da Compra -->
<div class="modal fade" id="modalDetalhesCompra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detalhes da Compra <span id="view-compra-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Fornecedor:</p>
                        <h6 class="fw-bold" id="view-fornecedor"></h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Data:</p>
                        <h6 class="fw-bold" id="view-data"></h6>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Custo Diluído</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="view-itens-body">
                            <!-- Itens via JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">TOTAL GERAL:</th>
                                <th class="text-end text-primary h5 fw-bold" id="view-total"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalhesCompra'));
    
    // Visualizar Detalhes
    document.querySelectorAll('.btn-view-compra').forEach(btn => {
        btn.addEventListener('click', function() {
            const compraId = this.dataset.id;
            fetch('buscar_itens_compra.php?id=' + compraId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('view-compra-id').textContent = '#' + compraId;
                        document.getElementById('view-fornecedor').textContent = data.compra.fornecedor || 'Não informado';
                        document.getElementById('view-data').textContent = data.compra.data;
                        document.getElementById('view-total').textContent = 'R$ ' + parseFloat(data.compra.total).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        const body = document.getElementById('view-itens-body');
                        body.innerHTML = '';
                        data.itens.forEach(item => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${item.nome}</td>
                                <td class="text-center">${item.quantidade}</td>
                                <td class="text-end">R$ ${parseFloat(item.preco_custo).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                                <td class="text-end">R$ ${parseFloat(item.subtotal).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                            `;
                            body.appendChild(row);
                        });
                        modal.show();
                    }
                });
        });
    });

    // Excluir/Estornar Compra
    document.querySelectorAll('.btn-delete-compra').forEach(btn => {
        btn.addEventListener('click', function() {
            const compraId = this.dataset.id;
            
            swal({
                title: "Estornar Compra?",
                text: "Isso removerá os itens do estoque e devolverá o valor para a conta financeira. Esta ação não pode ser desfeita!",
                icon: "warning",
                buttons: {
                    cancel: "Não, cancelar",
                    confirm: {
                        text: "Sim, estornar",
                        value: true,
                        visible: true,
                        className: "btn-danger",
                        closeModal: false
                    }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    fetch('excluir_compra.php?id=' + compraId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                swal("Estornado!", data.message, "success").then(() => {
                                    window.location.reload();
                                });
                            } else {
                                swal("Erro!", data.message, "error");
                            }
                        })
                        .catch(err => {
                            swal("Erro!", "Falha na comunicação com o servidor.", "error");
                        });
                }
            });
        });
    });
});
</script>
