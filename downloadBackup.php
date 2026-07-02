<?php
/**
 * downloadBackup.php
 * Força o download seguro de um arquivo de backup.
 * Protegido contra path traversal — só aceita arquivos no padrão esperado dentro de backups/.
 *
 * Requer permissão: gerenciar_backup
 * Parâmetro: ?arquivo=backup_segunda.sql
 */

include('config.php');
require_once 'auth.php';
verificarSessao();

if (!temPermissao('gerenciar_backup')) {
    http_response_code(403);
    die('Acesso negado.');
}

$arquivo = $_GET['arquivo'] ?? '';

// Validação: apenas nomes no padrão backup_<dia>.sql
if (!preg_match('/^backup_(segunda|terca|quarta|quinta|sexta|sabado|domingo)\.sql$/', $arquivo)) {
    http_response_code(400);
    die('Arquivo inválido.');
}

$backupDir  = __DIR__ . DIRECTORY_SEPARATOR . 'backups';
$caminhoReal = realpath($backupDir . DIRECTORY_SEPARATOR . $arquivo);
$backupDirReal = realpath($backupDir);

// Garante que o arquivo está dentro da pasta backups (anti path-traversal)
if ($caminhoReal === false || strpos($caminhoReal, $backupDirReal) !== 0) {
    http_response_code(404);
    die('Arquivo não encontrado.');
}

if (!file_exists($caminhoReal)) {
    http_response_code(404);
    die('Arquivo de backup não existe.');
}

// Força download
$tamanho = filesize($caminhoReal);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($caminhoReal) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $tamanho);
ob_end_clean();
readfile($caminhoReal);
exit;
