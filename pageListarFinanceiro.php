<?php
/**
 * pageListarFinanceiro.php
 * Tela de listagem e controle de lançamentos financeiros.
 * Inclui filtros robustos e segue o layout padrão premium do sistema.
 */
require_once 'config.php';
require_once 'funcoes.php';
require_once 'auth.php';
verificarSessao();

// Coleta de parâmetros dos filtros robustos
$tipo_lancamento = $_GET['tipo_lancamento'] ?? '';
$conta           = $_GET['conta'] ?? '';
$pago            = $_GET['pago'] ?? '';
$data_inicio     = $_GET['data_inicio'] ?? '';
$data_fim        = $_GET['data_fim'] ?? '';
$busca           = $_GET['busca'] ?? '';

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Buscar os dados com os novos filtros robustos
$financeiros = buscarFinanceiro($limite, $offset, $tipo_lancamento, $conta, $pago, $data_inicio, $data_fim, $busca);
$totalFinanceiro = contarFinanceiro($tipo_lancamento, $conta, $pago, $data_inicio, $data_fim, $busca);
$totalPaginas = ceil($totalFinanceiro / $limite);
?>

<div class="page-header">
    <h3 class="fw-bold mb-3">Financeiro</h3>
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
            <a href="#">Financeiro</a>
        </li>
        <li class="separator">
            <i class="fa fa-angle-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Listar Lançamentos</a>
        </li>
    </ul>
</div>

<!-- Filtros Robustos -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-round shadow-sm border-0 bg-white">
            <div class="card-body p-3">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="ListarFinanceiro">
                    <div class="row g-2 align-items-end">
                        <!-- Busca Textual -->
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Buscar por texto</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="busca" class="form-control form-control-sm border-start-0" placeholder="Descrição, valor ou ID..." value="<?= htmlspecialchars($busca) ?>">
                                </div>
                            </div>
                        </div>
                        <!-- Tipo de Lançamento -->
                        <div class="col-md-2 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Tipo</label>
                                <select name="tipo_lancamento" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1" <?= ($tipo_lancamento == '1' ? 'selected' : '') ?>>Entrada</option>
                                    <option value="2" <?= ($tipo_lancamento == '2' ? 'selected' : '') ?>>Saída</option>
                                    <option value="3" <?= ($tipo_lancamento == '3' ? 'selected' : '') ?>>Estorno</option>
                                </select>
                            </div>
                        </div>
                        <!-- Conta -->
                        <div class="col-md-2 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Conta</label>
                                <select name="conta" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    <?php
                                    $contasObj = buscarTodasContasFinanceiro();
                                    if (!empty($contasObj)) {
                                        foreach ($contasObj as $c) {
                                            $selected = ($conta == $c['id'] ? 'selected' : '');
                                            echo "<option value='" . htmlspecialchars($c['id']) . "' {$selected}>" . htmlspecialchars($c['nome']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!-- Período de Vencimento -->
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Período (Vencimento)</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($data_inicio) ?>">
                                    <span class="input-group-text bg-light border-0 py-0 px-2" style="font-size:11px;">até</span>
                                    <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= htmlspecialchars($data_fim) ?>">
                                </div>
                            </div>
                        </div>
                        <!-- Status de Pagamento -->
                        <div class="col-md-1 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Status</label>
                                <select name="pago" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1" <?= ($pago == '1' ? 'selected' : '') ?>>Pago</option>
                                    <option value="0" <?= ($pago == '0' ? 'selected' : '') ?>>Aberto</option>
                                </select>
                            </div>
                        </div>
                        <!-- Ações do Filtro -->
                        <div class="col-md-1 col-sm-6 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill py-2" title="Aplicar Filtros"><i class="fas fa-filter"></i></button>
                            <a href="index.php?page=ListarFinanceiro" class="btn btn-secondary btn-sm flex-fill py-2" title="Limpar Filtros"><i class="fas fa-eraser"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Lançamentos -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-round shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="card-title fw-bold mb-1" style="font-size: 1.1rem; color:#1a1a2e;">Lançamentos Financeiros</h4>
                        <p class="text-muted small mb-0">Total de registros encontrados: <strong><?= $totalFinanceiro ?></strong></p>
                    </div>
                    <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addRowModal">
                        <i class="fa fa-plus me-1"></i> Cadastrar Lançamento
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0 table-striped table-hover">
                        <thead class="thead-light">
                            <tr class="text-uppercase font-monospace fs-7 text-secondary">
                                <th style="width: 80px" class="ps-4">ID</th>
                                <th style="width: 80px" class="text-center">Tipo</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Conta</th>
                                <th>Vencimento</th>
                                <th>Pago</th>
                                <th style="width: 100px" class="pe-4 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($financeiros): ?>
                                <?php foreach ($financeiros as $financeiro): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?=$financeiro['id'];?></td>
                                        <td class="text-center">
                                            <?php if ($financeiro['tipo'] == '1'): ?>
                                                <span class="badge badge-success rounded-pill px-2 py-1" title="Entrada"><i class="fas fa-arrow-up me-1"></i>Entrada</span>
                                            <?php elseif ($financeiro['tipo'] == '2'): ?>
                                                <span class="badge badge-danger rounded-pill px-2 py-1" title="Saída"><i class="fas fa-arrow-down me-1"></i>Saída</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning rounded-pill px-2 py-1" title="Estorno"><i class="fas fa-arrow-left me-1"></i>Estorno</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?=htmlspecialchars($financeiro['descricao']);?></strong></td>
                                        <td class="fw-bold <?=($financeiro['tipo'] == '1' ? 'text-success' : ($financeiro['tipo'] == '2' ? 'text-danger' : 'text-warning'))?>">
                                            R$ <?=number_format($financeiro['valor'], 2, ',', '.');?>
                                        </td>
                                        <td><i class="fas fa-university text-muted me-1"></i><?=htmlspecialchars($financeiro['nome_conta'] ?? 'N/A');?></td>
                                        <td><i class="far fa-calendar-alt text-muted me-1"></i><?= date('d/m/Y', strtotime($financeiro['data_vencimento'])) ?></td>
                                        <td>
                                            <?php if ($financeiro['pago'] == 1): ?>
                                                <span class="badge badge-success"><i class="fas fa-check me-1"></i>Sim</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fas fa-times me-1"></i>Não</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <?php if ($financeiro['criado_manual'] == true): ?>
                                                <button type="button" class="btn btn-icon btn-link btn-danger open-delete-modal p-0" 
                                                        data-id="<?=$financeiro['id'];?>" 
                                                        data-tipo="<?=$financeiro['tipo'];?>" 
                                                        data-conta="<?=$financeiro['conta'];?>" 
                                                        data-valor="<?=$financeiro['valor'];?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmDeleteModal"
                                                        title="Excluir Lançamento">
                                                    <i class="fa fa-times fa-lg"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small italic">Automático</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Nenhum lançamento financeiro encontrado com os filtros aplicados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($totalPaginas > 1): ?>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        <ul class="pagination pg-primary mb-0">
                            <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=1&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&pago=<?= urlencode($pago) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&laquo; First</a>
                            </li>
                            <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= ($pagina - 1) ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&pago=<?= urlencode($pago) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&lsaquo;</a>
                            </li>
                            
                            <?php 
                            $paginas_visiveis = 5;
                            $inicio = max(1, $pagina - floor($paginas_visiveis/2));
                            $fim = min($totalPaginas, $inicio + $paginas_visiveis - 1);
                            $inicio = max(1, $fim - $paginas_visiveis + 1);
                            
                            for ($i = $inicio; $i <= $fim; $i++): 
                            ?>
                                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&pago=<?= urlencode($pago) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= ($pagina + 1) ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&pago=<?= urlencode($pago) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&rsaquo;</a>
                            </li>
                            <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= $totalPaginas ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&pago=<?= urlencode($pago) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&raquo; Last</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir permanentemente este lançamento financeiro? Essa operação alterará o saldo da conta associada.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" action="formExcluir.php" method="POST">
                    <input type="hidden" name="id" id="productIdToDelete">
                    <input type="hidden" name="funcao" value="ExcluirFinanceiro">
                    <input type="hidden" name="tabela" value="financeiro">
                    <input type="hidden" name="page" value="ListarFinanceiro">
                    <input type="hidden" name="tipo" id="tipoLancamento">
                    <input type="hidden" name="conta" id="contaLancamento">
                    <input type="hidden" name="valor" id="valorLancamento">
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Excluir</button>
                </form>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cadastro -->
<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border:none;">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Novo Lançamento Financeiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="cadastrarFinanceiro.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Descrição / Nome</label>
                                <input id="nome" name="nome" type="text" class="form-control border-0 px-1" placeholder="Ex: Pagamento fornecedor, Venda de balcão..." required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Tipo</label>
                                <select class="form-select border-0 px-1" id="tipo" name="tipo" required>
                                    <option value="">Selecione</option>
                                    <option value="1">Entrada</option>
                                    <option value="2">Saída</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Valor R$</label>
                                <input id="valor" type="number" step="0.01" class="form-control border-0 px-1" name="valor" placeholder="0,00" required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Conta</label>
                                <select class="form-select border-0 px-1" id="conta" name="conta" required>
                                    <option value="">Selecione</option>
                                    <?php
                                    $resultado = buscarTodasContasFinanceiro();
                                    if (!empty($resultado)) {
                                        foreach ($resultado as $contaObj) {
                                            echo "<option value='" . htmlspecialchars($contaObj['id']) . "'>" . htmlspecialchars($contaObj['nome']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>Nenhuma conta encontrada</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Data Vencimento</label>
                                <input id="data_vencimento" type="date" class="form-control border-0 px-1" name="data_vencimento" value="<?= date('Y-m-d') ?>" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar</button>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.open-delete-modal');
    const productIdInput = document.getElementById('productIdToDelete');
    const tipoLancamentoInput = document.getElementById('tipoLancamento');
    const contaLancamentoInput = document.getElementById('contaLancamento');
    const valorLancamentoInput = document.getElementById('valorLancamento');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            productIdInput.value = this.getAttribute('data-id');
            tipoLancamentoInput.value = this.getAttribute('data-tipo');
            contaLancamentoInput.value = this.getAttribute('data-conta');
            valorLancamentoInput.value = this.getAttribute('data-valor');
        });
    });
});
</script>
