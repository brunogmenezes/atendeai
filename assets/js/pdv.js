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

// Mostrar notificação toast elegante
function showToast(message, type = 'success') {
    // Remover toasts antigos para não acumular
    const existingToasts = document.querySelectorAll('.pdv-toast');
    if (existingToasts.length > 3) existingToasts[0].remove();

    const toast = document.createElement('div');
    toast.className = 'pdv-toast show';
    
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'danger' ? 'fa-exclamation-triangle' : 'fa-info-circle');
    const color = type === 'success' ? '#48bb78' : (type === 'danger' ? '#f56565' : '#ed8936');
    
    toast.innerHTML = `
        <div class="pdv-toast-content">
            <div class="pdv-toast-icon" style="background: ${color}">
                <i class="fa ${icon}"></i>
            </div>
            <div class="pdv-toast-body">
                <div class="pdv-toast-message">${message}</div>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 500);
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
            <td>
                <div class="fw-bold">${item.nome}</div>
                <div class="small text-muted">R$ ${item.preco.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
            </td>
            <td class="text-center">
                <div class="input-group input-group-sm justify-content-center" style="width: 100px; margin: 0 auto;">
                    <button class="btn btn-outline-secondary update-qty p-1" data-id="${item.id}" data-action="decrease" style="width: 25px;">-</button>
                    <span class="px-2 align-self-center font-weight-bold">${item.qtd}</span>
                    <button class="btn btn-outline-secondary update-qty p-1" data-id="${item.id}" data-action="increase" style="width: 25px;">+</button>
                </div>
            </td>
            <td class="text-end fw-bold">R$ ${(item.qtd * item.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td class="text-center">
                <button class="btn btn-link btn-sm text-danger remove-from-cart" data-id="${item.id}"><i class="fa fa-times"></i></button>
            </td>
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
    const paymentForm = document.getElementById('paymentForm');
    const descontoInput = document.getElementById('desconto');
    const productSearch = document.getElementById('product-search');

    // Bloquear ajuda do navegador no F1
    window.onhelp = function() {
        return false;
    };

    // Atalhos de teclado globais
    window.addEventListener('keydown', function(e) {
        // F1 - Buscar
        if (e.key === 'F1' || e.keyCode === 112) {
            e.preventDefault();
            e.stopPropagation();
            const input = document.getElementById('product-search');
            if (input) {
                input.focus();
                input.select();
            }
            return false;
        }
        // F2 - Limpar
        if (e.key === 'F2' || e.keyCode === 113) {
            e.preventDefault();
            document.getElementById('clear-cart')?.click();
        }
        // F9 - Receber
        if (e.key === 'F9' || e.keyCode === 120) {
            e.preventDefault();
            document.getElementById('btn-finalizar-venda')?.click();
        }
    });

    // Auto-focus no campo de busca
    setTimeout(() => {
        const input = document.getElementById('product-search');
        if (input) {
            input.focus();
            input.select();
        }
    }, 500);

    // Evento: Desconto mudando
    if (descontoInput) {
        descontoInput.addEventListener('input', calcularDesconto);
        descontoInput.addEventListener('change', calcularDesconto);
    }
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

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function() {
            if (cart.length === 0) return;
            
            swal({
                title: "Limpar Carrinho?",
                text: "Todos os itens adicionados serão removidos.",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancelar",
                        value: null,
                        visible: true,
                        className: "btn btn-secondary",
                        closeModal: true,
                    },
                    confirm: {
                        text: "Sim, Limpar!",
                        value: true,
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: true
                    }
                }
            }).then((willClear) => {
                if (willClear) {
                    cart = [];
                    updateCart();
                    showToast('Carrinho limpo com sucesso');
                }
            });
        });
    }

    // Evento: Buscar produtos
    if (productSearch) {
        productSearch.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#product-table tbody tr').forEach(row => {
                const productName = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
                const productId = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                row.style.display = (productName.includes(filter) || productId.includes(filter)) ? '' : 'none';
            });
        });

        productSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const visibleRows = document.querySelectorAll('#product-table tbody tr:not([style*="display: none"])');
                if (visibleRows.length > 0) {
                    const firstBtn = visibleRows[0].querySelector('.add-to-cart');
                    if (firstBtn) firstBtn.click();
                    this.value = '';
                    this.dispatchEvent(new Event('input'));
                }
            }
        });
    }
    // Evento: Botão "Ir para Pagamento"
    const btnFinalizarVenda = document.getElementById('btn-finalizar-venda');
    if (btnFinalizarVenda) {
        btnFinalizarVenda.addEventListener('click', function() {
            if (cart.length === 0) {
                swal({
                    title: "Carrinho Vazio",
                    text: "Adicione produtos ao carrinho antes de finalizar a venda!",
                    icon: "warning",
                    button: "Entendi",
                });
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
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small text-muted fw-bold">Tipo de Pagamento</label>
                        <select class="form-select form-control-lg payment-method shadow-sm" name="payment_methods[]" required>
                            ${selectHtml}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-bold">Valor (R$)</label>
                        <input type="number" class="form-control form-control-lg payment-amount shadow-sm" name="payment_amounts[]" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="col-md-2 text-center">
                        <button type="button" class="btn btn-outline-danger btn-lg remove-payment border-0">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            newRow.style.padding = '1rem';
            newRow.style.background = '#f8f9fa';
            newRow.style.borderRadius = '0.5rem';
            newRow.style.border = '1px solid #dee2e6';
            container.appendChild(newRow);
            
            // Auto-preencher com o valor restante
            const totalDescontoEl = document.getElementById('cart-total-compra-com-desconto');
            if (totalDescontoEl) {
                const totalComDesconto = parseFloat(totalDescontoEl.textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
                let paid = 0;
                // Soma todos os outros inputs (exceto o novo que acabamos de criar)
                document.querySelectorAll('.payment-amount').forEach((input, idx, arr) => {
                    if (idx < arr.length - 1) paid += parseFloat(input.value) || 0;
                });
                const remaining = Math.max(totalComDesconto - paid, 0);
                newRow.querySelector('.payment-amount').value = remaining.toFixed(2);
            }

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

    // Função para atualizar valor restante e barra de progresso
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
        const progressBar = document.getElementById('payment-progress');
        
        if (remainingElement) {
            remainingElement.textContent = remaining.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            remainingElement.classList.toggle('text-danger', remaining > 0);
            remainingElement.classList.toggle('text-success', remaining <= 0);
        }

        if (progressBar) {
            const percent = totalComDesconto > 0 ? Math.min((paid / totalComDesconto) * 100, 100) : 0;
            progressBar.style.width = percent + '%';
            if (percent >= 100) {
                progressBar.classList.remove('bg-success');
                progressBar.style.backgroundColor = '#2ecc71';
            } else {
                progressBar.classList.add('bg-success');
            }
        }
        
        // Bloqueio do botão de finalizar venda
        const btnSubmit = document.getElementById('btn-submit-venda');
        if (btnSubmit) {
            let allMethodsSelected = true;
            document.querySelectorAll('.payment-method').forEach(select => {
                if (!select.value) allMethodsSelected = false;
            });
            
            if (Math.abs(remaining) <= 0.001 && allMethodsSelected) {
                btnSubmit.disabled = false;
            } else {
                btnSubmit.disabled = true;
            }
        }
    }

    // Evento: Atualizar quando modal abre
    const modalEl = document.getElementById('finalizarVenda');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function() {
            // Reset tipo_atendimento select box
            const tipoAtendimentoEl = document.getElementById('tipo_atendimento');
            if (tipoAtendimentoEl) {
                tipoAtendimentoEl.value = '';
            }

            const totalComDescontoEl = document.getElementById('cart-total-compra-com-desconto');
            if (totalComDescontoEl) {
                const totalText = totalComDescontoEl.textContent;
                const totalVal = parseFloat(totalText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
                
                document.getElementById('total-compra').textContent = totalText;
                
                // Preencher o primeiro campo de valor automaticamente
                const firstAmountInput = document.querySelector('.payment-amount');
                if (firstAmountInput && !firstAmountInput.value) {
                    firstAmountInput.value = totalVal.toFixed(2);
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
        if (e.target.classList.contains('payment-amount') || e.target.classList.contains('payment-method')) {
            updateRemainingAmount();
        }
    });
    
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('payment-method')) {
            updateRemainingAmount();
        }
    });
    if (paymentForm) {
        paymentForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const btnSubmit = document.getElementById('btn-submit-venda');
            if (btnSubmit && btnSubmit.dataset.submitting === 'true') {
                return; // Impede múltiplos envios
            }
            
            const tipoAtendimentoEl = document.getElementById('tipo_atendimento');
            if (tipoAtendimentoEl && !tipoAtendimentoEl.value) {
                swal({
                    title: "Tipo de Atendimento",
                    text: "Por favor, selecione o tipo de atendimento (Presencial ou Online)!",
                    icon: "warning",
                    button: "Entendi",
                });
                tipoAtendimentoEl.focus();
                if (btnSubmit) {
                    btnSubmit.dataset.submitting = 'false';
                }
                return;
            }
            
            if (cart.length === 0) {
                swal({
                    title: "Atenção!",
                    text: "Carrinho vazio! Adicione produtos antes de finalizar.",
                    icon: "warning",
                    button: "Entendi",
                });
                
                const btnSubmitReativar = document.getElementById('btn-submit-venda');
                if (btnSubmitReativar) {
                    btnSubmitReativar.dataset.submitting = 'false';
                }
                return;
            }
            
            const desconto = parseFloat(document.getElementById('desconto')?.value) || 0;
            const total = cart.reduce((sum, item) => sum + item.qtd * item.preco, 0);
            const totalComDesconto = parseFloat((total * (1 - desconto / 100)).toFixed(2));
            
            // Validar pagamentos ANTES de fechar o modal
            let paid = 0;
            const paymentMethods = [];
            const paymentAmounts = [];
            let selectionError = false;

            document.querySelectorAll('.payment-method').forEach((select) => {
                if (!select.value) {
                    if (!selectionError) { // Mostra o alerta apenas uma vez
                        swal({
                            title: "Forma de Pagamento",
                            text: "Selecione a forma de pagamento para todos os valores inseridos!",
                            icon: "warning",
                            button: "Entendi",
                        });
                    }
                    select.focus();
                    selectionError = true;
                }
                paymentMethods.push(select.value);
            });
            if (selectionError) {
                const btnSubmitReativar = document.getElementById('btn-submit-venda');
                if (btnSubmitReativar) {
                    btnSubmitReativar.dataset.submitting = 'false';
                }
                return;
            }
            
            document.querySelectorAll('.payment-amount').forEach((input) => {
                const value = parseFloat(input.value) || 0;
                paymentAmounts.push(value);
                paid += value;
            });
            
            if (Math.abs(paid - totalComDesconto) > 0.001) {
                swal({
                    title: "Divergência de Valores",
                    text: `Valor pago (R$ ${paid.toFixed(2)}) é diferente do total da venda (R$ ${totalComDesconto.toFixed(2)})!`,
                    icon: "error",
                    button: "Corrigir",
                });
                
                const btnSubmitReativar = document.getElementById('btn-submit-venda');
                if (btnSubmitReativar) {
                    btnSubmitReativar.dataset.submitting = 'false';
                }
                return;
            }

            // Verificar se alguma das formas de pagamento é PIX
            let hasPix = false;
            document.querySelectorAll('.payment-method').forEach(select => {
                if (select.options[select.selectedIndex] && select.options[select.selectedIndex].text.toLowerCase().includes('pix')) {
                    hasPix = true;
                }
            });

            const proceedWithFinalization = async (pix_txid = null) => {
                // SE TUDO ESTIVER CERTO, então procedemos com o bloqueio e fechamento
                if (btnSubmit) {
                    btnSubmit.dataset.submitting = 'true';
                    btnSubmit.disabled = true;
                }

                const overlay = document.createElement('div');
                overlay.id = 'pdv-lock-overlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;backdrop-filter:blur(5px);';
                overlay.innerHTML = `
                    <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h4 class="fw-bold">PROCESSANDO VENDA...</h4>
                    <p>Por favor, aguarde um instante.</p>
                `;
                document.body.appendChild(overlay);

                $('#finalizarVenda').modal('hide');
                
                const dados = {
                    total: total,
                    desconto: desconto,
                    paymentMethods: paymentMethods,
                    paymentAmounts: paymentAmounts,
                    pix_txid: pix_txid,
                    tipo_atendimento: tipoAtendimentoEl ? tipoAtendimentoEl.value : null,
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
                    
                    document.getElementById('pdv-lock-overlay')?.remove();
                    
                    if (data.status === 'success') {
                        swal({
                            title: "Venda Finalizada!",
                            text: "O comprovante será gerado em seguida.",
                            icon: "success",
                            buttons: {
                                confirm: {
                                    text: "OK",
                                    value: true,
                                    visible: true,
                                    className: "btn btn-success",
                                    closeModal: true
                                }
                            }
                        }).then(() => {
                            imprimirComprovante(data.venda_id);
                            window.location.href = 'index.php?page=InicioPVD';
                        });
                    } else {
                        throw new Error(data.message || 'Erro ao finalizar a venda');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    document.getElementById('pdv-lock-overlay')?.remove();
                    
                    if (btnSubmit) {
                        btnSubmit.dataset.submitting = 'false';
                        updateRemainingAmount();
                    }
                    
                    swal({
                        title: "Erro ao Finalizar",
                        text: error.message,
                        icon: "error",
                        buttons: { confirm: { className: "btn btn-danger" } }
                    });
                }
            };

            proceedWithFinalization();
        });
    }
    
    // Inicializar carrinho
    updateCart();
});
    

function imprimirComprovante(vendaId) {
    if (!vendaId) {
        console.error('ID da venda não fornecido');
        swal({
            title: "Erro de Impressão",
            text: "Não foi possível gerar o comprovante. ID da venda ausente.",
            icon: "error",
            button: "Fechar"
        });
        return;
    }

    const url = `imprimirVenda.php?id=${encodeURIComponent(vendaId)}`;
    const janelaImpressao = window.open(url, '_blank', 'width=600,height=800');
    
    if (!janelaImpressao) {
        swal({
            title: "Pop-up Bloqueado",
            text: "Permita pop-ups para visualizar o comprovante automaticamente.",
            icon: "warning",
            button: "Entendido"
        }).then(() => {
            window.location.href = url;
        });
    }
}