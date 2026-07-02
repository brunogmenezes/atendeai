<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

if (!temPermissao('gerenciar_sistema')) {
    echo '<div class="alert alert-danger mt-4"><i class="fas fa-exclamation-triangle me-2"></i>Acesso Negado. Você não tem permissão para gerenciar perfis.</div>';
    exit;
}

// Buscar todos os perfis cadastrados
$stmtPerfis = $pdo->query("SELECT * FROM perfis ORDER BY id ASC");
$perfis = $stmtPerfis->fetchAll(PDO::FETCH_ASSOC);

// Buscar todas as permissões cadastradas para listar no modal
$stmtPermissoes = $pdo->query("SELECT * FROM permissoes ORDER BY nome ASC");
$permissoesDisponiveis = $stmtPermissoes->fetchAll(PDO::FETCH_ASSOC);

// Buscar mapa de permissões de cada perfil para pré-seleção em JS
$perfilPermissoesMapa = [];
foreach ($perfis as $perfil) {
    $stmtMap = $pdo->prepare("SELECT permissao_id FROM perfil_permissoes WHERE perfil_id = :perfil_id");
    $stmtMap->execute([':perfil_id' => $perfil['id']]);
    $perfilPermissoesMapa[$perfil['id']] = $stmtMap->fetchAll(PDO::FETCH_COLUMN);
}

// Definição dos segmentos de permissões para organização visual
$segmentos = [
    'Painel & Vendas' => [
        'icon' => 'fas fa-desktop text-primary',
        'perms' => ['ver_dashboard', 'acessar_pdv', 'listar_vendas', 'estornar_venda']
    ],
    'Estoque & Mercadorias' => [
        'icon' => 'fas fa-box text-warning',
        'perms' => ['listar_produtos', 'editar_produtos', 'listar_compras', 'lancar_compras']
    ],
    'Pessoas & Financeiro' => [
        'icon' => 'fas fa-wallet text-success',
        'perms' => ['listar_clientes', 'editar_clientes', 'listar_financeiro', 'editar_financeiro']
    ],
    'Relatórios & Sistema' => [
        'icon' => 'fas fa-cogs text-secondary',
        'perms' => ['listar_relatorios', 'gerenciar_sistema', 'gerenciar_backup']
    ]
];
?>

<div class="col-md-12">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-1 text-dark fw-bold">Perfis & Grupos de Permissão</h4>
                    <p class="text-muted small mb-0">Controle o nível de acesso e privilégios dos usuários do sistema</p>
                </div>
                <button class="btn btn-primary btn-round open-add-profile-modal" data-bs-toggle="modal" data-bs-target="#profileModal">
                    <i class="fa fa-plus me-1"></i>
                    Criar Novo Perfil
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light text-uppercase font-monospace fs-7 text-secondary">
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 25%">Nome do Perfil</th>
                            <th>Permissões Atribuídas</th>
                            <th style="width: 15%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($perfis): ?>
                            <?php foreach ($perfis as $perfil): ?>
                                <tr>
                                    <td class="fw-bold"><?=$perfil['id'];?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-title rounded-circle bg-primary text-white font-weight-bold">
                                                    <?=strtoupper(substr($perfil['nome'], 0, 2));?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark"><?=htmlspecialchars($perfil['nome']);?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php
                                            // Buscar os nomes das permissões do perfil
                                            $stmtPerms = $pdo->prepare("
                                                SELECT p.descricao 
                                                FROM permissoes p
                                                INNER JOIN perfil_permissoes pp ON pp.permissao_id = p.id
                                                WHERE pp.perfil_id = :perfil_id
                                                ORDER BY p.descricao ASC
                                            ");
                                            $stmtPerms->execute([':perfil_id' => $perfil['id']]);
                                            $perms = $stmtPerms->fetchAll(PDO::FETCH_COLUMN);

                                            if ($perms):
                                                foreach ($perms as $perm):
                                            ?>
                                                    <span class="badge bg-light text-dark border me-1 my-1 px-2 py-1"><i class="fas fa-check-circle text-success me-1"></i><?=htmlspecialchars($perm);?></span>
                                            <?php 
                                                endforeach;
                                            else:
                                            ?>
                                                <span class="text-muted small italic"><i class="fas fa-lock me-1"></i>Nenhuma permissão atribuída</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-button-action justify-content-center">
                                            <button type="button" class="btn btn-link btn-primary open-edit-profile-modal" 
                                                    data-id="<?=$perfil['id'];?>" 
                                                    data-nome="<?=htmlspecialchars($perfil['nome']);?>"
                                                    data-bs-toggle="modal" data-bs-target="#profileModal">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-link btn-danger open-delete-profile-modal" 
                                                    data-id="<?=$perfil['id'];?>" 
                                                    data-nome="<?=htmlspecialchars($perfil['nome']);?>"
                                                    data-bs-toggle="modal" data-bs-target="#deleteProfileModal">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Nenhum perfil cadastrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Unificado de Cadastro/Edição de Perfil -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="profileForm" action="formSalvarPerfil.php" method="POST">
                <input type="hidden" name="id" id="profile_id" value="">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title text-dark fw-bold" id="profileModalTitle">
                        <i class="fas fa-user-shield me-2 text-primary"></i>Novo Perfil de Acesso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group form-group-default mb-4">
                        <label class="text-uppercase fw-bold font-monospace text-secondary fs-7">Nome do Perfil / Grupo</label>
                        <input id="profile_nome" name="nome" type="text" class="form-control text-dark fw-bold" placeholder="Ex: Gerente Financeiro, Operador de Caixa" required/>
                    </div>

                    <h6 class="text-uppercase fw-bold font-monospace text-secondary fs-7 mb-3 border-bottom pb-2">
                        <i class="fas fa-list-check me-1"></i>Selecione as Permissões do Grupo
                    </h6>
                    
                    <style>
                        .permission-item {
                            transition: background-color 0.15s ease-in-out;
                            border-radius: 4px;
                            padding: 6px 8px;
                            margin-bottom: 4px;
                        }
                        .permission-item:hover {
                            background-color: rgba(0, 0, 0, 0.03);
                        }
                        .permission-item:last-child {
                            border-bottom: 0 !important;
                            margin-bottom: 0 !important;
                        }
                    </style>
                    <div class="row g-3">
                        <?php 
                        // Acompanhar quais permissões já foram renderizadas para controle
                        $permsRenderizadas = [];
                        foreach ($segmentos as $segInfo) {
                            $permsRenderizadas = array_merge($permsRenderizadas, $segInfo['perms']);
                        }

                        // Loop pelos segmentos definidos
                        foreach ($segmentos as $tituloSegmento => $segInfo): 
                        ?>
                            <div class="col-md-6">
                                <div class="card border border-light-subtle shadow-none h-100 mb-0 bg-white">
                                    <div class="card-header bg-light py-2 px-3 border-0">
                                        <h6 class="fw-bold m-0 text-dark" style="font-size: 0.8rem;">
                                            <i class="<?=$segInfo['icon'];?> me-2"></i><?=$tituloSegmento;?>
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php 
                                        $countSeg = 0;
                                        foreach ($permissoesDisponiveis as $permissao) {
                                            if (in_array($permissao['nome'], $segInfo['perms'])) {
                                                $countSeg++;
                                        ?>
                                                <div class="permission-item d-flex align-items-start border-bottom border-light-subtle">
                                                    <input class="permission-checkbox mt-1 me-2" type="checkbox" name="permissoes[]" 
                                                           value="<?=$permissao['id'];?>" id="perm_<?=$permissao['id'];?>"
                                                           style="width: 15px; height: 15px; flex-shrink: 0; cursor: pointer;">
                                                    <label class="cursor-pointer m-0 w-100" for="perm_<?=$permissao['id'];?>" style="line-height: 1.3;">
                                                        <strong class="text-dark d-block fw-semibold" style="font-size: 0.8rem;"><?=htmlspecialchars($permissao['descricao']);?></strong>
                                                        <span class="text-muted d-block" style="font-size: 0.7rem;"><?=htmlspecialchars($permissao['nome']);?></span>
                                                    </label>
                                                </div>
                                        <?php 
                                            }
                                        }
                                        if ($countSeg === 0) {
                                            echo '<span class="text-muted small italic">Nenhuma permissão neste segmento</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php
                        // Outras permissões (caso existam e não estejam categorizadas)
                        $outrasPerms = [];
                        foreach ($permissoesDisponiveis as $permissao) {
                            if (!in_array($permissao['nome'], $permsRenderizadas)) {
                                $outrasPerms[] = $permissao;
                            }
                        }
                        if (!empty($outrasPerms)):
                        ?>
                            <div class="col-md-6">
                                <div class="card border border-light-subtle shadow-none h-100 mb-0 bg-white">
                                    <div class="card-header bg-light py-2 px-3 border-0">
                                        <h6 class="fw-bold m-0 text-dark" style="font-size: 0.8rem;">
                                            <i class="fas fa-question-circle text-muted me-2"></i>Outras Permissões
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php foreach ($outrasPerms as $permissao): ?>
                                            <div class="permission-item d-flex align-items-start border-bottom border-light-subtle">
                                                <input class="permission-checkbox mt-1 me-2" type="checkbox" name="permissoes[]" 
                                                       value="<?=$permissao['id'];?>" id="perm_<?=$permissao['id'];?>"
                                                       style="width: 15px; height: 15px; flex-shrink: 0; cursor: pointer;">
                                                <label class="cursor-pointer m-0 w-100" for="perm_<?=$permissao['id'];?>" style="line-height: 1.3;">
                                                    <strong class="text-dark d-block fw-semibold" style="font-size: 0.8rem;"><?=htmlspecialchars($permissao['descricao']);?></strong>
                                                    <span class="text-muted d-block" style="font-size: 0.7rem;"><?=htmlspecialchars($permissao['nome']);?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Salvar Alterações</button>
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="formSalvarPerfil.php" method="POST">
                <input type="hidden" name="id" id="delete_profile_id">
                <input type="hidden" name="action" value="excluir">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-trash me-2"></i>Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza de que deseja excluir o perfil <strong id="delete_profile_name" class="text-dark"></strong>?</p>
                    <p class="text-warning small mb-0"><i class="fas fa-exclamation-triangle me-1"></i><b>Atenção:</b> Colaboradores que utilizam este perfil perderão o acesso a essas permissões.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Excluir Perfil</button>
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mapa de permissões por perfil gerado pelo PHP
const perfilPermissoesMapa = <?=json_encode($perfilPermissoesMapa);?>;

document.addEventListener('DOMContentLoaded', function () {
    const profileModal = document.getElementById('profileModal');
    const profileForm = document.getElementById('profileForm');
    const profileIdInput = document.getElementById('profile_id');
    const profileNomeInput = document.getElementById('profile_nome');
    const profileModalTitle = document.getElementById('profileModalTitle');
    const checkboxes = document.querySelectorAll('.permission-checkbox');

    // Ao abrir modal para criar novo
    document.querySelectorAll('.open-add-profile-modal').forEach(button => {
        button.addEventListener('click', function () {
            profileIdInput.value = '';
            profileNomeInput.value = '';
            profileModalTitle.innerHTML = '<i class="fas fa-user-shield me-2 text-primary"></i>Novo Perfil de Acesso';
            
            // Desmarcar todos os checkboxes
            checkboxes.forEach(cb => cb.checked = false);
        });
    });

    // Ao abrir modal para editar existente
    document.querySelectorAll('.open-edit-profile-modal').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');

            profileIdInput.value = id;
            profileNomeInput.value = nome;
            profileModalTitle.innerHTML = '<i class="fas fa-user-shield me-2 text-warning"></i>Editar Perfil: ' + nome;

            // Desmarcar todos os checkboxes primeiro
            checkboxes.forEach(cb => cb.checked = false);

            // Marcar os checkboxes do perfil selecionado
            const permsAtribuidas = perfilPermissoesMapa[id] || [];
            permsAtribuidas.forEach(permId => {
                const cb = document.getElementById('perm_' + permId);
                if (cb) {
                    cb.checked = true;
                }
            });
        });
    });

    // Ao abrir modal para excluir
    document.querySelectorAll('.open-delete-profile-modal').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');

            document.getElementById('delete_profile_id').value = id;
            document.getElementById('delete_profile_name').innerText = nome;
        });
    });
});
</script>
