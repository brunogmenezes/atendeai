<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();
?>
<style>
    .report-card {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        border: 2px solid transparent;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        text-decoration: none;
        display: block;
    }
    .report-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
        border-color: rgba(255, 255, 255, 0.3);
    }
    .report-card.secondary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.2);
    }
    .report-card.secondary:hover {
        box-shadow: 0 12px 30px rgba(245, 87, 108, 0.4);
    }
    .report-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        display: inline-block;
    }
    .report-card h4 {
        font-weight: 700;
        margin: 15px 0 8px;
        font-size: 1.1rem;
    }
    .report-card p {
        font-size: 0.9rem;
        opacity: 0.9;
        margin: 0;
    }
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 25px;
        margin-top: 30px;
        padding-left: 10px;
        border-left: 4px solid #667eea;
    }
    .period-btn.active {
        background-color: #667eea;
        color: white;
        border-color: #667eea;
    }
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
</style>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">
                <i class="fas fa-chart-bar me-2"></i>Relatórios
            </h4>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Vendas no Periodo">
                <div class="text-center">
                    <div class="report-icon">💰</div>
                    <h4>Vendas por Período</h4>
                    <p>Análise completa de vendas</p>
                </div>
            </div>
        </div>
    </div>

    <h2 class="section-title"><i class="fas fa-tasks me-2"></i>Relatórios Operacionais</h2>
    
    <div class="row">
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <a href="relatorios.php?tipo=estoque" target="_blank" class="report-card secondary">
                <div class="text-center">
                    <div class="report-icon">📦</div>
                    <h4>Estoque de Produtos</h4>
                    <p>Movimentação e controle</p>
                </div>
            </a>
        </div>
    </div>
</div>
    
<!-- Modal para seleção de período -->
<div class="modal fade" id="periodModal" tabindex="-1" aria-labelledby="periodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="periodModalLabel">Selecionar Período</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Relatório: <strong id="selectedReportText"></strong>
                </div>
                    
                <div class="mb-3">
                    <label for="startDate" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="startDate" required>
                </div>
                    
                <div class="mb-3">
                    <label for="endDate" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="endDate" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Períodos Rápidos</label>
                    <div class="btn-group d-flex gap-2" role="group">
                        <button type="button" class="btn btn-outline-secondary period-btn flex-grow-1" data-days="7">7 dias</button>
                        <button type="button" class="btn btn-outline-secondary period-btn flex-grow-1" data-days="30">30 dias</button>
                        <button type="button" class="btn btn-outline-secondary period-btn flex-grow-1" data-days="90">90 dias</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="generateReport">
                    <i class="fas fa-download me-2"></i>Gerar Relatório
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Quando o modal é aberto, atualiza o texto com o relatório selecionado
    const periodModal = document.getElementById('periodModal');
    if (periodModal) {
        periodModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const reportName = button?.getAttribute('data-report');
            const modalTitle = document.querySelector('#periodModal .modal-title');
            const reportText = document.querySelector('#selectedReportText');
            
            if (reportName && modalTitle && reportText) {
                modalTitle.textContent = reportName + ' - Selecionar Período';
                reportText.textContent = reportName;
                
                // Define datas padrão (hoje e 30 dias atrás)
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(endDate.getDate() - 30);
                
                document.getElementById('endDate').valueAsDate = endDate;
                document.getElementById('startDate').valueAsDate = startDate;
            }
        });
    }
    
    // Botões de período pré-definido
    document.querySelectorAll('.period-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const days = parseInt(this.getAttribute('data-days'));
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
            
            // Remove classe active de todos os botões
            document.querySelectorAll('.period-btn').forEach(btn => btn.classList.remove('active'));
            // Adiciona ao botão clicado
            this.classList.add('active');
            
            document.getElementById('endDate').valueAsDate = endDate;
            document.getElementById('startDate').valueAsDate = startDate;
        });
    });

    // Gerar relatório com validação
    const generateBtn = document.getElementById('generateReport');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const reportName = document.querySelector('#selectedReportText')?.textContent;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Validação
            if (!startDate || !endDate) {
                alert('⚠️ Por favor, selecione ambas as datas');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                alert('⚠️ A data inicial não pode ser maior que a data final');
                return;
            }
            
            // Mapeamento dos tipos de relatório
            const reportMapping = {
                'Vendas no Periodo': 'financeiro-mensal'
            };
            
            const reportType = reportMapping[reportName] || 'default';
            
            // Criar parâmetros para URL
            const params = new URLSearchParams({
                tipo: reportType,
                start: startDate,
                end: endDate
            });
            
            // Abrir nova aba com relatório
            window.open(`relatorios.php?${params.toString()}`, '_blank');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(periodModal);
            modal?.hide();
        });
    }
</script>