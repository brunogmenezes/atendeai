<?php
/**
 * restaurarBackup.php
 * Restaura o banco de dados a partir de um arquivo de backup .sql via psql.
 * Apenas backups no padrão backup_<dia>.sql são aceitos (anti path-traversal).
 *
 * Requer permissão: gerenciar_backup
 * Método: POST
 * Parâmetro: arquivo=backup_segunda.sql
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

verificarSessao();

if (!temPermissao('gerenciar_backup')) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$arquivo = $_POST['arquivo'] ?? '';

// Valida padrão do nome do arquivo
if (!preg_match('/^backup_(segunda|terca|quarta|quinta|sexta|sabado|domingo)\.sql$/', $arquivo)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Arquivo inválido.']);
    exit;
}

$backupDir    = __DIR__ . DIRECTORY_SEPARATOR . 'backups';
$backupFile   = realpath($backupDir . DIRECTORY_SEPARATOR . $arquivo);
$backupDirReal = realpath($backupDir);

// Anti path-traversal
if ($backupFile === false || strpos($backupFile, $backupDirReal) !== 0) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Arquivo não encontrado.']);
    exit;
}

if (!file_exists($backupFile)) {
    echo json_encode(['sucesso' => false, 'mensagem' => "O arquivo '{$arquivo}' não existe."]); 
    exit;
}

// ─── Credenciais (mesmas do config.php) ──────────────────────────────────────
if (!defined('DB_HOST')) {
    define('DB_HOST',     '45.224.128.87');
    define('DB_PORT',     '5432');
    define('DB_NAME',     'atendeaicopia');
    define('DB_USER',     'postgres');
    define('DB_PASSWORD', '91lS!&*Ke');
}

// ─── Localização do psql (Windows e Linux/Debian) ──────────────────────────
$psqlCandidates = [
    // Linux/Debian — via PATH (requer: apt install postgresql-client)
    '/usr/bin/psql',
    '/usr/lib/postgresql/17/bin/psql',
    '/usr/lib/postgresql/16/bin/psql',
    '/usr/lib/postgresql/15/bin/psql',
    '/usr/lib/postgresql/14/bin/psql',
    // Genérico via PATH
    'psql',
    // Windows — caminhos absolutos
    'C:\\Program Files\\PostgreSQL\\17\\bin\\psql.exe',
    'C:\\Program Files\\PostgreSQL\\16\\bin\\psql.exe',
    'C:\\Program Files\\PostgreSQL\\15\\bin\\psql.exe',
    'C:\\Program Files\\PostgreSQL\\14\\bin\\psql.exe',
];

$psql = null;
foreach ($psqlCandidates as $candidate) {
    $test = @shell_exec(escapeshellcmd($candidate) . ' --version 2>&1');
    if ($test && stripos($test, 'psql') !== false) {
        $psql = $candidate;
        break;
    }
}

if (!$psql) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'psql não encontrado. Instale o cliente PostgreSQL no servidor.']);
    exit(1);
}

// ─── Executa a restauração ────────────────────────────────────────────────────
putenv('PGPASSWORD=' . DB_PASSWORD);

$cmd = sprintf(
    '%s --host=%s --port=%s --username=%s --dbname=%s --file=%s 2>&1',
    escapeshellcmd($psql),
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
$status   = ($returnCode === 0) ? 'RESTAURACAO_OK' : 'RESTAURACAO_ERRO';
$logLine  = "[{$dataHora}] [{$status}] {$arquivo} | código: {$returnCode}";
if (!empty($output)) $logLine .= ' | ' . implode('; ', array_filter($output));
file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);

// ─── Resposta ─────────────────────────────────────────────────────────────────
if ($returnCode === 0) {
    echo json_encode([
        'sucesso'  => true,
        'mensagem' => "Banco restaurado com sucesso a partir de '{$arquivo}'! Data: {$dataHora}",
    ]);
} else {
    $detalhes = implode('; ', array_filter($output));
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => "Falha na restauração (código: {$returnCode}). " . ($detalhes ?: 'Verifique o log.'),
    ]);
}
