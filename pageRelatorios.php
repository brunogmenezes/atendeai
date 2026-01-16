<?php
require_once 'auth.php';
verificarSessao();
?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Relat�rios</h4>
    </div>

    <div class="row">
        <!-- Card de Relat�rios Financeiros -->
        <div class="col-md-6">
            <div class="card card-report">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Relat�rios Financeiros</h4>
                        <div class="card-tools">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="gerar_relatorio.php?tipo=financeiro-mensal" class="btn btn-link btn-block text-left">
                                <i class="fas fa-file-pdf text-danger"></i> Financeiro Mensal
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="gerar_relatorio.php?tipo=fluxo-caixa" class="btn btn-link btn-block text-left">
                                <i class="fas fa-file-pdf text-danger"></i> Fluxo de Caixa
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="gerar_relatorio.php?tipo=despesas-categorias" class="btn btn-link btn-block text-left">
                                <i class="fas fa-file-pdf text-danger"></i> Despesas por Categoria
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Card de Relat�rios de Vendas -->
        <div class="col-md-6">
            <div class="card card-report">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Relat�rios de Vendas</h4>
                        <div class="card-tools">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="gerar_relatorio.php?tipo=vendas-diarias" class="btn btn-link btn-block text-left">
                                <i class="fas fa-file-pdf text-danger"></i> Vendas Di�rias
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="gerar_relatorio.php?tipo=produtos-mais-vendidos" class="btn btn-link btn-block text-left">
                                <i class="fas fa-file-pdf text-danger"></i> Produtos Mais Vendidos
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="gerar_relatorio.php?tipo=vendas-vendedores" class="btn btn-link btn-block text-left">
                                <i class="fas fa-file-pdf text-danger"></i> Vendas por Vendedor
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avan�ados -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Gerar Relat�rio Personalizado</h4>
                </div>
                <div class="card-body">
                    <form action="gerar_relatorio.php" method="GET">
                        <input type="hidden" name="tipo" value="personalizado">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipo de Relat�rio</label>
                                    <select class="form-control" name="relatorio">
                                        <option value="financeiro">Financeiro</option>
                                        <option value="vendas">Vendas</option>
                                        <option value="estoque">Estoque</option>
                                        <option value="clientes">Clientes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Per�odo Inicial</label>
                                    <input type="date" class="form-control" name="data_inicio">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Per�odo Final</label>
                                    <input type="date" class="form-control" name="data_fim">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-pdf"></i> Gerar Relat�rio
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-report {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }
    
    .card-report:hover {
        transform: translateY(-5px);
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .list-group-item a:hover {
        text-decoration: none;
        background-color: #f8f9fa;
    }
</style>