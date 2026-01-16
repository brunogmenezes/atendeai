<?php
	include("config.php");
	include("funcoes.php");
    require_once 'auth.php';
verificarSessao();
?>
<style>
    .form-group
    {
        margin-bottom: 1rem;
    }
    .form-select, .form-control
    {
        height: calc(2.25rem + 8px);
    }
    .card-header
    {
        padding-bottom: 1.5rem;
    }
    .report-card
    {
        cursor: pointer;
        transition: transform 0.2s;
        height: 100%;
    }
    .report-card:hover
    {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .themed-grid-col
    {
        padding-top: 15px;
        padding-bottom: 15px;
        background-color: rgba(86, 61, 124, 0.1);
        border: 1px solid rgba(86, 61, 124, 0.2);
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .report-icon
    {
        font-size: 2.5rem;
        margin-bottom: 10px;
        color: #563d7c;
    }
    a
    {
        color: #563d7c;
    }
</style>
<div class="col-md-12">
	<div class="card">
		<div class="card-header">
            <div class="d-flex align-items-center">
                <h4 class="card-title">Listar Relatórios</h4>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-md-4 themed-grid-col report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Vendas no Periodo">
            <div class="text-center">
                <div class="report-icon">💰</div>
                <h4>Vendas por Período</h4>
                <p class="text-muted">Análise de vendas no período informado</p>
            </div>
        </div>
            <!--
        <div class="col-md-4 themed-grid-col report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Faturamento">
            <div class="text-center">
                <div class="report-icon">📈</div>
                <h4>Faturamento</h4>
                <p class="text-muted">Vendas e receitas</p>
            </div>
        </div>

            
        <div class="col-md-4 themed-grid-col report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Despesas">
            <div class="text-center">
                <div class="report-icon">📉</div>
                <h4>Despesas</h4>
                <p class="text-muted">Gastos e custos</p>
            </div>
        </div>
    </div>
   -->     
    <div class="row mb-3 mt-5">
        <div class="col-md-12">
            <h2>Relatórios Operacionais</h2>
        </div>
    </div>
        
    <div class="row">
        
            <div class="col-md-4 themed-grid-col report-card" data-bs-toggle="modal" data-bs-target="" data-report="Produtos">
                <a href="relatorios.php?tipo=estoque" target="_blank" >
                    <div class="text-center">
                        <div class="report-icon">📦</div>
                        <h4>Produtos</h4>
                        <p class="text-muted">Estoque e movimentação</p>
                    </div>
                </a>
            </div>
            <!--
        <div class="col-md-4 themed-grid-col report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Clientes">
            <div class="text-center">
                <div class="report-icon">👥</div>
                <h4>Clientes</h4>
                <p class="text-muted">Cadastros e compras</p>
            </div>
        </div>
            
        <div class="col-md-4 themed-grid-col report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Vendedores">
            <div class="text-center">
                <div class="report-icon">👔</div>
                <h4>Vendedores</h4>
                <p class="text-muted">Performance e metas</p>
            </div>
        </div>
        -->
    </div>
</div>
    
<!-- Modal para seleção de período -->
<div class="modal fade" id="periodModal" tabindex="-1" aria-labelledby="periodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="periodModalLabel">Selecionar Período</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="selectedReportText">Relatório selecionado: <strong></strong></p>
                    
                <div class="mb-3">
                    <label for="startDate" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="startDate">
                </div>
                    
                <div class="mb-3">
                    <label for="endDate" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="endDate">
                </div>
                    
                <div class="btn-group d-flex" role="group">
                    <button type="button" class="btn btn-outline-secondary period-btn" data-days="7">7 dias</button>
                    <button type="button" class="btn btn-outline-secondary period-btn" data-days="30">30 dias</button>
                    <button type="button" class="btn btn-outline-secondary period-btn" data-days="90">90 dias</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="generateReport">Gerar Relatório</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->

<script>
    // Quando o modal é aberto, atualiza o texto com o relatório selecionado
    document.getElementById('periodModal').addEventListener('show.bs.modal', function (event)
    {
        var button = event.relatedTarget;
        var reportName = button.getAttribute('data-report');
        var modalTitle = document.querySelector('#periodModal .modal-title');
        var reportText = document.querySelector('#selectedReportText strong');
            
        modalTitle.textContent = reportName + ' - Selecionar Período';
        reportText.textContent = reportName;
            
        // Define datas padrão (hoje e 30 dias atrás)
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(endDate.getDate() - 30);
            
        document.getElementById('endDate').valueAsDate = endDate;
        document.getElementById('startDate').valueAsDate = startDate;
    });
        
    // Botões de período pré-definido
    document.querySelectorAll('.period-btn').forEach(button =>
    {
        button.addEventListener('click', function()
        {
            const days = parseInt(this.getAttribute('data-days'));
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
                
            document.getElementById('endDate').valueAsDate = endDate;
            document.getElementById('startDate').valueAsDate = startDate;
        });
    });

    document.getElementById('generateReport').addEventListener('click', function() {
    const reportName = document.querySelector('#selectedReportText strong').textContent;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Por favor, selecione ambas as datas');
        return;
    }
    
    // Mapeamento dos tipos de relatório para os valores que seu PHP espera
    const reportMapping = {
        'Vendas no Periodo': 'financeiro-mensal',
        'Faturamento': 'vendas-diarias',
        'Despesas': 'financeiro-mensal',
        'Produtos': 'produtos',
        'Clientes': 'clientes',
        'Vendedores': 'vendedores'
    };
    
    // Obter o tipo de relatório correspondente ou usar um valor padrão
    const reportType = reportMapping[reportName] || 'default';
    
    // Codificar os parâmetros para URL
    const params = new URLSearchParams({
        tipo: reportType,
        start: startDate,
        end: endDate
    });
    
    // Abrir nova aba com a página de relatório
    window.open(`relatorios.php?${params.toString()}`, '_blank');
    
    // Fechar o modal
    window.location.reload();
});

//window.location.reload();
</script>