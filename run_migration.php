<?php
/**
 * run_migration.php — Executar apenas UMA VEZ para inserir a permissão de backup.
 * Acesse via browser: http://localhost/atendeai/run_migration.php
 * APAGUE este arquivo após executar.
 */
require_once 'config.php';

try {
    $sql = "INSERT INTO permissoes (nome, descricao)
            VALUES ('gerenciar_backup', 'Visualizar, gerar e baixar backups do banco de dados')
            ON CONFLICT (nome) DO NOTHING";
    $pdo->exec($sql);
    echo '<div style="font-family:Arial;padding:20px;background:#d4edda;color:#155724;border-radius:8px;max-width:500px;margin:40px auto;">';
    echo '<h3>✅ Migration executada com sucesso!</h3>';
    echo '<p>Permissão <strong>gerenciar_backup</strong> inserida na tabela <strong>permissoes</strong>.</p>';
    echo '<p><strong>⚠️ Apague este arquivo agora:</strong> <code>run_migration.php</code></p>';
    echo '</div>';
} catch (PDOException $e) {
    echo '<div style="font-family:Arial;padding:20px;background:#f8d7da;color:#721c24;border-radius:8px;max-width:500px;margin:40px auto;">';
    echo '<h3>❌ Erro na migration</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
