<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();
?>
<div class="container mt-5">
    <h1 class="text-center mb-4">Gerencie seus Produtos</h1>

    <!-- Botão para abrir o Modal de Cadastro -->
    <div class="mb-4">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cadastroModal">Cadastrar Produto</button>
    </div>

    <!-- Formulário de Busca -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select class="form-select" name="filtro">
                    <option value="nome">Nome</option>
                    <option value="id">ID</option>
                    <option value="preco">Preço</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" name="valor" placeholder="Digite o termo para busca">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </div>
        <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>">
    </form>

    <!-- Tabela de Produtos -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Preço Custo</th>
                    <th>QTD Estoque</th>
                    <th>Custo Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtro = $_GET['filtro'] ?? '';
                $valor = $_GET['valor'] ?? '';
                $pagina = $_GET['pagina'] ?? 1;
                $limite = 10;
                $offset = ($pagina - 1) * $limite;

                // Buscar produtos com paginação
                $produtos = buscarProdutos($filtro, $valor, $limite, $offset);
                $totalProdutos = contarProdutos($filtro, $valor);
                $totalPaginas = ceil($totalProdutos / $limite);

                if ($produtos) {
                    foreach ($produtos as $produto) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($produto['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($produto['nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($produto['descricao']) . "</td>";
                        echo "<td>R$ " . number_format($produto['preco'], 2, ',', '.') . "</td>";
                        echo "<td>" . htmlspecialchars($produto['quantidade']) . "</td>";
                        echo "<td>R$ " . number_format($produto['quantidade']*$produto['preco'], 2, ',', '.') . "</td>";
                        echo '<td>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalImagem" 
            onclick="buscarImagem(' . $produto['id'] . ', \'' . htmlspecialchars($produto['imagem']) . '\')">
        Imagem
    </button>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cadastroModal" 
            onclick="editarProduto(' . $produto['id'] . ')">Editar
    </button>
    <form action="excluirProduto.php" method="POST" style="display:inline;">
        <input type="hidden" name="id" value="' . $produto['id'] . '">
        <input type="hidden" name="imagem" value="' . htmlspecialchars($produto['imagem']) . '">
        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
    </form>
</td>';

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Nenhum produto encontrado</td></tr>";
                }
                ?>
<div class="modal fade" id="modalImagem" tabindex="-1" aria-labelledby="modalImagemLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImagemLabel">Atualizar Imagem do Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formImagem" action="atualizarImagem.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="produtoId" name="id">
                    <div class="mb-3 text-center">
                        <img id="imagemAtual" src="" alt="Imagem do Produto" class="img-fluid mb-3" style="max-width: 100%; max-height: 200px; object-fit: cover;">
                    </div>
                    <div class="mb-3">
                        <label for="imagem" class="form-label">Selecionar Nova Imagem</label>
                        <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Atualizar Imagem</button>
                </form>
            </div>
        </div>
    </div>
</div>


            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo htmlspecialchars($_GET['page'] ?? ''); ?>&pagina=<?php echo $i; ?>&filtro=<?php echo urlencode($filtro); ?>&valor=<?php echo urlencode($valor); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Modal de Cadastro/Editar Produto -->
<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="cadastroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cadastroModalLabel">Cadastrar/Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="cadastrarProduto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="idProduto" name="id">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço</label>
                        <input type="text" class="form-control" id="preco" name="preco" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" required>
                    </div>
                    <div class="mb-3">
                        <label for="imagem" class="form-label">Imagem</label>
                        <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function editarProduto(id) {
        fetch(`buscarProduto.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('idProduto').value = data.id;
                document.getElementById('nome').value = data.nome;
                document.getElementById('descricao').value = data.descricao;
                document.getElementById('preco').value = data.preco;
                document.getElementById('quantidade').value = data.quantidade;
            })
            .catch(error => console.error('Erro ao carregar produto:', error));
    }
</script>
<script type="text/javascript">
function buscarImagem(id, caminhoImagem) {

    // Atualiza o campo oculto com o ID do produto
    document.getElementById('produtoId').value = id;

    // Atualiza a exibição da imagem no modal
    const imagem = document.getElementById('imagemAtual');
    if (caminhoImagem) {
        imagem.src = "uploads/"+caminhoImagem;
        imagem.style.display = 'block';
    } else {
        imagem.style.display = 'none';
    }
}

</script>

