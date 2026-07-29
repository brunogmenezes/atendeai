<?php
require_once 'config.php';
require_once 'funcoes.php';
require_once 'auth.php';

verificarSessao();

// Buscar dados com validação de erro
try {
    $produtosLista = BuscarporTabela('produtos');
    if (empty($produtosLista)) {
        $mensagem_erro = "Nenhum produto disponível para venda.";
    }
    $dadosEmpresa = buscarDadosEmpresa();
} catch (Exception $e) {
    $mensagem_erro = "Erro ao carregar dados: " . $e->getMessage();
    $produtosLista = [];
}
?>
<style>
    .pdv-wrapper { 
        background: #f4f7f6; 
        padding: 10px; 
        border-radius: 15px; 
        height: calc(100vh - 100px); 
        display: flex;
        flex-direction: column;
    }
    .pdv-card { 
        border: none; 
        border-radius: 15px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
        background: white; 
        overflow: hidden; 
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .pdv-header { 
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); 
        color: white; 
        padding: 12px 20px; 
    }
    .product-list-container { 
        flex-grow: 1;
        overflow-y: auto; 
        padding-right: 5px; 
        max-height: calc(100vh - 280px);
    }
    .product-list-container::-webkit-scrollbar { width: 5px; }
    .product-list-container::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
    
    .cart-container { 
        background: #fff; 
        border-radius: 15px; 
        padding: 15px; 
        box-shadow: inset 0 0 10px rgba(0,0,0,0.02); 
        height: 100%; 
        border: 1px solid #edf2f7;
        display: flex;
        flex-direction: column;
    }
    .total-section { 
        background: #1a202c; 
        color: white; 
        border-radius: 12px; 
        padding: 15px; 
        margin-top: 10px; 
    }
    .total-value { font-size: 2rem; font-weight: 800; color: #48bb78; line-height: 1.2; }
    .total-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; }
    
    .search-input { border-radius: 10px; padding: 10px 15px; border: 2px solid #edf2f7; font-size: 1rem; }
    .btn-pay { padding: 12px; font-weight: 700; border-radius: 10px; font-size: 1.1rem; }
    
    .shortcut-badge { background: #edf2f7; color: #4a5568; padding: 1px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; border: 1px solid #cbd5e0; }
    
    .table-sm td, .table-sm th { padding: 0.3rem; }
    .card-body { padding: 15px !important; flex-grow: 1; display: flex; flex-direction: column; }
    .row.g-4 { flex-grow: 1; }

    /* Estilos para o novo Toast Premium */
    .pdv-toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 10000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        padding: 12px 15px;
        min-width: 250px;
        transform: translateX(120%);
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-left: 5px solid #48bb78;
    }
    .pdv-toast.show { transform: translateX(0); }
    .pdv-toast.hide { transform: translateX(120%); opacity: 0; }
    .pdv-toast-content { display: flex; align-items: center; }
    .pdv-toast-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .pdv-toast-message {
        font-weight: 700;
        color: #2d3748;
        font-size: 0.9rem;
    }
</style>

<div class="pdv-wrapper">
    <?php if (isset($mensagem_erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> <?= htmlspecialchars($mensagem_erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="pdv-card">
        <div class="pdv-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 font-weight-bold">AtendAI PDV</h4>
                <div class="mt-1" style="opacity: 0.8;">
                    <span class="me-3"><i class="far fa-user me-1"></i> <?=$_SESSION['username'];?></span>
                    <span><i class="far fa-calendar-alt me-1"></i> <?=date('d/m/Y');?></span>
                </div>
            </div>
            <div class="text-end">
                <div class="h5 mb-0 fw-bold"><?= isset($dadosEmpresa['nome']) ? htmlspecialchars($dadosEmpresa['nome']) : 'Empresa' ?></div>
                <small style="opacity: 0.7;">Terminal de Vendas #01</small>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Área de Produtos (Lado Esquerdo) -->
                <div class="col-lg-7">
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="product-search" class="form-control search-input border-start-0" placeholder="Buscar produto por nome ou ID... (F1)">
                        </div>
                    </div>
                    
                    <div class="product-list-container">
                        <table class="table table-hover align-middle" id="product-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th class="text-center">Estoque</th>
                                    <th class="text-end">Preço</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtosLista as $produto): 
                                    $lowStock = ($produto['quantidade'] <= ($produto['quantidade_critico'] ?? 5));
                                ?>
                                <tr>
                                    <td class="text-muted">#<?= $produto['id'] ?></td>
                                    <td>
                                        <div class="fw-bold product-name"><?= htmlspecialchars($produto['nome']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-badge <?= $lowStock ? 'stock-low' : 'stock-ok' ?>">
                                            <?= (int)$produto['quantidade'] ?> un
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-primary">R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm add-to-cart shadow-sm rounded-pill px-3"
                                            data-id="<?= $produto['id'] ?>"
                                            data-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                            data-preco="<?= $produto['preco_venda'] ?>">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Área do Carrinho (Lado Direito) -->
                <div class="col-lg-5">
                    <div class="cart-container d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-shopping-basket me-2 text-primary"></i>Carrinho</h5>
                            <button class="btn btn-link btn-sm text-danger p-0 text-decoration-none" id="clear-cart">
                                <i class="fa fa-trash-alt me-1"></i> Limpar (F2)
                            </button>
                        </div>

                        <div class="flex-grow-1 overflow-auto mb-2" style="min-height: 150px; max-height: calc(100vh - 550px);">
                            <table class="table table-sm align-middle">
                                <tbody id="cart-items">
                                    <!-- Itens do carrinho -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-auto">
                            <div class="p-2 bg-light rounded-3 mb-2 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="fw-bold" id="cart-total">R$ 0,00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Desconto (%)</span>
                                    <input type="number" id="desconto" class="form-control form-control-sm text-end" style="width: 60px;" min="0" max="100" value="0">
                                </div>
                                <div class="d-flex justify-content-between border-top pt-1">
                                    <span class="text-muted">Valor Desconto</span>
                                    <span class="text-danger" id="cart-desconto">R$ 0,00</span>
                                </div>
                            </div>

                            <div class="total-section">
                                <div class="total-label">Total a Pagar</div>
                                <div class="total-value" id="cart-total-compra-com-desconto">R$ 0,00</div>
                                <button class="btn btn-success btn-pay w-100 mt-3 shadow-lg" id="btn-finalizar-venda">
                                    <i class="fa fa-cash-register me-2"></i> RECEBER (F9)
                                </button>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <span class="shortcut-badge">F1</span> Buscar
                                <span class="shortcut-badge">F2</span> Limpar
                                <span class="shortcut-badge">F9</span> Receber
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal de Finalização de Venda -->
<div class="modal fade" id="finalizarVenda" tabindex="-1" role="dialog" aria-labelledby="finalizarVendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 p-0" style="overflow: hidden;">
                <div class="w-100 p-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3 me-3 shadow-sm" style="backdrop-filter: blur(5px);">
                            <i class="fa fa-cash-register fa-2x text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title h3 fw-bold mb-0 text-white" id="finalizarVendaLabel">Recebimento</h5>
                            <div class="d-flex align-items-center mt-1">
                                <span class="badge bg-success-soft text-white border border-white border-opacity-25 py-1 px-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                                    MODO TERMINAL
                                </span>
                                <small class="text-white-50 ms-2" style="font-size: 0.75rem;">Confirme os valores para finalizar</small>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Total da Venda</div>
                                <div class="h2 fw-bold text-dark mb-0">R$ <span id="total-compra">0,00</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                            <div class="card-body p-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Valor Restante</div>
                                <div class="h2 fw-bold text-danger mb-0">R$ <span id="remaining-amount">0,00</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de Progresso de Pagamento -->
                <div class="progress mb-4" style="height: 10px; border-radius: 10px;">
                    <div id="payment-progress" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>

                <form id="paymentForm" class="bg-white p-4 rounded-4 shadow-sm">
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-bold"><i class="fa fa-handshake me-1"></i>Tipo de Atendimento <span class="text-danger">*</span></label>
                        <select class="form-select form-control-lg shadow-sm" id="tipo_atendimento" name="tipo_atendimento" required>
                            <option value="">Selecione...</option>
                            <option value="presencial">Presencial</option>
                            <option value="online">Online</option>
                        </select>
                    </div>

                    <h6 class="fw-bold mb-3"><i class="fa fa-credit-card me-2"></i>Formas de Pagamento</h6>
                    
                    <div id="paymentMethodsContainer">
                        <!-- Primeira linha de pagamento (padrão) -->
                        <div class="payment-method-row mb-3 p-3 bg-light rounded-3 border">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted fw-bold">Tipo de Pagamento</label>
                                    <select class="form-select form-control-lg payment-method shadow-sm" name="payment_methods[]" required>
                                        <option value="">Selecione...</option>
                                        <?php
                                            $resultado = buscarTipoPagamento();
                                            if (!empty($resultado)) {
                                                foreach ($resultado as $conta) {
                                                    echo "<option value='" . htmlspecialchars($conta['id']) . "'>" . htmlspecialchars($conta['nome']) . "</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted fw-bold">Valor (R$)</label>
                                    <input type="number" class="form-control form-control-lg payment-amount shadow-sm" name="payment_amounts[]" step="0.01" min="0" required placeholder="0.00">
                                </div>
                                <div class="col-md-2 text-center">
                                    <button type="button" class="btn btn-outline-danger btn-lg remove-payment border-0" style="display: none;">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" id="addPaymentMethod" class="btn btn-outline-primary border-dashed py-2" style="border-style: dashed;">
                            <i class="fa fa-plus-circle me-1"></i> Adicionar outra forma (Divisão)
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-light btn-lg w-100 py-3 fw-bold text-muted" data-bs-dismiss="modal">
                                CANCELAR
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm" id="btn-submit-venda">
                                <i class="fa fa-check-circle me-2"></i> FINALIZAR VENDA
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/pdv.js"></script>