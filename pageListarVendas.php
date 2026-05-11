<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

// Validação de parâmetros GET
$dia = isset($_GET['dia']) && is_numeric($_GET['dia']) ? (int)$_GET['dia'] : null;
$mes = isset($_GET['mes']) && is_numeric($_GET['mes']) ? (int)$_GET['mes'] : null;
$ano = isset($_GET['ano']) && is_numeric($_GET['ano']) ? (int)$_GET['ano'] : null;

$tabela = 'vendas';
$pagina = $_GET['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

$vendas = buscarTabelaVendas($tabela, '', '', $limite, $offset, 'DESC', $dia, $mes, $ano);
$totalQuery = contarNumeroPorVendas($dia, $mes, $ano);
$totalPaginas = ceil($totalQuery / $limite);

$resumoDia = contarNumeroPorVendas($dia, $mes, $ano) ?? 0;
$totalVendasDia = buscarTotalVendasnoPeriodo($dia, $mes, $ano) ?? 0;
$totalVendasMes = buscarTotalVendasnoMes($mes, $ano) ?? 0;
?>

<div class="page-header">
    <h3 class="fw-bold mb-3">Relatório de Vendas</h3>
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
            <a href="#">Listar Vendas</a>
        </li>
    </ul>
</div>

<!-- Resumo do Período -->
<div class="row">
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Vendas do Dia</p>
                            <h4 class="card-title"><?= $resumoDia ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Total do Dia</p>
                            <h4 class="card-title text-success">R$ <?= number_format($totalVendasDia, 2, ',', '.') ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Total do Mês</p>
                            <h4 class="card-title text-secondary">R$ <?= number_format($totalVendasMes, 2, ',', '.') ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row card-tools-still-right">
                    <div class="card-title">Filtros e Exportação</div>
                    <div class="card-tools">
                        <a href="gerar_pdf_vendas.php?dia=<?= $dia ?? '' ?>&mes=<?= $mes ?? '' ?>&ano=<?= $ano ?? '' ?>" class="btn btn-primary btn-round btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Gerar PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET" class="row g-2">
                    <input type="hidden" name="page" value="ListarVendas">
                    <div class="col-md-3">
                        <label class="form-label">Dia</label>
                        <select name="dia" class="form-select">
                            <option value="">Todos</option>
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == $dia) ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mês</label>
                        <select name="mes" class="form-select">
                            <option value="">Todos</option>
                            <?php
                            $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                            foreach ($meses as $num => $nome): ?>
                                <option value="<?= $num ?>" <?= ($num == $mes) ? 'selected' : '' ?>><?= $nome ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ano</label>
                        <select name="ano" class="form-select">
                            <?php for ($i = 2025; $i <= 2030; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == $ano || ($ano==null && $i==date('Y'))) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrar Vendas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabela de Vendas -->
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Vendas Realizadas</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 80px">ID</th>
                                <th class="text-start">Total Líquido</th>
                                <th class="text-start">Pagamento</th>
                                <th class="text-start">Vendedor</th>
                                <th class="text-center">Data / Hora</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($vendas): ?>
                                <?php foreach ($vendas as $retorno): 
                                    $descontoVenda = (float)($retorno['desconto'] ?? 0);
                                    $totalLiquidoVenda = (float)$retorno['total'] * (1 - ($descontoVenda / 100));
                                    $estornado = $retorno['estornado'] == true;
                                ?>
                                    <tr class="<?= $estornado ? 'text-muted' : '' ?>">
                                        <td>#<?= $retorno['id'] ?></td>
                                        <td class="text-start fw-bold <?= $estornado ? 'text-decoration-line-through' : 'text-success' ?>">
                                            R$ <?= number_format($totalLiquidoVenda, 2, ',', '.') ?>
                                        </td>
                                        <td class="text-start small"><?= $retorno['tipos_pagamento'] ?></td>
                                        <td class="text-start"><?= $retorno['usuariovendedor'] ?></td>
                                        <td class="text-center small"><?= date('d/m/Y H:i', strtotime($retorno['data_venda'])) ?></td>
                                        <td class="text-center">
                                            <?php if($estornado): ?>
                                                <span class="badge badge-danger">Estornada</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Concluída</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-primary" onclick="imprimir(<?= $retorno['id'] ?>)" title="Imprimir">
                                                    <i class="fa fa-print"></i>
                                                </button>
                                                <?php if($user['isAdmin']==true && !$estornado): ?>
                                                    <button type="button" class="btn btn-link btn-danger open-delete-modal" data-id="<?= $retorno['id'] ?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" title="Estornar">
                                                        <i class="fa fa-retweet"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center p-5 text-muted">Nenhuma venda encontrada para este período.</td>
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
                                <a class="page-link" href="index.php?page=ListarVendas&pagina=<?= $i ?>&dia=<?= $dia ?>&mes=<?= $mes ?>&ano=<?= $ano ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Estorno -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger fw-bold">Estornar Venda?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja estornar esta venda? O estoque será devolvido e o financeiro ajustado.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" action="formExcluir.php" method="POST">
                    <input type="hidden" name="tabela" value="vendas">
                    <input type="hidden" name="funcao" value="EstornarVenda">
                    <input type="hidden" name="page" value="ListarVendas">
                    <input type="hidden" name="id" id="productIdToDelete">
                    <button type="submit" class="btn btn-danger">Sim, Estornar</button>
                </form>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
function imprimir(id) {
    if (!id || isNaN(id)) return;
    window.open(`imprimirVenda.php?id=${encodeURIComponent(id)}`, '_blank');
}

document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.open-delete-modal');
    const productIdInput = document.getElementById('productIdToDelete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            productIdInput.value = this.getAttribute('data-id');
        });
    });
});
</script>
