<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

$produtos = BuscarporTabela('produtos');
?>

<div class="page-header">
    <h3 class="fw-bold mb-3">Nova Compra / Entrada de Estoque</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="index.php">
                <i class="fa fa-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="fa fa-angle-right"></i>
        </li>
        <li class="nav-item">
            <a href="index.php?page=ListarCompras">Compras</a>
        </li>
        <li class="separator">
            <i class="fa fa-angle-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Nova Compra</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Produtos da Compra</div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Selecionar Produto</label>
                        <select id="select-produto" class="form-select select2">
                            <option value="">Pesquise o produto...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?= $p['id'] ?>" data-nome="<?= htmlspecialchars($p['nome']) ?>" data-preco="<?= $p['preco_custo'] ?>">
                                    <?= $p['id'] ?> - <?= htmlspecialchars($p['nome']) ?> (Estoque: <?= $p['quantidade'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qtd</label>
                        <input type="number" id="input-qtd" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Custo Unitário (R$)</label>
                        <input type="number" id="input-custo" class="form-control" step="0.01" placeholder="0,00">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="btn-add-item" class="btn btn-primary w-100">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="table-itens-compra">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Custo Unit.</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Itens adicionados aparecerão aqui -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Resumo e Despesas</div>
            </div>
            <div class="card-body">
                <form id="form-finalizar-compra">
                    <div class="mb-3">
                        <label class="form-label">Fornecedor</label>
                        <input type="text" name="fornecedor" class="form-control" placeholder="Nome do Fornecedor">
                    </div>

                    <?php $contas = BuscarporTabela('contas'); ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Contas para Pagamento</label>
                            <button type="button" id="btn-add-pagamento" class="btn btn-xs btn-outline-info">
                                <i class="fa fa-plus"></i> Conta
                            </button>
                        </div>
                        <div id="container-pagamentos">
                            <!-- Pagamento inicial -->
                            <div class="row g-2 mb-2 pagamento-row align-items-center">
                                <div class="col-7">
                                    <select class="form-select form-select-sm pagamento-conta" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($contas as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="number" class="form-control form-control-sm pagamento-valor" step="0.01" placeholder="0,00">
                                </div>
                                <div class="col-1 text-center">
                                    <button type="button" class="btn btn-link btn-danger p-0" onclick="this.closest('.pagamento-row').remove();">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Despesas Adicionais</label>
                            <button type="button" id="btn-add-despesa" class="btn btn-xs btn-outline-primary">
                                <i class="fa fa-plus"></i> Despesa
                            </button>
                        </div>
                        <div id="container-despesas">
                            <!-- Despesas dinâmicas aparecerão aqui -->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data da Compra</label>
                        <input type="date" name="data_compra" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Total Produtos</span>
                            <span class="fw-bold" id="resumo-produtos">R$ 0,00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Total Despesas</span>
                            <span class="fw-bold text-danger" id="resumo-despesas">R$ 0,00</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="h5 mb-0">TOTAL GERAL</span>
                            <span class="h5 mb-0 fw-bold text-primary" id="resumo-total">R$ 0,00</span>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="fa fa-info-circle me-1"></i> As despesas serão diluídas no custo unitário de cada produto.
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm" id="btn-salvar-compra">
                        <i class="fa fa-check-circle me-2"></i> SALVAR COMPRA
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAdd = document.getElementById('btn-add-item');
    const tableBody = document.querySelector('#table-itens-compra tbody');
    const selectProduto = document.getElementById('select-produto');
    const inputQtd = document.getElementById('input-qtd');
    const inputCusto = document.getElementById('input-custo');
    
    const btnAddDespesa = document.getElementById('btn-add-despesa');
    const containerDespesas = document.getElementById('container-despesas');
    
    const totalProdutosEl = document.getElementById('resumo-produtos');
    const totalDespesasEl = document.getElementById('resumo-despesas');
    const totalGeralEl = document.getElementById('resumo-total');

    const btnAddPagamento = document.getElementById('btn-add-pagamento');
    const containerPagamentos = document.getElementById('container-pagamentos');

    // Opções de contas para o JS
    const contasOptions = `
        <option value="">Selecione...</option>
        <?php foreach ($contas as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
        <?php endforeach; ?>
    `;

    // Adicionar linha de pagamento
    btnAddPagamento.addEventListener('click', function() {
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 pagamento-row align-items-center';
        div.innerHTML = `
            <div class="col-7">
                <select class="form-select form-select-sm pagamento-conta" required>
                    ${contasOptions}
                </select>
            </div>
            <div class="col-4">
                <input type="number" class="form-control form-control-sm pagamento-valor" step="0.01" placeholder="0,00">
            </div>
            <div class="col-1 text-center">
                <button type="button" class="btn btn-link btn-danger p-0" onclick="this.closest('.pagamento-row').remove();">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `;
        containerPagamentos.appendChild(div);
    });

    let itensCompra = [];

    // Adicionar linha de despesa extra
    btnAddDespesa.addEventListener('click', function() {
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 despesa-row align-items-center';
        div.innerHTML = `
            <div class="col-7">
                <input type="text" class="form-control form-control-sm despesa-nome" placeholder="Ex: Frete, Seguro...">
            </div>
            <div class="col-4">
                <input type="number" class="form-control form-control-sm despesa-valor" step="0.01" value="0.00">
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-link btn-danger p-0" onclick="this.closest('.despesa-row').remove(); window.updateTotals();">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `;
        containerDespesas.appendChild(div);
        
        // Listener para recalcular quando o valor da despesa muda
        div.querySelector('.despesa-valor').addEventListener('input', updateTotals);
    });

    selectProduto.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.dataset.preco) {
            inputCusto.value = option.dataset.preco;
        }
    });

    btnAdd.addEventListener('click', function() {
        const prodId = selectProduto.value;
        const option = selectProduto.options[selectProduto.selectedIndex];
        const qtd = parseInt(inputQtd.value);
        const custo = parseFloat(inputCusto.value);

        if (!prodId || qtd <= 0 || isNaN(custo)) {
            $.notify({
                icon: 'fas fa-exclamation-circle',
                title: 'Atenção',
                message: 'Preencha os dados do produto corretamente!',
            },{
                type: 'warning',
                placement: {
                    from: "top",
                    align: "right"
                },
                time: 1000,
            });
            return;
        }

        const subtotal = qtd * custo;
        const item = {
            id: prodId,
            nome: option.dataset.nome,
            qtd: qtd,
            custo: custo,
            subtotal: subtotal
        };

        itensCompra.push(item);
        renderTable();
        
        selectProduto.value = '';
        inputQtd.value = 1;
        inputCusto.value = '';
    });

    function renderTable() {
        tableBody.innerHTML = '';
        let totalProdutos = 0;
        
        // Calcular total de despesas dinâmicas
        let totalDespesas = 0;
        document.querySelectorAll('.despesa-valor').forEach(input => {
            totalDespesas += parseFloat(input.value) || 0;
        });

        itensCompra.forEach(item => totalProdutos += item.subtotal);

        const totalGeral = totalProdutos + totalDespesas;
        const fatorDiluicao = totalProdutos > 0 ? totalDespesas / totalProdutos : 0;

        itensCompra.forEach((item, index) => {
            const custoRealUnitario = item.custo * (1 + fatorDiluicao);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    ${item.nome}
                    <div class="small text-muted">Custo Base: R$ ${item.custo.toFixed(2)}</div>
                </td>
                <td class="text-center">${item.qtd}</td>
                <td class="text-end">R$ ${item.custo.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td class="text-end fw-bold text-success">
                    R$ ${custoRealUnitario.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    <div class="small text-muted font-weight-normal">Real Diluído</div>
                </td>
                <td class="text-center">
                    <button class="btn btn-link btn-danger p-0" onclick="removeItem(${index})">
                        <i class="fa fa-times"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        totalProdutosEl.textContent = 'R$ ' + totalProdutos.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        totalDespesasEl.textContent = 'R$ ' + totalDespesas.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        totalGeralEl.textContent = 'R$ ' + totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2});

        if (itensCompra.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-4">Nenhum item adicionado</td></tr>';
        }
    }

    // Expor função globalmente para o botão remover despesa
    window.updateTotals = renderTable;
    window.removeItem = function(index) {
        itensCompra.splice(index, 1);
        renderTable();
    };

    renderTable();

    const btnSalvarCompra = document.getElementById('btn-salvar-compra');

    document.getElementById('form-finalizar-compra').addEventListener('submit', function(e) {
        e.preventDefault();
        if (itensCompra.length === 0) {
            $.notify({
                icon: 'fas fa-shopping-cart',
                title: 'Carrinho Vazio',
                message: 'Adicione pelo menos um produto!',
            },{
                type: 'danger',
                placement: {
                    from: "top",
                    align: "right"
                },
                time: 1000,
            });
            return;
        }

        const totalProdutos = itensCompra.reduce((sum, i) => sum + i.subtotal, 0);
        let totalDespesas = 0;
        const despesasExtras = [];
        
        document.querySelectorAll('.despesa-row').forEach(row => {
            const nome = row.querySelector('.despesa-nome').value || 'Despesa';
            const valor = parseFloat(row.querySelector('.despesa-valor').value) || 0;
            if (valor > 0) {
                totalDespesas += valor;
                despesasExtras.push({ nome, valor });
            }
        });

        const fatorDiluicao = totalProdutos > 0 ? totalDespesas / totalProdutos : 0;
        const totalGeral = totalProdutos + totalDespesas;

        // Coletar pagamentos
        const pagamentos = [];
        let totalPago = 0;
        let erroPagamento = false;

        document.querySelectorAll('.pagamento-row').forEach(row => {
            const contaId = row.querySelector('.pagamento-conta').value;
            const valor = parseFloat(row.querySelector('.pagamento-valor').value) || 0;
            
            if (!contaId || valor <= 0) {
                erroPagamento = true;
            } else {
                pagamentos.push({ conta_id: contaId, valor: valor });
                totalPago += valor;
            }
        });

        if (erroPagamento || pagamentos.length === 0) {
            $.notify({
                icon: 'fas fa-wallet',
                title: 'Pagamento',
                message: 'Preencha os dados de pagamento corretamente (conta e valor > 0)!',
            },{
                type: 'warning',
                placement: {
                    from: "top",
                    align: "right"
                },
                time: 1000,
            });
            return;
        }

        // Validar se o total pago bate com o total geral (com pequena margem de erro para floats)
        if (Math.abs(totalPago - totalGeral) > 0.01) {
            $.notify({
                icon: 'fas fa-calculator',
                title: 'Divergência de Valores',
                message: 'O total dos pagamentos (R$ ' + totalPago.toFixed(2) + ') deve ser igual ao total geral da compra (R$ ' + totalGeral.toFixed(2) + ')!',
            },{
                type: 'danger',
                placement: {
                    from: "top",
                    align: "right"
                },
                time: 3000,
            });
            return;
        }

        const dados = {
            fornecedor: this.fornecedor.value,
            pagamentos: pagamentos,
            data_compra: this.data_compra.value,
            despesas_extras: despesasExtras,
            total_geral: totalGeral,
            itens: itensCompra.map(item => ({
                ...item,
                custo_diluido: item.custo * (1 + fatorDiluicao)
            }))
        };

        // Bloquear botão para evitar duplos cliques
        btnSalvarCompra.disabled = true;
        btnSalvarCompra.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> PROCESSANDO...';

        // Enviar para o servidor
        fetch('finalizar_compra_estoque.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                swal({
                    title: "Compra Salva!",
                    text: data.message,
                    icon: "success",
                }).then(() => {
                    window.location.href = 'index.php?page=ListarCompras';
                });
            } else {
                swal("Erro!", data.message, "error");
                btnSalvarCompra.disabled = false;
                btnSalvarCompra.innerHTML = '<i class="fa fa-check-circle me-2"></i> SALVAR COMPRA';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            swal("Erro!", "Ocorreu uma falha ao processar a compra.", "error");
            btnSalvarCompra.disabled = false;
            btnSalvarCompra.innerHTML = '<i class="fa fa-check-circle me-2"></i> SALVAR COMPRA';
        });
    });
});
</script>
