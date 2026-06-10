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

    // Buscar Despesas Fixas com paginação e filtros
    $despesasFixas = buscarDespesasFixas($filtro, $valor, $limite, $offset);
    $totalDespesasFixas = contarDespesasFixas($filtro, $valor);
    $totalPaginas = ceil($totalDespesasFixas / $limite);
?>

<div class="page-inner">
    <!-- Cabeçalho de Página -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Despesas Fixas Mensais</h3>
            <h6 class="op-7 mb-2">Gerencie e visualize as despesas fixas recorrentes do sistema</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addRowModal">
                <i class="fa fa-plus me-2"></i>
                Cadastrar Despesa
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header pb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="card-title">Despesas Cadastradas</div>
                    </div>
                    
                    <!-- Formulário de Filtro -->
                    <form method="GET" class="mt-3">
                        <input type="hidden" name="page" value="ListarDespesasFixas">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group pb-0">
                                    <label class="form-label mb-1">Filtrar por</label>
                                    <select name="filtro" class="form-select form-select-sm">
                                        <option value="">Selecione</option>
                                        <option value="descricao" <?= ($filtro == 'descricao' ? 'selected' : '' )?>>Descrição</option>
                                        <option value="valor" <?= ($filtro == 'valor' ? 'selected' : '' )?>>Valor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group pb-0">
                                    <label class="form-label mb-1">Valor da busca</label>
                                    <input type="text" name="valor" class="form-control form-control-sm" placeholder="Digite o termo para buscar..." value="<?= htmlspecialchars($valor) ?>">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2 mt-2 mt-md-0 pb-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-search me-1"></i> Filtrar
                                </button>
                                <a href="?page=ListarDespesasFixas" class="btn btn-secondary btn-sm flex-fill text-center">
                                    <i class="fas fa-eraser me-1"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="add-row" class="display table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 80px">ID</th>
                                    <th>Descrição</th>
                                    <th>Data de Lançamento</th>
                                    <th style="width: 150px" class="text-end">Valor</th>
                                    <th style="width: 120px" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total Geral:</td>
                                    <td class="text-end text-danger font-weight-bold">
                                        <?php
                                            $somaDespesasFixas = BuscarSomaPorTabela('despesasfixas', 'valor', $filtro, $valor);
                                        ?>
                                        R$ <?=$somaDespesasFixas !== null ? number_format($somaDespesasFixas, 2, ',', '.') : '0,00';?>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                    if ($despesasFixas)
                                    {
                                        foreach ($despesasFixas as $despesas)
                                        {
                                ?>
                                            <tr>
                                                <td class="fw-bold text-primary">#<?=$despesas['id'];?></td>
                                                <td><?=htmlspecialchars($despesas['descricao']);?></td>
                                                <td>
                                                    <span class="badge badge-count"><?= !empty($despesas['data_lancamento']) ? date('d/m/Y H:i', strtotime($despesas['data_lancamento'])) : '-' ?></span>
                                                </td>
                                                <td class="text-end fw-bold text-danger">R$ <?=number_format($despesas['valor'], 2, ',', '.');?></td>
                                                <td class="text-center">
                                                    <div class="form-button-action">
                                                        <button type="button" class="btn btn-link btn-primary btn-lg open-edit-modal" data-id="<?=$despesas['id'];?>" data-descricao="<?=$despesas['descricao'];?>" data-valor="<?=$despesas['valor'];?>" data-bs-toggle="modal" data-bs-target="#editDespesaFixaModal" data-bs-toggle="tooltip" title="Editar">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-link btn-danger btn-lg open-delete-modal" data-id="<?=$despesas['id'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-bs-toggle="tooltip" title="Excluir">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                <?php
                                        }
                                    } else {
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Nenhuma despesa fixa cadastrada</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($totalPaginas > 1): ?>
                    <div class="card-footer">
                        <ul class="pagination pg-primary mb-0 justify-content-end">
                            <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarDespesasFixas&pagina=1&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>" aria-label="Primeira">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=ListarDespesasFixas&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarDespesasFixas&pagina=<?= $totalPaginas ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>" aria-label="Última">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content card-round">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir o cadastro dessa despesa fixa?</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" action="excluirDespesaFixa.php" method="POST">
                    <input type="hidden" name="id" id="productIdToDelete">
                    <button type="submit" class="btn btn-danger btn-round">Excluir</button>
                    <button type="button" class="btn btn-light btn-round" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cadastro -->
<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content card-round">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <span class="fw-bold">Nova Despesa Fixa</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="cadastrarDespesaFixa.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Descrição</label>
                                <input id="nome" name="nome" type="text" class="form-control" placeholder="Ex: Aluguel, Internet..." required/>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-group form-group-default">
                                <label>Valor R$</label>
                                <input id="valor" type="number" step="0.01" class="form-control" name="valor" placeholder="0,00" required />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0">
                        <button type="submit" class="btn btn-primary btn-round">Salvar</button>
                        <button type="button" class="btn btn-danger btn-round" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editDespesaFixaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content card-round">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <span class="fw-bold">Editar Despesa Fixa</span>
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
                                <input type="text" class="form-control" id="edit_descricao" name="descricao" required>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-group form-group-default">
                                <label>Valor R$</label>
                                <input type="number" step="0.01" class="form-control" id="edit_valor" name="valor" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0">
                        <button type="submit" class="btn btn-primary btn-round">Salvar</button>
                        <button type="button" class="btn btn-danger btn-round" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
        
        // Inicializar tooltips do Bootstrap
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
