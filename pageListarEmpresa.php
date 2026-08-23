<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();
?>

<div class="col-md-12">
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['message'] ?? 'Operação realizada com sucesso!') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['status'] === 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_GET['message'] ?? 'Ocorreu um erro ao processar a operação.') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0"><i class="fas fa-building text-primary me-2"></i>Dados da Empresa</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="empresas-table" class="display table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 80px;">Logo</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Endereço</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Última Atualização</th>
                            <th style="width: 80px;" class="text-center">Ações</th>
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
                                $logoPath = !empty($empresa['logo']) ? 'uploads/' . $empresa['logo'] : '';
                                $hasLogo = !empty($logoPath) && file_exists(__DIR__ . '/' . $logoPath);
                        ?>
                                <tr>
                                    <td><strong>#<?= $empresa['id'] ?></strong></td>
                                    <td>
                                        <?php if ($hasLogo): ?>
                                            <div class="p-1 border rounded bg-white d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                                                <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo <?= htmlspecialchars($empresa['nome']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            </div>
                                        <?php else: ?>
                                            <div class="border rounded bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;" title="Sem logo cadastrada">
                                                <i class="fas fa-image fa-lg text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($empresa['nome']) ?></strong></td>
                                    <td><?= formatarCNPJ($empresa['cnpj']) ?></td>
                                    <td><?= htmlspecialchars($empresa['endereco']) ?></td>
                                    <td><?= formatarTelefone($empresa['telefone']) ?></td>
                                    <td><?= !empty($empresa['email']) ? htmlspecialchars($empresa['email']) : '<span class="text-muted">-</span>' ?></td>
                                    <td><?= $empresa['data_atualizacao'] ? date('d/m/Y H:i', strtotime($empresa['data_atualizacao'])) : "-" ?></td>
                                    <td class="text-center">
                                        <div class="form-button-action">
                                            <button type="button" class="btn btn-sm btn-primary open-edit-modal" 
                                                data-id="<?= $empresa['id'] ?>"
                                                data-nome="<?= htmlspecialchars($empresa['nome']) ?>"
                                                data-cnpj="<?= $empresa['cnpj'] ?>"
                                                data-endereco="<?= htmlspecialchars($empresa['endereco']) ?>"
                                                data-telefone="<?= $empresa['telefone'] ?>"
                                                data-email="<?= htmlspecialchars($empresa['email'] ?? '') ?>"
                                                data-logo="<?= htmlspecialchars($empresa['logo'] ?? '') ?>"
                                                data-bs-toggle="modal" data-bs-target="#editEmpresaModal"
                                                title="Editar Dados e Logo">
                                                <i class="fa fa-edit me-1"></i> Editar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                    Nenhum registro de empresa encontrado.
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
            <div class="col-md-12 mt-3">
                <ul class="pagination pg-primary justify-content-center">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&valor=<?= urlencode($valor) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Edição de Empresa -->
<div class="modal fade" id="editEmpresaModal" tabindex="-1" role="dialog" aria-labelledby="editEmpresaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmpresaModalLabel"><i class="fas fa-building text-primary me-2"></i>Editar Dados da Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="editarEmpresa.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="empresaIdEdit">
                    <input type="hidden" name="remover_logo" id="edit_remover_logo" value="0">

                    <!-- Seção da Logo da Empresa -->
                    <div class="card mb-4 border" style="background-color: #f8fafc;">
                        <div class="card-body">
                            <label class="form-label fw-bold text-dark d-block mb-2">
                                <i class="fas fa-image text-primary me-1"></i> Logo da Empresa
                            </label>
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div id="logo-preview-container" class="border rounded bg-white d-flex align-items-center justify-content-center p-2" style="width: 110px; height: 110px; box-shadow: 0 2px 4px rgba(0,0,0,0.06);">
                                        <img id="edit_logo_preview" src="" alt="Prévia da Logo" style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                                        <div id="edit_logo_placeholder" class="text-center text-muted">
                                            <i class="fas fa-image fa-2x mb-1 text-secondary"></i>
                                            <div style="font-size: 10px;">Sem logo</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-2">
                                        <input type="file" class="form-control" id="edit_logo" name="logo" accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml">
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-info-circle me-1"></i> Formatos recomendados: <strong>PNG, JPG, WEBP ou SVG</strong> (fundo transparente ou claro). A logo será exibida na <strong>tela de login</strong> e no <strong>topo do sistema</strong>.
                                    </div>
                                    <div id="btn-remover-logo-container" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remover-logo">
                                            <i class="fas fa-trash-alt me-1"></i> Remover logo atual
                                        </button>
                                        <span id="txt-logo-removida" class="text-danger small ms-2 fw-semibold" style="display: none;">
                                            (A logo será excluída ao salvar)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Dados da Empresa -->
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-semibold">Nome / Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required placeholder="Nome da empresa">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control cnpj-mask" id="edit_cnpj" name="cnpj" required placeholder="00.000.000/0000-00">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Endereço Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_endereco" name="endereco" required placeholder="Rua, número, bairro, cidade - UF">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Telefone / WhatsApp <span class="text-muted fw-normal small">(Opcional)</span></label>
                            <input type="text" class="form-control phone-mask" id="edit_telefone" name="telefone" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">E-mail <span class="text-muted fw-normal small">(Opcional)</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" placeholder="contato@empresa.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configuração da edição
    const editButtons = document.querySelectorAll('.open-edit-modal');
    const empresaIdEdit = document.getElementById('empresaIdEdit');
    const empresaNomeEdit = document.getElementById('edit_nome');
    const empresaCnpjEdit = document.getElementById('edit_cnpj');
    const empresaEnderecoEdit = document.getElementById('edit_endereco');
    const empresaTelefoneEdit = document.getElementById('edit_telefone');
    const empresaEmailEdit = document.getElementById('edit_email');
    const editLogoInput = document.getElementById('edit_logo');
    const editLogoPreview = document.getElementById('edit_logo_preview');
    const editLogoPlaceholder = document.getElementById('edit_logo_placeholder');
    const btnRemoverLogoContainer = document.getElementById('btn-remover-logo-container');
    const btnRemoverLogo = document.getElementById('btn-remover-logo');
    const editRemoverLogoInput = document.getElementById('edit_remover_logo');
    const txtLogoRemovida = document.getElementById('txt-logo-removida');

    let currentLogoFile = '';

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome') || '';
            const cnpj = this.getAttribute('data-cnpj') || '';
            const endereco = this.getAttribute('data-endereco') || '';
            const telefone = this.getAttribute('data-telefone') || '';
            const email = this.getAttribute('data-email') || '';
            const logo = this.getAttribute('data-logo') || '';

            empresaIdEdit.value = id;
            empresaNomeEdit.value = nome;
            empresaCnpjEdit.value = cnpj;
            empresaEnderecoEdit.value = endereco;
            empresaTelefoneEdit.value = telefone;
            if (empresaEmailEdit) empresaEmailEdit.value = email;

            // Resetar controles de logo
            currentLogoFile = logo;
            editRemoverLogoInput.value = '0';
            if (editLogoInput) editLogoInput.value = '';
            if (txtLogoRemovida) txtLogoRemovida.style.display = 'none';

            if (logo && logo.trim() !== '') {
                editLogoPreview.src = 'uploads/' + logo;
                editLogoPreview.style.display = 'block';
                editLogoPlaceholder.style.display = 'none';
                btnRemoverLogoContainer.style.display = 'block';
                if (btnRemoverLogo) {
                    btnRemoverLogo.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Remover logo atual';
                    btnRemoverLogo.classList.remove('btn-danger');
                    btnRemoverLogo.classList.add('btn-outline-danger');
                }
            } else {
                editLogoPreview.src = '';
                editLogoPreview.style.display = 'none';
                editLogoPlaceholder.style.display = 'block';
                btnRemoverLogoContainer.style.display = 'none';
            }
        });
    });

    // Preview ao selecionar novo arquivo
    if (editLogoInput) {
        editLogoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    editLogoPreview.src = e.target.result;
                    editLogoPreview.style.display = 'block';
                    editLogoPlaceholder.style.display = 'none';
                    editRemoverLogoInput.value = '0';
                    if (txtLogoRemovida) txtLogoRemovida.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Botão de remoção de logo
    if (btnRemoverLogo) {
        btnRemoverLogo.addEventListener('click', function () {
            if (editRemoverLogoInput.value === '0') {
                editRemoverLogoInput.value = '1';
                editLogoInput.value = '';
                editLogoPreview.style.display = 'none';
                editLogoPlaceholder.style.display = 'block';
                if (txtLogoRemovida) txtLogoRemovida.style.display = 'inline';
                btnRemoverLogo.innerHTML = '<i class="fas fa-undo me-1"></i> Desfazer remoção';
                btnRemoverLogo.classList.remove('btn-outline-danger');
                btnRemoverLogo.classList.add('btn-outline-secondary');
            } else {
                editRemoverLogoInput.value = '0';
                if (txtLogoRemovida) txtLogoRemovida.style.display = 'none';
                if (currentLogoFile) {
                    editLogoPreview.src = 'uploads/' + currentLogoFile;
                    editLogoPreview.style.display = 'block';
                    editLogoPlaceholder.style.display = 'none';
                }
                btnRemoverLogo.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Remover logo atual';
                btnRemoverLogo.classList.remove('btn-outline-secondary');
                btnRemoverLogo.classList.add('btn-outline-danger');
            }
        });
    }

    // Máscaras para os campos se jQuery inputmask estiver disponível
    if (typeof $ !== 'undefined' && $.fn.inputmask) {
        $('.cnpj-mask').inputmask('99.999.999/9999-99');
        $('.phone-mask').inputmask('(99) 9999-9999[9]');
    }
});
</script>