<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();
?>

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <h4 class="card-title">Dados da Empresa</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="empresas-table" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Endereço</th>
                            <th>Telefone</th>
                            <th>Última Atualização</th>
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
            
                        $empresas = buscarTabela('empresa', $filtro, $valor, $limite, $offset);
                        $totalEmpresas = contarNumeroPorTabela('empresa', $filtro, $valor);
                        $totalPaginas = ceil($totalEmpresas / $limite);
            
                        if ($empresas) {
                            foreach ($empresas as $empresa) {
                        ?>
                                <tr>
                                    <td><?= $empresa['id'] ?></td>
                                    <td><?= htmlspecialchars($empresa['nome']) ?></td>
                                    <td><?= formatarCNPJ($empresa['cnpj']) ?></td>
                                    <td><?= htmlspecialchars($empresa['endereco']) ?></td>
                                    <td><?= formatarTelefone($empresa['telefone']) ?></td>
                                    <td><?= $empresa['data_atualizacao'] ? date('d/m/Y H:i:s', strtotime($empresa['data_atualizacao'])) : "-" ?></td>
                                    <td>
                                        <div class="form-button-action">
                                            <button type="button" class="btn btn-link btn-primary open-edit-modal" 
                                                data-id="<?= $empresa['id'] ?>"
                                                data-nome="<?= htmlspecialchars($empresa['nome']) ?>"
                                                data-cnpj="<?= $empresa['cnpj'] ?>"
                                                data-endereco="<?= htmlspecialchars($empresa['endereco']) ?>"
                                                data-telefone="<?= $empresa['telefone'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#editEmpresaModal">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="demo">
                            <ul class="pagination pg-primary">
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir os dados desta empresa?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="excluirEmpresa.php" method="POST">
                    <input type="hidden" name="id" id="empresaIdToDelete">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cadastro de Empresa -->
<div class="modal fade" id="addEmpresaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Cadastrar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="cadastrarEmpresa.php" method="POST">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Nome</label>
                                <input name="nome" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>CNPJ</label>
                                <input name="cnpj" type="text" class="form-control cnpj-mask" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Endereço</label>
                                <input name="endereco" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Telefone</label>
                                <input name="telefone" type="text" class="form-control phone-mask">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição de Empresa -->
<div class="modal fade" id="editEmpresaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Editar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="editarEmpresa.php" method="POST">
                    <div class="row">
                        <input type="hidden" name="id" id="empresaIdEdit">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Nome</label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>CNPJ</label>
                                <input type="text" class="form-control cnpj-mask" id="edit_cnpj" name="cnpj" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Endereço</label>
                                <input type="text" class="form-control" id="edit_endereco" name="endereco" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Telefone</label>
                                <input type="text" class="form-control phone-mask" id="edit_telefone" name="telefone">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configuração da exclusão
    const deleteButtons = document.querySelectorAll('.open-delete-modal');
    const empresaIdInput = document.getElementById('empresaIdToDelete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            empresaIdInput.value = this.getAttribute('data-id');
        });
    });

    // Configuração da edição
    const editButtons = document.querySelectorAll('.open-edit-modal');
    const empresaIdEdit = document.getElementById('empresaIdEdit');
    const empresaNomeEdit = document.getElementById('edit_nome');
    const empresaCnpjEdit = document.getElementById('edit_cnpj');
    const empresaEnderecoEdit = document.getElementById('edit_endereco');
    const empresaTelefoneEdit = document.getElementById('edit_telefone');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            empresaIdEdit.value = this.getAttribute('data-id');
            empresaNomeEdit.value = this.getAttribute('data-nome');
            empresaCnpjEdit.value = this.getAttribute('data-cnpj');
            empresaEnderecoEdit.value = this.getAttribute('data-endereco');
            empresaTelefoneEdit.value = this.getAttribute('data-telefone');
        });
    });

    // Máscaras para os campos
    $('.cnpj-mask').inputmask('99.999.999/9999-99');
    $('.phone-mask').inputmask('(99) 9999-9999[9]');
});
</script>