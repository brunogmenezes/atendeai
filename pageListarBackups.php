<?php
/**
 * pageListarBackups.php
 * Interface de gestão de backups do banco de dados.
 * Exibe os 7 backups semanais, permite gerar, baixar e restaurar.
 */
include('config.php');
include('funcoes.php');
require_once 'auth.php';
verificarSessao();

if (!temPermissao('visualizar_backup')) {
    echo '<div class="alert alert-danger mt-4"><i class="fas fa-exclamation-triangle me-2"></i><b>Acesso Negado.</b> Você não tem permissão para visualizar backups.</div>';
    exit;
}

// ─── Mapa de backups da semana ────────────────────────────────────────────────
$diasSemana = [
    1 => ['slug' => 'segunda',  'label' => 'Segunda-feira'],
    2 => ['slug' => 'terca',    'label' => 'Terça-feira'],
    3 => ['slug' => 'quarta',   'label' => 'Quarta-feira'],
    4 => ['slug' => 'quinta',   'label' => 'Quinta-feira'],
    5 => ['slug' => 'sexta',    'label' => 'Sexta-feira'],
    6 => ['slug' => 'sabado',   'label' => 'Sábado'],
    7 => ['slug' => 'domingo',  'label' => 'Domingo'],
];

$backupDir = __DIR__ . DIRECTORY_SEPARATOR . 'backups';
$diaAtualN = (int) date('N');

$backups = [];
foreach ($diasSemana as $num => $dia) {
    $arquivo = $backupDir . DIRECTORY_SEPARATOR . 'backup_' . $dia['slug'] . '.sql';
    $existe   = file_exists($arquivo);
    $backups[$num] = [
        'slug'     => $dia['slug'],
        'label'    => $dia['label'],
        'arquivo'  => 'backup_' . $dia['slug'] . '.sql',
        'existe'   => $existe,
        'tamanho'  => $existe ? filesize($arquivo) : 0,
        'data_mod' => $existe ? filemtime($arquivo) : null,
        'eh_hoje'  => ($num === $diaAtualN),
    ];
}

// ─── Último backup realizado ──────────────────────────────────────────────────
$ultimoBackup = null;
foreach ($backups as $b) {
    if ($b['existe'] && (!$ultimoBackup || $b['data_mod'] > $ultimoBackup['data_mod'])) {
        $ultimoBackup = $b;
    }
}

// ─── Log (últimas 10 linhas) ──────────────────────────────────────────────────
$logFile   = $backupDir . DIRECTORY_SEPARATOR . 'backup.log';
$logLinhas = [];
if (file_exists($logFile)) {
    $all       = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logLinhas = array_slice(array_reverse($all), 0, 10);
}

function formatarTamanhoPage(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
    return $bytes . ' B';
}
?>

<style>
.backup-card        { border-radius: 16px; border: none; box-shadow: 0 2px 16px rgba(0,0,0,.08); }
.backup-status-ok   { background: linear-gradient(135deg,#1a7f4f,#28a745); color:#fff; border-radius:12px; padding:18px 24px; }
.backup-status-none { background: linear-gradient(135deg,#6c757d,#495057); color:#fff; border-radius:12px; padding:18px 24px; }
.day-row            { border-radius:10px; transition: background .15s; }
.day-row:hover      { background:#f8f9fa; }
.day-row.today-row  { background: rgba(40,167,69,.07); border-left: 3px solid #28a745; }
.badge-ok           { background:#d4edda; color:#155724; font-weight:600; }
.badge-missing      { background:#f8d7da; color:#721c24; font-weight:600; }
.badge-today        { background:#cce5ff; color:#004085; font-weight:600; }
.log-box            { background:#1a1a2e; color:#e2e2e2; border-radius:10px; font-family:monospace; font-size:12px; max-height:200px; overflow-y:auto; padding:14px; }
.log-line-ok        { color:#56ca00; }
.log-line-rest      { color:#fd7e14; }
.log-line-err       { color:#ff4c51; }
.btn-backup-now     { background: linear-gradient(135deg,#667eea,#764ba2); border:none; color:#fff; border-radius:10px; padding:10px 28px; font-weight:600; letter-spacing:.4px; transition: transform .15s, box-shadow .15s; }
.btn-backup-now:hover{ transform:translateY(-1px); box-shadow:0 6px 20px rgba(102,126,234,.4); color:#fff; }
.btn-backup-now:disabled{ opacity:.65; transform:none; }
.spinner-btn        { display:none; width:16px; height:16px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; vertical-align:middle; }
@keyframes spin     { to { transform:rotate(360deg); } }
.info-card-row      { background:#f0f4ff; border-radius:12px; padding:16px 20px; border-left:4px solid #667eea; }
.step-badge         { display:inline-flex; align-items:center; justify-content:center; background:#667eea; color:#fff; border-radius:50%; width:24px; height:24px; font-size:12px; font-weight:700; flex-shrink:0; }
.restore-warning    { background:#fff3cd; border:1px solid #ffc107; border-radius:10px; padding:14px 16px; }
</style>

<div class="col-md-12">

    <!-- Cabeçalho -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;">
                <i class="fas fa-database me-2" style="color:#667eea;"></i>Gerenciamento de Backup
            </h4>
            <p class="text-muted small mb-0">Backup automático diário — rotação de 7 dias (1 por dia da semana)</p>
        </div>
        <?php if (temPermissao('gerar_backup')): ?>
        <button id="btnGerarAgora" class="btn btn-backup-now" onclick="gerarBackupAgora()">
            <span id="btnTexto"><i class="fas fa-play-circle me-2"></i>Gerar Backup Agora</span>
            <span id="btnSpinner" class="spinner-btn"></span>
        </button>
        <?php endif; ?>
    </div>

    <!-- Cards de status -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="backup-card card h-100 p-3">
                <?php if ($ultimoBackup): ?>
                <div class="backup-status-ok h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-check-circle fa-lg me-2"></i>
                            <span class="fw-bold">Último Backup</span>
                        </div>
                        <div class="fs-5 fw-bold"><?= date('d/m/Y H:i', $ultimoBackup['data_mod']) ?></div>
                        <div class="opacity-75 small"><?= $ultimoBackup['label'] ?> &mdash; <?= formatarTamanhoPage($ultimoBackup['tamanho']) ?></div>
                    </div>
                    <div class="mt-2 opacity-75 small"><i class="fas fa-file-code me-1"></i><?= $ultimoBackup['arquivo'] ?></div>
                </div>
                <?php else: ?>
                <div class="backup-status-none h-100 d-flex flex-column justify-content-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2 opacity-75"></i>
                    <div class="fw-bold">Nenhum backup encontrado</div>
                    <div class="small opacity-75 mt-1">Clique em "Gerar Backup Agora" para criar o primeiro</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="backup-card card h-100 p-3">
                <div class="p-2">
                    <p class="text-muted small mb-2 fw-semibold text-uppercase" style="letter-spacing:.6px;">Resumo da Semana</p>
                    <?php
                    $disponiveis  = count(array_filter($backups, fn($b) => $b['existe']));
                    $totalTamanho = array_sum(array_column($backups, 'tamanho'));
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-hdd me-2" style="color:#28a745; width:18px;"></i>
                        <span class="me-auto">Backups disponíveis</span>
                        <strong><?= $disponiveis ?>/7</strong>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-weight-hanging me-2" style="color:#667eea; width:18px;"></i>
                        <span class="me-auto">Espaço total usado</span>
                        <strong><?= formatarTamanhoPage($totalTamanho) ?></strong>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-day me-2" style="color:#fd7e14; width:18px;"></i>
                        <span class="me-auto">Dia de hoje</span>
                        <strong><?= $diasSemana[$diaAtualN]['label'] ?></strong>
                    </div>
                    <div class="progress mt-3" style="height:6px; border-radius:3px;">
                        <div class="progress-bar bg-success" style="width:<?= ($disponiveis/7)*100 ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="backup-card card h-100 p-3">
                <div class="p-2">
                    <p class="text-muted small mb-2 fw-semibold text-uppercase" style="letter-spacing:.6px;">Política de Retenção</p>
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-sync-alt me-2 mt-1" style="color:#667eea; width:18px;"></i>
                        <span class="small">Um arquivo por dia da semana (7 slots)</span>
                    </div>
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-redo-alt me-2 mt-1" style="color:#fd7e14; width:18px;"></i>
                        <span class="small">O backup do mesmo dia sobrescreve o anterior</span>
                    </div>
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-clock me-2 mt-1" style="color:#28a745; width:18px;"></i>
                        <span class="small">Agendamento recomendado: diário às 02:00</span>
                    </div>
                    <div class="d-flex align-items-start">
                        <i class="fas fa-lock me-2 mt-1" style="color:#dc3545; width:18px;"></i>
                        <span class="small">Acesso direto via browser bloqueado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta de resultado das ações -->
    <div id="alertaBackup" class="alert mb-3" style="display:none; border-radius:10px;"></div>

    <!-- Tabela dos 7 backups -->
    <div class="backup-card card mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
            <h5 class="fw-bold mb-0" style="color:#1a1a2e;">
                <i class="fas fa-calendar-week me-2" style="color:#667eea;"></i>Backups por Dia da Semana
            </h5>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-muted small" style="font-size:11px; text-transform:uppercase; letter-spacing:.6px;">
                            <th class="border-0 pb-3">Dia</th>
                            <th class="border-0 pb-3">Arquivo</th>
                            <th class="border-0 pb-3">Tamanho</th>
                            <th class="border-0 pb-3">Gerado em</th>
                            <th class="border-0 pb-3">Status</th>
                            <th class="border-0 pb-3 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $num => $b): ?>
                        <tr class="day-row <?= $b['eh_hoje'] ? 'today-row' : '' ?>" id="row-<?= $b['slug'] ?>">
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($b['eh_hoje']): ?>
                                        <span class="badge badge-today rounded-pill px-2">HOJE</span>
                                    <?php endif; ?>
                                    <strong><?= $b['label'] ?></strong>
                                </div>
                            </td>
                            <td class="text-muted small py-3" style="font-family:monospace;"><?= $b['arquivo'] ?></td>
                            <td class="py-3">
                                <span id="tam-<?= $b['slug'] ?>">
                                    <?= $b['existe'] ? formatarTamanhoPage($b['tamanho']) : '—' ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <span id="dt-<?= $b['slug'] ?>">
                                    <?= $b['existe'] ? date('d/m/Y H:i', $b['data_mod']) : '—' ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <span id="st-<?= $b['slug'] ?>" class="badge rounded-pill px-3 py-1 <?= $b['existe'] ? 'badge-ok' : 'badge-missing' ?>">
                                    <?= $b['existe'] ? '✓ Disponível' : '✕ Não gerado' ?>
                                </span>
                            </td>
                            <td class="py-3 text-end">
                                <?php if ($b['existe']): ?>
                                <div class="d-flex gap-2 justify-content-end" id="acoes-<?= $b['slug'] ?>">
                                    <?php if (temPermissao('baixar_backup')): ?>
                                    <a href="downloadBackup.php?arquivo=<?= urlencode($b['arquivo']) ?>"
                                       class="btn btn-sm btn-outline-primary rounded-pill"
                                       title="Baixar <?= $b['arquivo'] ?>">
                                        <i class="fas fa-download me-1"></i>Baixar
                                    </a>
                                    <?php endif; ?>
                                    <?php if (temPermissao('restaurar_backup')): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning rounded-pill"
                                            title="Restaurar banco a partir de <?= $b['arquivo'] ?>"
                                            onclick="confirmarRestauracao('<?= $b['arquivo'] ?>', '<?= $b['label'] ?>', '<?= date('d/m/Y H:i', $b['data_mod']) ?>')">
                                        <i class="fas fa-undo-alt me-1"></i>Restaurar
                                    </button>
                                    <?php endif; ?>
                                    <?php if (!temPermissao('baixar_backup') && !temPermissao('restaurar_backup')): ?>
                                    <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span id="acoes-<?= $b['slug'] ?>" class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Log -->
    <?php if (!empty($logLinhas)): ?>
    <div class="backup-card card mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
            <h5 class="fw-bold mb-0" style="color:#1a1a2e;">
                <i class="fas fa-scroll me-2" style="color:#667eea;"></i>Log de Atividades
                <span class="text-muted fw-normal fs-6 ms-2">(últimas 10 execuções)</span>
            </h5>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            <div class="log-box">
                <?php foreach ($logLinhas as $linha): ?>
                <div class="<?= strpos($linha,'[OK]')!==false ? 'log-line-ok' : (strpos($linha,'RESTAURACAO_OK')!==false ? 'log-line-rest' : 'log-line-err') ?>">
                    <?= htmlspecialchars($linha) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Instrução de agendamento -->
    <div class="backup-card card mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
            <h5 class="fw-bold mb-0" style="color:#1a1a2e;">
                <i class="fas fa-robot me-2" style="color:#667eea;"></i>Configurar Backup Automático
            </h5>
        </div>
        <div class="card-body px-4 pb-4">
            <p class="text-muted small mb-3">Selecione o ambiente de execução:</p>
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-linux" type="button">
                        <i class="fab fa-linux me-1"></i>Linux / Debian <span class="badge bg-success ms-1" style="font-size:10px;">Produção</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-windows" type="button">
                        <i class="fab fa-windows me-1"></i>Windows / WAMP <span class="badge bg-secondary ms-1" style="font-size:10px;">Homologação</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-linux" role="tabpanel">
                    <div class="info-card-row mb-3">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="step-badge">1</span>
                            <div><strong>Instalar o cliente PostgreSQL</strong><br>
                            <span class="text-muted small">Necessário para ter <code>pg_dump</code> e <code>psql</code>:</span>
                            <div class="bg-dark text-white rounded p-2 mt-1 small" style="font-family:monospace;">sudo apt update &amp;&amp; sudo apt install -y postgresql-client</div></div>
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="step-badge">2</span>
                            <div><strong>Permissão na pasta backups/</strong><br>
                            <span class="text-muted small">Usuário do PHP (<code>www-data</code>) precisa de escrita:</span>
                            <div class="bg-dark text-white rounded p-2 mt-1 small" style="font-family:monospace;">sudo chown www-data:www-data /var/www/atendeai/backups<br>sudo chmod 750 /var/www/atendeai/backups</div></div>
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="step-badge">3</span>
                            <div><strong>Adicionar ao crontab</strong><br>
                            <span class="text-muted small">Execute <code>sudo crontab -e</code> e adicione (diário às 02:00):</span>
                            <div class="bg-dark text-white rounded p-2 mt-1 small" style="font-family:monospace;">0 2 * * * /usr/bin/php /var/www/atendeai/cron_backup.php</div></div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <span class="step-badge">4</span>
                            <div><strong>Testar manualmente</strong><br>
                            <div class="bg-dark text-white rounded p-2 mt-1 small" style="font-family:monospace;">sudo -u www-data php /var/www/atendeai/cron_backup.php</div></div>
                        </div>
                    </div>
                    <div class="alert alert-info small py-2 mb-0" style="border-radius:8px;">
                        <i class="fas fa-info-circle me-1"></i> Ajuste <code>/var/www/atendeai/</code> conforme o caminho real no seu servidor.
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-windows" role="tabpanel">
                    <div class="info-card-row">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="step-badge">1</span>
                            <div><strong>Abrir o Agendador de Tarefas</strong><br>
                            <span class="text-muted small">Pressione <kbd>Win+R</kbd>, digite <code>taskschd.msc</code> e Enter.</span></div>
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="step-badge">2</span>
                            <div><strong>Criar Tarefa Básica</strong><br>
                            <span class="text-muted small">Nome: <em>Backup AtendeAI</em> — Disparar: Diariamente às 02:00</span></div>
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="step-badge">3</span>
                            <div><strong>Configurar a Ação</strong><br>
                            <span class="text-muted small">Programa: <code>C:\wamp64\bin\php\php8.x.x\php.exe</code><br>Argumento: <code>C:\wamp64\www\atendeai\cron_backup.php</code></span></div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <span class="step-badge">4</span>
                            <div><strong>Verificar</strong><br>
                            <span class="text-muted small">Execute a tarefa manualmente e confirme na tabela acima.</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de restauração -->
<div class="modal fade" id="modalRestaurar" tabindex="-1" aria-labelledby="modalRestaurarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="modalRestaurarLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Restauração
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="restore-warning mb-3">
                    <strong><i class="fas fa-exclamation-circle me-1 text-warning"></i>Atenção:</strong>
                    Esta ação irá <strong>sobrescrever todos os dados atuais</strong> do banco com os dados do backup selecionado. Esta operação <strong>não pode ser desfeita</strong>.
                </div>
                <p class="mb-1"><strong>Backup selecionado:</strong></p>
                <p class="text-muted mb-1" id="modalArquivoInfo" style="font-family:monospace;"></p>
                <p class="text-muted small" id="modalDataInfo"></p>
                <p class="mt-3 mb-0">Tem certeza que deseja continuar?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill" id="btnConfirmarRestaurar" onclick="executarRestauracao()">
                    <span id="txtRestaurar"><i class="fas fa-undo-alt me-1"></i>Sim, Restaurar</span>
                    <span id="spinRestaurar" class="spinner-btn"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let arquivoParaRestaurar = '';

function confirmarRestauracao(arquivo, label, dataHora) {
    arquivoParaRestaurar = arquivo;
    document.getElementById('modalArquivoInfo').textContent = arquivo;
    document.getElementById('modalDataInfo').textContent   = label + ' — gerado em ' + dataHora;
    new bootstrap.Modal(document.getElementById('modalRestaurar')).show();
}

function executarRestauracao() {
    if (!arquivoParaRestaurar) return;

    const btn     = document.getElementById('btnConfirmarRestaurar');
    const txt     = document.getElementById('txtRestaurar');
    const spin    = document.getElementById('spinRestaurar');
    const alerta  = document.getElementById('alertaBackup');

    btn.disabled = true;
    spin.style.display = 'inline-block';
    txt.innerHTML = 'Restaurando...';

    const formData = new FormData();
    formData.append('arquivo', arquivoParaRestaurar);

    fetch('restaurarBackup.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            // Fecha o modal
            bootstrap.Modal.getInstance(document.getElementById('modalRestaurar')).hide();
            alerta.style.display = 'block';
            if (data.sucesso) {
                alerta.className = 'alert alert-success mb-3';
                alerta.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>Restauração concluída!</strong> ' + data.mensagem;
            } else {
                alerta.className = 'alert alert-danger mb-3';
                alerta.innerHTML = '<i class="fas fa-times-circle me-2"></i><strong>Falha na restauração:</strong> ' + data.mensagem;
            }
            window.scrollTo({top: 0, behavior: 'smooth'});
        })
        .catch(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalRestaurar')).hide();
            alerta.style.display = 'block';
            alerta.className = 'alert alert-danger mb-3';
            alerta.innerHTML = '<i class="fas fa-times-circle me-2"></i>Erro de comunicação com o servidor.';
        })
        .finally(() => {
            btn.disabled = false;
            spin.style.display = 'none';
            txt.innerHTML = '<i class="fas fa-undo-alt me-1"></i>Sim, Restaurar';
        });
}

function gerarBackupAgora() {
    const btn       = document.getElementById('btnGerarAgora');
    const btnTexto  = document.getElementById('btnTexto');
    const btnSpin   = document.getElementById('btnSpinner');
    const alerta    = document.getElementById('alertaBackup');

    btn.disabled = true;
    btnSpin.style.display = 'inline-block';
    btnTexto.innerHTML = '<i class="fas fa-hourglass-half me-2"></i>Gerando backup...';
    alerta.style.display = 'none';

    fetch('gerarBackup.php')
        .then(r => r.json())
        .then(data => {
            alerta.style.display = 'block';
            if (data.sucesso) {
                alerta.className = 'alert alert-success mb-3';
                alerta.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>Sucesso!</strong> ' + data.mensagem;
                atualizarLinhaDia(data.arquivo, data.tamanho, data.data_hora);
            } else {
                alerta.className = 'alert alert-danger mb-3';
                alerta.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i><strong>Erro:</strong> ' + data.mensagem;
            }
        })
        .catch(() => {
            alerta.style.display = 'block';
            alerta.className = 'alert alert-danger mb-3';
            alerta.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Erro de comunicação com o servidor.';
        })
        .finally(() => {
            btn.disabled = false;
            btnSpin.style.display = 'none';
            btnTexto.innerHTML = '<i class="fas fa-play-circle me-2"></i>Gerar Backup Agora';
        });
}

function atualizarLinhaDia(arquivo, tamanho, dataHora) {
    const match = arquivo.match(/^backup_(.+)\.sql$/);
    if (!match) return;
    const slug = match[1];

    const el = (id) => document.getElementById(id);

    if (el('tam-' + slug)) el('tam-' + slug).textContent = formatarTamanho(tamanho);
    if (el('dt-'  + slug)) el('dt-'  + slug).textContent = dataHora;

    const elSt = el('st-' + slug);
    if (elSt) { elSt.className = 'badge rounded-pill px-3 py-1 badge-ok'; elSt.textContent = '✓ Disponível'; }

    const elAcoes = el('acoes-' + slug);
    if (elAcoes) {
        let acoesHtml = `<div class="d-flex gap-2 justify-content-end" id="acoes-${slug}">`;
        <?php if (temPermissao('baixar_backup')): ?>
        acoesHtml += `<a href="downloadBackup.php?arquivo=${encodeURIComponent(arquivo)}"
               class="btn btn-sm btn-outline-primary rounded-pill">
               <i class="fas fa-download me-1"></i>Baixar
            </a>`;
        <?php endif; ?>
        <?php if (temPermissao('restaurar_backup')): ?>
        acoesHtml += `<button type="button" class="btn btn-sm btn-outline-warning rounded-pill"
                onclick="confirmarRestauracao('${arquivo}', '${slug}', '${dataHora}')">
                <i class="fas fa-undo-alt me-1"></i>Restaurar
            </button>`;
        <?php endif; ?>
        <?php if (!temPermissao('baixar_backup') && !temPermissao('restaurar_backup')): ?>
        acoesHtml += `<span class="text-muted small">—</span>`;
        <?php endif; ?>
        acoesHtml += `</div>`;
        elAcoes.outerHTML = acoesHtml;
    }
}

function formatarTamanho(bytes) {
    if (bytes >= 1073741824) return (bytes/1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576)    return (bytes/1048576).toFixed(2)    + ' MB';
    if (bytes >= 1024)       return (bytes/1024).toFixed(2)       + ' KB';
    return bytes + ' B';
}
</script>
