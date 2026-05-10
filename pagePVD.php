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
<div class="container-fluid">
    <?php if (isset($mensagem_erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($mensagem_erro) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="card mt-4">
        <div class="card-header bg-primary text-white d-flex flex-column flex-sm-row justify-content-between align-items-center py-2 py-sm-3">
            <h4 class="mb-1 mb-sm-0">PDV - Ponto de Venda</h4>
            <span class="badge bg-white text-primary"><?= isset($dadosEmpresa['nome']) ? htmlspecialchars($dadosEmpresa['nome']) : 'Empresa' ?></span>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Área de Produtos -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="product-search" class="form-control border-start-0 ps-0" placeholder="Buscar produto...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border-radius: 8px;">
                        <table class="table table-striped table-hover table-mobile-cards" id="product-table">
                            <thead>
                                <tr>
                                    <th class="d-none d-md-table-cell" style="width: 10%">ID</th>
                                    <th>Produto</th>
                                    <th>Preço</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtosLista as $produto): ?>
                                <tr>
                                    <td class="d-none d-md-table-cell" data-label="ID"><?= $produto['id'] ?></td>
                                    <td class="product-name" data-label="Produto"><?= htmlspecialchars($produto['nome']) ?></td>
                                    <td data-label="Preço">R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></td>
                                    <td class="text-center" data-label="Ação">
                                        <button class="btn btn-primary btn-sm add-to-cart w-100 w-sm-auto"
                                            data-id="<?= $produto['id'] ?>"
                                            data-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                            data-preco="<?= $produto['preco_venda'] ?>">
                                            <i class="fa fa-plus me-1"></i> <span class="d-sm-none">Adicionar</span>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Área do Carrinho -->
                <div class="col-lg-6">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0"><i class="fa fa-shopping-basket me-2 text-primary"></i>Carrinho</h5>
                        <span class="badge bg-primary rounded-pill ms-2" id="cart-count">0</span>
                    </div>
                    <div class="table-responsive mb-3" style="max-height: 400px; overflow-y: auto; border-radius: 8px;">
                        <table class="table table-bordered table-mobile-cards">
                            <thead>
                                <tr class="table-light">
                                    <th>Produto</th>
                                    <th style="width: 15%">Qtd</th>
                                    <th class="d-none d-sm-table-cell">Preço</th>
                                    <th>Total</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <!-- Produtos adicionados ao carrinho -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="d-none d-sm-table-cell">Total R$</th>
                                    <th class="d-sm-none">Total R$</th>
                                    <th class="d-none d-sm-table-cell"></th>
                                    <th id="cart-total" class="text-success font-weight-bold">0,00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="d-none d-sm-table-cell">Desconto R$</th>
                                    <th class="d-sm-none">Desconto R$</th>
                                    <th class="d-none d-sm-table-cell"></th>
                                    <th id="cart-desconto">0,00</th>
                                    <th></th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="2" class="d-none d-sm-table-cell text-dark">Total com Desconto</th>
                                    <th class="d-sm-none text-dark">Total Líquido</th>
                                    <th class="d-none d-sm-table-cell"></th>
                                    <th id="cart-total-compra-com-desconto" class="fw-bold">0,00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text">Desconto (%)</span>
                                <input type="number" id="desconto" class="form-control" min="0" max="100" value="0" onchange="calcularDesconto()" oninput="calcularDesconto()">
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-start">
                        <button class="btn btn-success flex-fill py-2" id="btn-finalizar-venda">
                            <i class="fa fa-shopping-cart me-1"></i> Finalizar Venda
                        </button>
                        <button class="btn btn-outline-danger flex-fill py-2" id="clear-cart">
                            <i class="fa fa-trash me-1"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Finalização de Venda -->
<div class="modal fade" id="finalizarVenda" tabindex="-1" role="dialog" aria-labelledby="finalizarVendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizarVendaLabel">Total: R$ <span id="total-compra">0,00</span></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <!-- Área para múltiplos pagamentos -->
                    <div id="paymentMethodsContainer">
                        <div class="payment-method-row mb-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control payment-method" name="payment_methods[]" required>
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
                                    <input type="number" class="form-control payment-amount" name="payment_amounts[]" step="0.01" min="0" required placeholder="Valor">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-payment" style="display: none;">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" id="addPaymentMethod" class="btn btn-secondary btn-sm mb-3">
                        <i class="fa fa-plus"></i> Adicionar Forma de Pagamento
                    </button>
                    
                    <div class="form-group">
                        <label>Valor Restante: R$ <span id="remaining-amount">0,00</span></label>
                    </div>
                    
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-danger flex-fill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-lg flex-fill">
                            <i class="fa fa-cash-register me-1"></i> Finalizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/pdv.js?v=<?=time();?>"></script>