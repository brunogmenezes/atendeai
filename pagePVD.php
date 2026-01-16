<?php
require_once 'config.php';
require_once 'funcoes.php';

require_once 'auth.php';
verificarSessao();

$produtosLista = BuscarporTabela('produtos');
$dadosEmpresa = buscarDadosEmpresa();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV - Ponto de Venda</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="container-fluid">
    <div class="card mt-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">PDV - Ponto de Venda</h4>
    <small><?= htmlspecialchars($dadosEmpresa['nome']) ?></small>
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
                    <div class="row mb-3"> <!-- Adiciona uma linha para organizar o campo de desconto -->
                        <div class="col-md-5"> <!-- Ocupa toda a largura disponível -->
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Desconto (%)</span>
                                </div>
                                <input type="number" id="desconto" class="form-control" min="0" max="100" value="0" oninput="calcularDesconto()">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-success" data-toggle="modal" data-target="#finalizarVenda">
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
<div class="modal fade" id="finalizarVenda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Total: R$ <span id="total-compra">0,00</span></h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
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
                    
                    <div class="modal-footer border-0">
                        <button class="btn btn-lg btn-success w-100 mt-3">
                            <i class="fa fa-cash-register"></i> Finalizar Venda
                        </button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Passa os dados da empresa para o JavaScript
    const dadosEmpresa = <?php echo json_encode($dadosEmpresa); ?>;


    // Adicionar nova forma de pagamento
document.getElementById('addPaymentMethod').addEventListener('click', function() {
    const container = document.getElementById('paymentMethodsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'payment-method-row mb-2';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <select class="form-control payment-method" name="payment_methods[]" required>
                    ${document.querySelector('.payment-method').innerHTML}
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control payment-amount" name="payment_amounts[]" step="0.01" min="0" required placeholder="Valor">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-payment">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    updateRemainingAmount();
});

// Remover forma de pagamento
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-payment')) {
        e.target.closest('.payment-method-row').remove();
        updateRemainingAmount();
    }
});

// Atualizar valor restante
function updateRemainingAmount() {
    const totalComDesconto = parseFloat(
        document.getElementById('cart-total-compra-com-desconto').textContent
            .replace(/[^\d,]/g, '')
            .replace(',', '.')
    ) || 0;
    
    let paid = 0;
    document.querySelectorAll('.payment-amount').forEach(input => {
        paid += parseFloat(input.value) || 0;
    });
    
    const remaining = totalComDesconto - paid;
    const remainingElement = document.getElementById('remaining-amount');
    remainingElement.textContent = remaining.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    
    if (remaining <= 0) {
        remainingElement.classList.remove('text-danger');
        remainingElement.classList.add('text-success');
    } else {
        remainingElement.classList.remove('text-success');
        remainingElement.classList.add('text-danger');
    }
}

// Atualizar quando o modal é aberto
document.getElementById('finalizarVenda').addEventListener('show.bs.modal', function() {
    const totalComDesconto = document.getElementById('cart-total-compra-com-desconto').textContent;
    document.getElementById('total-compra').textContent = totalComDesconto;
    
    // Resetar os pagamentos
    const container = document.getElementById('paymentMethodsContainer');
    container.innerHTML = `
        <div class="payment-method-row mb-2">
            <div class="row">
                <div class="col-md-6">
                    <select class="form-control payment-method" name="payment_methods[]" required>
                        <option value="">Selecione...</option>
                        ${document.querySelector('.payment-method').innerHTML.replace('selected', '')}
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
    `;
    
    updateRemainingAmount();
});

// Atualizar quando os valores mudam
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('payment-amount')) {
        updateRemainingAmount();
    }
});
</script>
<script src="assets/js/pdv.js"></script> <!-- Arquivo JavaScript separado -->
</body>
</html>