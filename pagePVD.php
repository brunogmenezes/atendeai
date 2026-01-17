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
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">PDV - Ponto de Venda</h4>
            <small><?= isset($dadosEmpresa['nome']) ? htmlspecialchars($dadosEmpresa['nome']) : 'Empresa' ?></small>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Área de Produtos -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <input type="text" id="product-search" class="form-control" placeholder="Buscar produto por nome...">
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped" id="product-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>Preço</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtosLista as $produto): ?>
                                <tr>
                                    <td><?= $produto['id'] ?></td>
                                    <td class="product-name"><?= htmlspecialchars($produto['nome']) ?></td>
                                    <td>R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-to-cart"
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

                <!-- Área do Carrinho -->
                <div class="col-md-6">
                    <h5>Carrinho</h5>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Preço</th>
                                    <th>Total</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <!-- Produtos adicionados ao carrinho -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total R$</th>
                                    <th id="cart-total" class="text-success font-weight-bold">0,00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3">Desconto R$</th>
                                    <th id="cart-desconto">0,00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3">Total com Desconto R$</th>
                                    <th id="cart-total-compra-com-desconto">0,00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Desconto (%)</span>
                                </div>
                                <input type="number" id="desconto" class="form-control" min="0" max="100" value="0" onchange="calcularDesconto()" oninput="calcularDesconto()">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-success" id="btn-finalizar-venda">
                        <i class="fa fa-shopping-cart"></i> Ir para Pagamento
                    </button>
                    <button class="btn btn-danger" id="clear-cart">
                        <i class="fa fa-trash"></i> Limpar Carrinho
                    </button>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fa fa-cash-register"></i> Finalizar Venda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/pdv.js"></script>