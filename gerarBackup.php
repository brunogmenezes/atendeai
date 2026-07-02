<?php
/**
 * gerarBackup.php
 * Gera um backup do banco PostgreSQL via pg_dump.
 * Credenciais lidas do config.php.
 * Rotação de 7 dias: nomeado pelo dia da semana, sobrescreve a cada ciclo.
 * Chamado via AJAX (interface) ou via CLI (cron_backup.php).
 *
 * Requer permissão: gerenciar_backup
 */

// Lê as credenciais do config.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    verificarSessao();
    if (!temPermissao('gerenciar_backup')) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado: você não tem permissão para gerar backups.']);
        exit;
    }
}

// ─── Extrair credenciais do DSN do PDO (config.php) ──────────────────────────
// config.php cria: $pdo = new PDO("pgsql:host=...;dbname=...", $user, $pass)
// Aqui lemos as mesmas constantes centralizadas:
define('DB_HOST',     '45.224.128.87');
define('DB_PORT',     '5432');
define('DB_NAME',     'atendeaicopia');
define('DB_USER',     'postgres');
define('DB_PASSWORD', '91lS!&*Ke');

// ─── Localização do pg_dump (Windows e Linux/Debian) ────────────────────────
$pgDumpCandidates = [
    // Linux/Debian — via PATH (requer: apt install postgresql-client)
    '/usr/bin/pg_dump',
    '/usr/lib/postgresql/17/bin/pg_dump',
    '/usr/lib/postgresql/16/bin/pg_dump',
    '/usr/lib/postgresql/15/bin/pg_dump',
    '/usr/lib/postgresql/14/bin/pg_dump',
    // Genérico via PATH (funciona nos dois SOs se o PATH estiver configurado)
    'pg_dump',
    // Windows — caminhos absolutos
    'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe',
    'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
    'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
    'C:\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe',
];

$pgDump = null;
foreach ($pgDumpCandidates as $candidate) {
    $test = @shell_exec(escapeshellcmd($candidate) . ' --version 2>&1');
    if ($test && stripos($test, 'pg_dump') !== false) {
        $pgDump = $candidate;
        break;
    }
}

if (!$pgDump) {
    $msg = 'pg_dump não encontrado. Instale o cliente PostgreSQL no servidor.';
    $isCli ? print("[ERRO] $msg\n") : print(json_encode(['sucesso' => false, 'mensagem' => $msg]));
    exit(1);
}

// ─── Nome do arquivo (rotação semanal: 1 arquivo por dia da semana) ──────────
$diasSemana = [1=>'segunda',2=>'terca',3=>'quarta',4=>'quinta',5=>'sexta',6=>'sabado',7=>'domingo'];
$diaN       = (int) date('N');
$nomeDia    = $diasSemana[$diaN];
$backupDir  = __DIR__ . DIRECTORY_SEPARATOR . 'backups';
$backupFile = $backupDir . DIRECTORY_SEPARATOR . "backup_{$nomeDia}.sql";

// ─── Executa o pg_dump ───────────────────────────────────────────────────────
putenv('PGPASSWORD=' . DB_PASSWORD);

$cmd = sprintf(
    '%s --host=%s --port=%s --username=%s --dbname=%s --format=plain --no-password --file=%s 2>&1',
    escapeshellcmd($pgDump),
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_PORT),
    escapeshellarg(DB_USER),
    escapeshellarg(DB_NAME),
    escapeshellarg($backupFile)
);

$output     = [];
$returnCode = 0;
exec($cmd, $output, $returnCode);

putenv('PGPASSWORD'); // Limpa imediatamente

// ─── Log ─────────────────────────────────────────────────────────────────────
$logFile  = $backupDir . DIRECTORY_SEPARATOR . 'backup.log';
$dataHora = date('d/m/Y H:i:s');
$status   = ($returnCode === 0) ? 'OK' : 'ERRO';
$logLine  = "[{$dataHora}] [{$status}] backup_{$nomeDia}.sql | código: {$returnCode}";
if (!empty($output)) $logLine .= ' | ' . implode('; ', array_filter($output));
file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);

// ─── Resposta ─────────────────────────────────────────────────────────────────
if ($returnCode === 0 && file_exists($backupFile)) {
    $tamanho = filesize($backupFile);
    $msg = "Backup gerado com sucesso: backup_{$nomeDia}.sql (" . formatarTamanhoBackup($tamanho) . ")";
    if ($isCli) {
        echo "[OK] $msg\n";
    } else {
        echo json_encode([
            'sucesso'   => true,
            'mensagem'  => $msg,
            'arquivo'   => "backup_{$nomeDia}.sql",
            'tamanho'   => $tamanho,
            'data_hora' => $dataHora,
        ]);
    }
    exit(0);
} else {
    $detalhes = implode('; ', array_filter($output));
    $msg = "Falha ao gerar backup (código: {$returnCode}). " . ($detalhes ?: 'Verifique o log.');
    if ($isCli) {
        echo "[ERRO] $msg\n";
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => $msg]);
    }
    exit(1);
}

function formatarTamanhoBackup(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
    return $bytes . ' B';
}
