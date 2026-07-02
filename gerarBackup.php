<?php
/**
 * gerarBackup.php
 * Gera um backup do banco PostgreSQL via pg_dump.
 * Credenciais lidas das constantes definidas em config.php.
 * Rotação de 7 dias: nomeado pelo dia da semana, sobrescreve a cada ciclo.
 * Chamado via AJAX (interface) ou via CLI (cron_backup.php).
 *
 * Requer permissão: gerenciar_backup
 */

// ─── Captura QUALQUER saída anterior (warnings, erros, redirects) ─────────────
// Garante que o script SEMPRE retorne JSON válido ao fetch do frontend.
ob_start();

// Suprime warnings do PHP para não quebrar o JSON
error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$isCli = (php_sapi_name() === 'cli');

// Limpa qualquer saída gerada pelos includes antes de definir o Content-Type
if (!$isCli) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
}

// ─── Autenticação ─────────────────────────────────────────────────────────────
if (!$isCli) {
    verificarSessao();
    if (!temPermissao('gerenciar_backup')) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado.']);
        ob_end_flush();
        exit;
    }
}

// ─── Garante que a pasta backups/ existe ─────────────────────────────────────
$backupDir = __DIR__ . DIRECTORY_SEPARATOR . 'backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0750, true);
}

// ─── Verifica se exec() está disponível ──────────────────────────────────────
if (!function_exists('exec') || in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
    $msg = 'A função exec() está desabilitada no PHP. Habilite-a no php.ini (disable_functions) para usar o backup.';
    $isCli ? print("[ERRO] $msg\n") : print(json_encode(['sucesso' => false, 'mensagem' => $msg]));
    ob_end_flush();
    exit(1);
}

// ─── Localização do pg_dump (Linux/Debian e Windows) ─────────────────────────
$pgDumpCandidates = [
    '/usr/bin/pg_dump',
    '/usr/lib/postgresql/17/bin/pg_dump',
    '/usr/lib/postgresql/16/bin/pg_dump',
    '/usr/lib/postgresql/15/bin/pg_dump',
    '/usr/lib/postgresql/14/bin/pg_dump',
    'pg_dump',
    'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe',
    'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
    'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
    'C:\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe',
];

$pgDump = null;
foreach ($pgDumpCandidates as $candidate) {
    // file_exists para caminhos absolutos (mais rápido e confiável)
    if (strpos($candidate, '/') === 0 || strpos($candidate, 'C:\\') === 0) {
        if (file_exists($candidate)) {
            $pgDump = $candidate;
            break;
        }
    } else {
        // Comando genérico: testa via shell
        $test = @shell_exec(escapeshellcmd($candidate) . ' --version 2>&1');
        if ($test && stripos($test, 'pg_dump') !== false) {
            $pgDump = $candidate;
            break;
        }
    }
}

if (!$pgDump) {
    $msg = 'pg_dump não encontrado. No Debian execute: sudo apt install postgresql-client';
    $isCli ? print("[ERRO] $msg\n") : print(json_encode(['sucesso' => false, 'mensagem' => $msg]));
    ob_end_flush();
    exit(1);
}

// ─── Nome do arquivo (rotação semanal) ───────────────────────────────────────
$diasSemana = [1=>'segunda',2=>'terca',3=>'quarta',4=>'quinta',5=>'sexta',6=>'sabado',7=>'domingo'];
$diaN       = (int) date('N');
$nomeDia    = $diasSemana[$diaN];
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

putenv('PGPASSWORD');

// ─── Log ─────────────────────────────────────────────────────────────────────
$logFile  = $backupDir . DIRECTORY_SEPARATOR . 'backup.log';
$dataHora = date('d/m/Y H:i:s');
$status   = ($returnCode === 0) ? 'OK' : 'ERRO';
$logLine  = "[{$dataHora}] [{$status}] backup_{$nomeDia}.sql | código: {$returnCode} | pg_dump: {$pgDump}";
if (!empty($output)) $logLine .= ' | ' . implode('; ', array_filter($output));
file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);

// ─── Resposta ─────────────────────────────────────────────────────────────────
ob_clean(); // Limpa qualquer saída residual antes do JSON final

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
} else {
    $detalhes = implode('; ', array_filter($output));
    $msg = "Falha ao gerar backup (código: {$returnCode}). " . ($detalhes ?: 'Verifique o log no servidor.');
    if ($isCli) {
        echo "[ERRO] $msg\n";
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => $msg]);
    }
}

ob_end_flush();

function formatarTamanhoBackup(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
    return $bytes . ' B';
}
