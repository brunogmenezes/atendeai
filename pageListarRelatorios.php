<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();
?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    .report-container {
        padding: 20px 0;
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .section-header i {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 20px;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .section-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .report-card {
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        height: 100%;
        border: none;
        background: white;
        padding: 40px 25px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--primary-gradient);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: -1;
    }

    .report-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    }

    .report-card:hover::before {
        opacity: 1;
    }

    .report-card:hover .report-icon,
    .report-card:hover h4,
    .report-card:hover p {
        color: white !important;
    }

    .report-card.secondary::before {
        background: var(--secondary-gradient);
    }
    
    .report-card.secondary:hover {
        box-shadow: 0 20px 40px rgba(245, 87, 108, 0.15);
    }

    .report-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
        transition: transform 0.4s ease;
        background: #f8f9fa;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.02);
    }

    .report-card:hover .report-icon {
        transform: scale(1.1) rotate(5deg);
        background: rgba(255, 255, 255, 0.2);
    }

    .report-card h4 {
        font-weight: 700;
        margin-bottom: 10px;
        color: #2c3e50;
        font-size: 1.25rem;
        transition: color 0.3s ease;
    }

    .report-card p {
        font-size: 0.95rem;
        color: #7f8c8d;
        margin: 0;
        text-align: center;
        transition: color 0.3s ease;
    }

    .report-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 5px 12px;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .report-card:hover .report-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .modal-content {
        border-radius: 25px;
        border: none;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: var(--primary-gradient);
        padding: 30px;
        border: none;
    }

    .modal-title {
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .modal-body {
        padding: 40px;
    }

    .form-control {
        border-radius: 12px;
        padding: 12px 15px;
        border: 2px solid #eee;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .period-btn {
        border-radius: 12px;
        padding: 10px;
        font-weight: 600;
        border: 2px solid #eee;
        transition: all 0.3s ease;
    }

    .period-btn:hover {
        background: #f8f9fa;
        border-color: #667eea;
        color: #667eea;
    }

    .period-btn.active {
        background: var(--primary-gradient) !important;
        border-color: transparent !important;
        color: white !important;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
        border-radius: 12px;
        padding: 12px 30px;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(102, 126, 234, 0.4);
    }
</style>
<div class="report-container">
    <div class="section-header">
        <i class="fas fa-hand-holding-usd"></i>
        <h2>Gestão Financeira</h2>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Vendas no Periodo">
                <span class="report-badge">Financeiro</span>
                <div class="report-icon">💰</div>
                <h4>Vendas por Período</h4>
                <p>Análise detalhada de faturamento, tickets e pagamentos.</p>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="report-card" data-bs-toggle="modal" data-bs-target="#periodModal" data-report="Fluxo de Caixa" style="border-left: 5px solid #4facfe;">
                <span class="report-badge">Financeiro</span>
                <div class="report-icon">📊</div>
                <h4>Fluxo de Caixa</h4>
                <p>Análise diária de entradas, saídas e saldo líquido.</p>
            </div>
        </div>
    </div>

    <div class="section-header mt-4">
        <i class="fas fa-boxes"></i>
        <h2>Controle Operacional</h2>
    </div>
    
    <div class="row">
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <a href="relatorios.php?tipo=estoque" target="_blank" class="report-card secondary">
                <span class="report-badge">Logística</span>
                <div class="report-icon">📦</div>
                <h4>Estoque Atual</h4>
                <p>Posição consolidada, custos e lucro potencial do estoque.</p>
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
                'Vendas no Periodo': 'financeiro-mensal',
                'Fluxo de Caixa': 'fluxo-caixa'
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