// Variáveis globais
let cart = [];

// Função para calcular desconto
function calcularDesconto() {
    const desconto = parseFloat(document.getElementById('desconto')?.value) || 0;
    const cartTotalEl = document.getElementById('cart-total');
    
    if (!cartTotalEl) return;
    
    const totalBruto = parseFloat(
        cartTotalEl.textContent.replace(/[^\d,]/g, '').replace(',', '.')
    ) || 0;
    
    const descontoValor = totalBruto * (desconto / 100);
    const totalComDesconto = totalBruto - descontoValor;
    
    const descontoEl = document.getElementById('cart-desconto');
    const totalDescontoEl = document.getElementById('cart-total-compra-com-desconto');
    
    if (descontoEl) {
        descontoEl.textContent = descontoValor.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }
    if (totalDescontoEl) {
        totalDescontoEl.textContent = totalComDesconto.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }
}

// Mostrar notificação toast
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast show position-fixed bottom-0 end-0 mb-3 me-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast-body bg-success text-white">
            <i class="fa fa-check-circle mr-2"></i> ${message}
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Atualizar carrinho na tela
function updateCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    // Validação básica
    if (!cartItems || !cartTotal) {
        console.warn('Elementos do carrinho não encontrados');
        return;
    }
    
    let total = 0;
    cartItems.innerHTML = '';
    
    cart.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.nome}</td>
            <td>
                <button class="btn btn-sm btn-outline-secondary update-qty" data-id="${item.id}" data-action="decrease">-</button>
                <span class="mx-2">${item.qtd}</span>
                <button class="btn btn-sm btn-outline-secondary update-qty" data-id="${item.id}" data-action="increase">+</button>
            </td>
            <td>R$ ${item.preco.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td>R$ ${(item.qtd * item.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td><button class="btn btn-danger btn-sm remove-from-cart" data-id="${item.id}"><i class="fa fa-minus"></i></button></td>
        `;
        cartItems.appendChild(row);
        total += item.qtd * item.preco;
    });

    const formattedTotal = total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    cartTotal.textContent = formattedTotal;
    
    // Atualizar campo total do modal (com segurança)
    const totalComprEl = document.getElementById('total-compra');
    if (totalComprEl) {
        totalComprEl.textContent = formattedTotal;
    }
    
    // Atualizar estado do botão de finalizar (com segurança)
    const finalizarBtn = document.getElementById('btn-finalizar-venda');
    if (finalizarBtn && finalizarBtn.disabled !== undefined) {
        finalizarBtn.disabled = cart.length === 0;
    }
    
    // Calcular desconto (com proteção)
    try {
        calcularDesconto();
    } catch (e) {
        console.warn('Erro ao calcular desconto:', e);
    }
}

// Adicionar produto ao carrinho
function addToCart(id, nome, preco) {
    const existing = cart.find(item => item.id === id);
    
    if (existing) {
        existing.qtd++;
    } else {
        cart.push({ id: String(id), nome, preco: parseFloat(preco), qtd: 1 });
    }
    
    updateCart();
    showToast(`${nome} adicionado ao carrinho`);
}

document.addEventListener('DOMContentLoaded', function() {
    const cartItems = document.getElementById('cart-items');
    const clearCartBtn = document.getElementById('clear-cart');
    const productSearch = document.getElementById('product-search');
    const paymentForm = document.getElementById('paymentForm');

    // Evento: Adicionar produtos ao carrinho
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');
            const preco = this.getAttribute('data-preco');
            addToCart(id, nome, preco);
        });
    });

    // Evento: Atualizar quantidade e remover do carrinho
    if (cartItems) {
        cartItems.addEventListener('click', function(event) {
            const target = event.target.closest('[data-id]');
            if (!target) return;
            
            const id = target.getAttribute('data-id');
            const item = cart.find(item => item.id === id);
            if (!item) return;
            
            if (target.classList.contains('update-qty')) {
                const action = target.getAttribute('data-action');
                if (action === 'increase') {
                    item.qtd++;
                } else if (action === 'decrease' && item.qtd > 1) {
                    item.qtd--;
                }
                updateCart();
            }
            else if (target.classList.contains('remove-from-cart')) {
                cart = cart.filter(item => item.id !== id);
                updateCart();
                showToast('Item removido do carrinho');
            }
        });
    }

    // Evento: Limpar carrinho
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function() {
            if (cart.length > 0 && confirm('Deseja realmente limpar o carrinho?')) {
                cart = [];
                updateCart();
                showToast('Carrinho limpo');
            }
        });
    }

    // Evento: Buscar produtos
    if (productSearch) {
        productSearch.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#product-table tbody tr').forEach(row => {
                const productName = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
                row.style.display = productName.includes(filter) ? '' : 'none';
            });
        });
    }
    // Evento: Botão "Ir para Pagamento"
    const btnFinalizarVenda = document.getElementById('btn-finalizar-venda');
    if (btnFinalizarVenda) {
        btnFinalizarVenda.addEventListener('click', function() {
            if (cart.length === 0) {
                alert('Adicione produtos ao carrinho antes de finalizar a venda!');
                return;
            }
            // Abrir modal
            $('#finalizarVenda').modal('show');
        });
    }
    // Evento: Adicionar forma de pagamento
    const addPaymentMethodBtn = document.getElementById('addPaymentMethod');
    if (addPaymentMethodBtn) {
        addPaymentMethodBtn.addEventListener('click', function() {
            const container = document.getElementById('paymentMethodsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'payment-method-row mb-2';
            
            const selectHtml = document.querySelector('.payment-method')?.innerHTML || '<option value="">Selecione...</option>';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <select class="form-control payment-method" name="payment_methods[]" required>
                            ${selectHtml}
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
    }

    // Evento: Remover forma de pagamento
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-payment')) {
            e.target.closest('.payment-method-row').remove();
            updateRemainingAmount();
        }
    });

    // Função para atualizar valor restante
    function updateRemainingAmount() {
        const totalDescontoEl = document.getElementById('cart-total-compra-com-desconto');
        if (!totalDescontoEl) return;
        
        const totalComDesconto = parseFloat(
            totalDescontoEl.textContent.replace(/[^\d,]/g, '').replace(',', '.')
        ) || 0;
        
        let paid = 0;
        document.querySelectorAll('.payment-amount').forEach(input => {
            paid += parseFloat(input.value) || 0;
        });
        
        const remaining = totalComDesconto - paid;
        const remainingElement = document.getElementById('remaining-amount');
        if (remainingElement) {
            remainingElement.textContent = remaining.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            
            if (remaining <= 0) {
                remainingElement.classList.remove('text-danger');
                remainingElement.classList.add('text-success');
            } else {
                remainingElement.classList.remove('text-success');
                remainingElement.classList.add('text-danger');
            }
        }
    }

    // Evento: Atualizar quando modal abre
    const modalEl = document.getElementById('finalizarVenda');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function() {
            const totalComDescontoEl = document.getElementById('cart-total-compra-com-desconto');
            if (totalComDescontoEl) {
                const totalComDesconto = totalComDescontoEl.textContent;
                const totalCompraEl = document.getElementById('total-compra');
                if (totalCompraEl) {
                    totalCompraEl.textContent = totalComDesconto;
                }
            }
            updateRemainingAmount();
        });

        // Garantir fechamento do modal (Bootstrap 4 ou 5)
        const closeButtons = document.querySelectorAll('#finalizarVenda [data-dismiss="modal"], #finalizarVenda [data-bs-dismiss="modal"], #finalizarVenda .close');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (window.bootstrap && bootstrap.Modal) {
                    const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    instance.hide();
                } else if (window.$) {
                    $('#finalizarVenda').modal('hide');
                } else {
                    modalEl.classList.remove('show');
                    modalEl.setAttribute('aria-hidden', 'true');
                }
            });
        });
    }

    // Evento: Atualizar valor restante quando input muda
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('payment-amount')) {
            updateRemainingAmount();
        }
    });
    if (paymentForm) {
        paymentForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            if (cart.length === 0) {
                alert('Carrinho vazio!');
                return;
            }
            
            const desconto = parseFloat(document.getElementById('desconto')?.value) || 0;
            const total = cart.reduce((sum, item) => sum + item.qtd * item.preco, 0);
            const totalComDesconto = parseFloat((total * (1 - desconto / 100)).toFixed(2));
            
            // Validar pagamentos
            let paid = 0;
            const paymentMethods = [];
            const paymentAmounts = [];
            
            document.querySelectorAll('.payment-method').forEach((select) => {
                if (!select.value) {
                    alert('Selecione todas as formas de pagamento!');
                    select.focus();
                    throw new Error('Forma de pagamento não selecionada');
                }
                paymentMethods.push(select.value);
            });
            
            document.querySelectorAll('.payment-amount').forEach((input) => {
                const value = parseFloat(input.value) || 0;
                paymentAmounts.push(value);
                paid += value;
            });
            
            if (Math.abs(paid - totalComDesconto) > 0.01) {
                alert(`Valor pago (R$ ${paid.toFixed(2)}) diferente do total (R$ ${totalComDesconto.toFixed(2)})!`);
                return;
            }
            
            const dados = {
                total: total,
                desconto: desconto,
                paymentMethods: paymentMethods,
                paymentAmounts: paymentAmounts,
                itens: cart.map(item => ({
                    id: item.id,
                    nome: item.nome,
                    qtd: item.qtd,
                    preco: item.preco
                }))
            };
            
            try {
                const response = await fetch('finalizar_compra.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    imprimirComprovante(data.venda_id);
                    showToast('Venda finalizada com sucesso!');
                    cart = [];
                    updateCart();
                    $('#finalizarVenda').modal('hide');
                    window.location.href = 'index.php?page=InicioPVD';
                } else {
                    throw new Error(data.message || 'Erro ao finalizar a venda');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro: ' + error.message);
            }
        });
    }
    
    // Inicializar carrinho
    updateCart();
});

function imprimirComprovante(vendaId) {
    if (!vendaId) {
        console.error('ID da venda não fornecido');
        alert('Não foi possível gerar o comprovante. ID da venda ausente.');
        return;
    }

    const url = `imprimirVenda.php?id=${encodeURIComponent(vendaId)}`;
    const janelaImpressao = window.open(url, '_blank', 'width=600,height=800');
    
    if (!janelaImpressao) {
        alert('Permita pop-ups para visualizar o comprovante');
        window.location.href = url;
    }
}