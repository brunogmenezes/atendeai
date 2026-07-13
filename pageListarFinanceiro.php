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
$categoria_id    = $_GET['categoria_id'] ?? '';
$data_inicio     = $_GET['data_inicio'] ?? date('Y-m-01');  // Padrão: primeiro dia do mês atual
$data_fim        = $_GET['data_fim']     ?? date('Y-m-d');  // Padrão: hoje
$busca           = $_GET['busca'] ?? '';

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Buscar os dados com os novos filtros robustos
$financeiros = buscarFinanceiro($limite, $offset, $tipo_lancamento, $conta, $categoria_id, $data_inicio, $data_fim, $busca);
$totalFinanceiro = contarFinanceiro($tipo_lancamento, $conta, $categoria_id, $data_inicio, $data_fim, $busca);
$totalPaginas = ceil($totalFinanceiro / $limite);

// Totalizadores do período
$totais = totalizarFinanceiro($tipo_lancamento, $conta, $categoria_id, $data_inicio, $data_fim);

// Categorias para o modal
$categoriasEntrada = buscarCategoriasFinanceiro(1);
$categoriasSaida   = buscarCategoriasFinanceiro(2);
$todasCategorias   = buscarCategoriasFinanceiro();

// Dados para gráfico de pizza de saídas
$saidasPorCategoria = buscarSaidasPorCategoria($data_inicio, $data_fim, $conta);
$chartLabels = json_encode(array_column($saidasPorCategoria, 'categoria'));
$chartValues = json_encode(array_map(fn($r) => (float)$r['total'], $saidasPorCategoria));
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

<!-- Cards de Resumo -->
<div class="row">
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-arrow-up"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0"><p class="card-category text-muted">Entradas</p><h4 class="card-title">R$ <?= number_format($totais['total_entradas'] ?? 0, 2, ',', '.') ?></h4></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-arrow-down"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0"><p class="card-category text-muted">Saídas</p><h4 class="card-title">R$ <?= number_format($totais['total_saidas'] ?? 0, 2, ',', '.') ?></h4></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-balance-scale"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0"><p class="card-category text-muted">Saldo Período</p><h4 class="card-title">R$ <?= number_format($totais['saldo'] ?? 0, 2, ',', '.') ?></h4></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros Robustos -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-round shadow-sm border-0 bg-white">
            <div class="card-body p-3">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="ListarFinanceiro">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Buscar por texto</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="busca" class="form-control form-control-sm border-start-0" placeholder="Descrição, valor ou ID..." value="<?= htmlspecialchars($busca) ?>">
                                </div>
                            </div>
                        </div>
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
                        <div class="col-md-3 col-sm-6">
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
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Período (Lançamento)</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($data_inicio) ?>">
                                    <span class="input-group-text bg-light border-0 py-0 px-2" style="font-size:11px;">até</span>
                                    <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= htmlspecialchars($data_fim) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1 col-sm-6 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill py-2" title="Aplicar Filtros"><i class="fas fa-filter"></i></button>
                            <a href="index.php?page=ListarFinanceiro" class="btn btn-secondary btn-sm flex-fill py-2" title="Limpar Filtros"><i class="fas fa-eraser"></i></a>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group mb-0 p-0">
                                <label class="small text-muted fw-bold mb-1">Categoria</label>
                                <select name="categoria_id" class="form-select form-select-sm">
                                    <option value="">Todas as categorias</option>
                                    <optgroup label="── Entrada">
                                    <?php foreach ($categoriasEntrada as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id'] ? 'selected' : '') ?>><?= htmlspecialchars($cat['nome']) ?></option>
                                    <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="── Saída">
                                    <?php foreach ($categoriasSaida as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id'] ? 'selected' : '') ?>><?= htmlspecialchars($cat['nome']) ?></option>
                                    <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de Pizza: Saídas por Categoria -->
<div class="row mb-4">
    <div class="col-md-5">
        <div class="card card-round shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:linear-gradient(135deg,#ff6b6b,#ee5a24);">
                        <i class="fas fa-chart-pie text-white" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color:#1a1a2e;">Saídas por Categoria</h6>
                        <small class="text-muted">Distribuição dos gastos</small>
                    </div>
                </div>
            </div>
            <div class="card-body px-3 py-2 d-flex justify-content-center align-items-center">
                <?php if (!empty($saidasPorCategoria)): ?>
                <div style="position:relative;width:100%;max-width:320px;">
                    <canvas id="chartSaidasCategoria" style="max-height:280px;"></canvas>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-chart-pie fa-3x mb-3" style="opacity:.2;"></i>
                    <p class="mb-0 small">Nenhuma saída com categoria registrada no período.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card card-round shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:linear-gradient(135deg,#6c5ce7,#a29bfe);">
                        <i class="fas fa-list-ul text-white" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color:#1a1a2e;">Detalhamento</h6>
                        <small class="text-muted">Valor por categoria</small>
                    </div>
                </div>
            </div>
            <div class="card-body px-4 py-2">
                <?php if (!empty($saidasPorCategoria)):
                    $totalSaidas = array_sum(array_column($saidasPorCategoria, 'total'));
                    $cores = ['#e74c3c','#e67e22','#f39c12','#8e44ad','#2980b9','#27ae60','#16a085','#d35400','#c0392b','#7f8c8d'];
                    $i = 0;
                    foreach ($saidasPorCategoria as $s):
                        $pct = $totalSaidas > 0 ? round(($s['total'] / $totalSaidas) * 100, 1) : 0;
                        $cor = $cores[$i % count($cores)];
                        $i++;
                ?>
                <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded" style="display:inline-block;width:12px;height:12px;background:<?= $cor ?>;flex-shrink:0;"></span>
                        <span class="small fw-semibold" style="color:#2d3436;"><?= htmlspecialchars($s['categoria']) ?></span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-danger small">R$ <?= number_format($s['total'], 2, ',', '.') ?></span>
                        <span class="badge ms-1" style="background:#fff0f0;color:#e74c3c;font-size:10px;"><?= $pct ?>%</span>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="text-center py-4 text-muted small">Nenhum dado disponível.</div>
                <?php endif; ?>
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
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Conta</th>
                                <th>Lançamento</th>
                                <th style="width: 130px" class="pe-4 text-end">Ações</th>
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
                                        <td>
                                            <?php if (!empty($financeiro['nome_categoria'])): ?>
                                                <span class="badge rounded-pill px-2 py-1" style="background:#f0f0ff;color:#6c5ce7;font-size:11px;">
                                                    <i class="fas fa-tag me-1" style="font-size:9px;"></i><?= htmlspecialchars($financeiro['nome_categoria']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?=htmlspecialchars($financeiro['descricao']);?></strong></td>
                                        <td class="fw-bold <?=($financeiro['tipo'] == '1' ? 'text-success' : ($financeiro['tipo'] == '2' ? 'text-danger' : 'text-warning'))?>">
                                            R$ <?=number_format($financeiro['valor'], 2, ',', '.');?>
                                        </td>
                                        <td><i class="fas fa-university text-muted me-1"></i><?=htmlspecialchars($financeiro['nome_conta'] ?? 'N/A');?></td>
                                        <td>
                                            <i class="far fa-calendar-alt text-muted me-1"></i><?= date('d/m/Y', strtotime($financeiro['data_lancamento'])) ?>
                                            <br><small class="text-muted"><i class="far fa-clock me-1"></i><?= date('H:i', strtotime($financeiro['data_lancamento'])) ?></small>
                                            <?php if (!empty($financeiro['nome_usuario']) || !empty($financeiro['username_usuario'])): ?>
                                            <br><small class="text-muted"><i class="far fa-user me-1"></i><?= htmlspecialchars($financeiro['nome_usuario'] ?: $financeiro['username_usuario']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <?php if ($financeiro['criado_manual'] == true): ?>
                                                <button type="button" class="btn btn-icon btn-link btn-primary open-edit-modal p-0 me-1"
                                                        data-id="<?= $financeiro['id'] ?>"
                                                        data-nome="<?= htmlspecialchars($financeiro['descricao']) ?>"
                                                        data-tipo="<?= $financeiro['tipo'] ?>"
                                                        data-valor="<?= $financeiro['valor'] ?>"
                                                        data-conta="<?= $financeiro['conta'] ?>"
                                                        data-categoria="<?= $financeiro['categoria_id'] ?? '' ?>"
                                                        data-vencimento="<?= substr($financeiro['data_vencimento'] ?? '', 0, 10) ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editRowModal"
                                                        title="Editar Lançamento">
                                                    <i class="fa fa-edit fa-lg"></i>
                                                </button>
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
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=1&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&categoria_id=<?= urlencode($categoria_id) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&laquo; Início</a>
                            </li>
                            <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= ($pagina - 1) ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&categoria_id=<?= urlencode($categoria_id) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&lsaquo;</a>
                            </li>
                            <?php 
                            $paginas_visiveis = 5;
                            $inicio = max(1, $pagina - floor($paginas_visiveis/2));
                            $fim = min($totalPaginas, $inicio + $paginas_visiveis - 1);
                            $inicio = max(1, $fim - $paginas_visiveis + 1);
                            for ($i = $inicio; $i <= $fim; $i++): ?>
                                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&categoria_id=<?= urlencode($categoria_id) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= ($pagina + 1) ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&categoria_id=<?= urlencode($categoria_id) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&rsaquo;</a>
                            </li>
                            <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=ListarFinanceiro&pagina=<?= $totalPaginas ?>&busca=<?= urlencode($busca) ?>&tipo_lancamento=<?= urlencode($tipo_lancamento) ?>&conta=<?= urlencode($conta) ?>&categoria_id=<?= urlencode($categoria_id) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_fim=<?= urlencode($data_fim) ?>">&raquo; Fim</a>
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

<!-- Modal de Edição -->
<div class="modal fade" id="editRowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border:none;">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-edit text-primary me-2"></i>Editar Lançamento Financeiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="editarFinanceiro.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Descrição / Nome</label>
                                <input id="edit_nome" name="nome" type="text" class="form-control border-0 px-1" placeholder="Descrição..." required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Tipo</label>
                                <select class="form-select border-0 px-1" id="edit_tipo" name="tipo" required>
                                    <option value="1">Entrada</option>
                                    <option value="2">Saída</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Categoria</label>
                                <select class="form-select border-0 px-1" id="edit_categoria_id" name="categoria_id">
                                    <option value="">Sem categoria</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Valor R$</label>
                                <input id="edit_valor" type="number" step="0.01" class="form-control border-0 px-1" name="valor" placeholder="0,00" required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Conta</label>
                                <select class="form-select border-0 px-1" id="edit_conta" name="conta" required>
                                    <?php
                                    $contasEdit = buscarTodasContasFinanceiro();
                                    if (!empty($contasEdit)) {
                                        foreach ($contasEdit as $ce) {
                                            echo "<option value='" . htmlspecialchars($ce['id']) . "'>" . htmlspecialchars($ce['nome']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default p-2">
                                <label class="fw-bold small text-muted">Data Vencimento</label>
                                <input id="edit_vencimento" type="date" class="form-control border-0 px-1" name="data_vencimento" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Alterações</button>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
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
                                <label class="fw-bold small text-muted">Categoria</label>
                                <select class="form-select border-0 px-1" id="categoria_id" name="categoria_id">
                                    <option value="">Selecione o tipo primeiro</option>
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
// Categorias carregadas do PHP
const categoriasEntrada = <?= json_encode(array_map(fn($c) => ['id' => $c['id'], 'nome' => $c['nome']], $categoriasEntrada)) ?>;
const categoriasSaida   = <?= json_encode(array_map(fn($c) => ['id' => $c['id'], 'nome' => $c['nome']], $categoriasSaida)) ?>;

document.addEventListener('DOMContentLoaded', function () {
    // --- Modal de exclusão ---
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

    // --- Modal de edição ---
    const editButtons = document.querySelectorAll('.open-edit-modal');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id').value        = this.dataset.id;
            document.getElementById('edit_nome').value      = this.dataset.nome;
            document.getElementById('edit_valor').value     = this.dataset.valor;
            document.getElementById('edit_vencimento').value = this.dataset.vencimento;

            const editTipo = document.getElementById('edit_tipo');
            editTipo.value = this.dataset.tipo;

            const editConta = document.getElementById('edit_conta');
            editConta.value = this.dataset.conta;

            // Atualizar categorias e pré-selecionar
            const catId = this.dataset.categoria;
            atualizarCategoriasEdit(editTipo.value, catId);
        });
    });

    const editTipoSelect = document.getElementById('edit_tipo');
    if (editTipoSelect) {
        editTipoSelect.addEventListener('change', function () {
            atualizarCategoriasEdit(this.value, '');
        });
    }

    function atualizarCategoriasEdit(tipo, selectedId) {
        const sel = document.getElementById('edit_categoria_id');
        if (!sel) return;
        sel.innerHTML = '<option value="">Sem categoria</option>';
        let lista = tipo === '1' ? categoriasEntrada : (tipo === '2' ? categoriasSaida : []);
        lista.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.nome;
            if (String(cat.id) === String(selectedId)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    // --- Seleção dinâmica de categoria por tipo (Cadastro) ---
    const tipoSelect = document.getElementById('tipo');
    const categoriaSelect = document.getElementById('categoria_id');

    function atualizarCategorias() {
        const tipo = tipoSelect ? tipoSelect.value : '';
        if (!categoriaSelect) return;
        categoriaSelect.innerHTML = '<option value="">Selecione a categoria</option>';
        let lista = [];
        if (tipo === '1') lista = categoriasEntrada;
        else if (tipo === '2') lista = categoriasSaida;

        lista.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.nome;
            categoriaSelect.appendChild(opt);
        });

        categoriaSelect.disabled = lista.length === 0;
        if (lista.length === 0) {
            categoriaSelect.innerHTML = '<option value="">Selecione o tipo primeiro</option>';
        }
    }

    if (tipoSelect) tipoSelect.addEventListener('change', atualizarCategorias);
    atualizarCategorias();

    // --- Gráfico de Pizza ---
    <?php if (!empty($saidasPorCategoria)): ?>
    const ctx = document.getElementById('chartSaidasCategoria');
    if (ctx) {
        const coresChart = [
            '#e74c3c','#e67e22','#f39c12','#8e44ad','#2980b9',
            '#27ae60','#16a085','#d35400','#c0392b','#7f8c8d'
        ];
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= $chartLabels ?>,
                datasets: [{
                    data: <?= $chartValues ?>,
                    backgroundColor: coresChart.slice(0, <?= count($saidasPorCategoria) ?>),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed;
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ' R$ ' + val.toLocaleString('pt-BR', {minimumFractionDigits:2}) + '  (' + pct + '%)';
                            }
                        }
                    }
                },
                animation: { animateRotate: true, duration: 800 }
            }
        });
    }
    <?php endif; ?>
});
</script>
