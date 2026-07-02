<?php
/**
 * cron_backup.php
 * Script executado pelo agendador de tarefas. NÃO requer sessão — apenas CLI.
 *
 * ─── Linux / Debian (produção) ───────────────────────────────────────────────
 * Instalar cliente PostgreSQL (se não tiver):
 *   sudo apt install postgresql-client
 *
 * Adicionar ao crontab (sudo crontab -e ou crontab -e do usuário www-data):
 *   0 2 * * * /usr/bin/php /var/www/atendeai/cron_backup.php >> /var/www/atendeai/backups/backup.log 2>&1
 *
 * Verificar o PHP disponível:
 *   which php   ou   php -v
 *
 * ─── Windows / WAMP (homologação) ────────────────────────────────────────────
 * Agendador de Tarefas:
 *   Programa : C:\wamp64\bin\php\phpX.X.X\php.exe
 *   Argumentos: C:\wamp64\www\atendeai\cron_backup.php
 *   Disparar : Diariamente às 02:00
 */

// Garante execução apenas via CLI (segurança)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Este script só pode ser executado via linha de comando.');
}

define('CRON_CALL', true);

// Inclui as configurações sem iniciar sessão
require_once __DIR__ . '/config.php';

echo "[" . date('d/m/Y H:i:s') . "] Iniciando backup automático...\n";

// Delega para o script principal de backup
require __DIR__ . '/gerarBackup.php';
