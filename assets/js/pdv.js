function calcularDesconto() {
    const porcentagemDesconto = parseFloat(document.getElementById('desconto').value) / 100 || 0;
    const totalCompra = parseFloat(document.getElementById('cart-total').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0;

    const valorDesconto = totalCompra * porcentagemDesconto;
    const totalComDesconto = totalCompra - valorDesconto;

    document.getElementById('cart-desconto').textContent = valorDesconto.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('cart-total-compra-com-desconto').textContent = totalComDesconto.toLocaleString('pt-BR', {minimumFractionDigits: 2});
}

document.addEventListener('DOMContentLoaded', function() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const totalCompra = document.getElementById('total-compra');
    const finalizarBtn = document.querySelector('[data-target="#finalizarVenda"]');
    //let cart = JSON.parse(localStorage.getItem('pdv_cart')) || [];
    let cart = [];

    // Atualiza o carrinho na interface e no localStorage
    function updateCart() {
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
        totalCompra.textContent = formattedTotal;
        
        // Atualiza estado do botão de finalizar
        finalizarBtn.disabled = cart.length === 0;
        finalizarBtn.classList.toggle('disabled', cart.length === 0);
        
        calcularDesconto();
        //localStorage.setItem('pdv_cart', JSON.stringify(cart));
    }

    // Adiciona produto ao carrinho
    function addToCart(id, nome, preco) {
        const existing = cart.find(item => item.id === id);
        
        if (existing) {
            existing.qtd++;
        } else {
            cart.push({ id, nome, preco, qtd: 1 });
        }
        
        updateCart();
        showToast(`${nome} adicionado ao carrinho`);
    }

    // Mostra notificação toast
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast show position-fixed bottom-0 end-0 mb-3 me-3';
        toast.innerHTML = `
            <div class="toast-body bg-success text-white">
                ${message}
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Evento para adicionar produtos
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');
            const preco = parseFloat(this.getAttribute('data-preco')) || 0;
            addToCart(id, nome, preco);
        });
    });

    // Eventos do carrinho (quantidade, remover)
    cartItems.addEventListener('click', function(event) {
        const target = event.target.closest('[data-id]');
        if (!target) return;
        
        const id = target.getAttribute('data-id');
        const item = cart.find(item => item.id === id);
        
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

    // Limpar carrinho
    document.getElementById('clear-cart').addEventListener('click', function() {
        if (cart.length > 0 && confirm('Deseja realmente limpar o carrinho?')) {
            cart = [];
            updateCart();
            showToast('Carrinho limpo');
        }
    });

    // Busca de produtos
    const searchInput = document.getElementById('product-search');
    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#product-table tbody tr').forEach(row => {
            const productName = row.querySelector('.product-name').textContent.toLowerCase();
            row.style.display = productName.includes(filter) ? '' : 'none';
        });
    });

    // Finalizar venda (substitua a função existente)
document.getElementById('paymentForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const desconto = parseFloat(document.getElementById('desconto').value) || 0;
    const total = cart.reduce((sum, item) => sum + item.qtd * item.preco, 0);
    const totalComDesconto = parseFloat((total * (1 - desconto / 100)).toFixed(2));
    
    // Validar pagamentos
    let paid = 0;
    paid = parseFloat(paid.toFixed(2));
    const paymentMethods = [];
    const paymentAmounts = [];
    
    document.querySelectorAll('.payment-method').forEach((select, index) => {
        if (!select.value) {
            alert('Selecione todas as formas de pagamento!');
            select.focus();
            throw new Error('Forma de pagamento não selecionada');
        }
        paymentMethods.push(select.value);
    });
    
    document.querySelectorAll('.payment-amount').forEach((input, index) => {
        const value = parseFloat(input.value) || 0;
        paymentAmounts.push(value);
        paid += value;
    });
    
    if (paid < totalComDesconto) {
        alert(`Valor pago (R$ ${paid.toFixed(2)}) é menor que o total com desconto (R$ ${totalComDesconto.toFixed(2)})!`);
        return;
    }

    if (paid > totalComDesconto) {
        alert(`Valor pago (R$ ${paid.toFixed(2)}) é maior que o total com desconto (R$ ${totalComDesconto.toFixed(2)})!`);
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
        alert(error.message);
    }
});
    
    // Inicializa o carrinho
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