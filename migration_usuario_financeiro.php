<?php
require_once 'config.php';

try {
    // Adicionar coluna usuario_id na tabela financeiro
    $pdo->exec("ALTER TABLE financeiro ADD COLUMN IF NOT EXISTS usuario_id int4 REFERENCES usuarios(id)");
    echo "Coluna usuario_id adicionada com sucesso!\n";

    // Verificar
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='financeiro' AND column_name='usuario_id'");
    $col = $stmt->fetch();
    echo $col ? "Verificado: coluna existe.\n" : "ERRO: coluna nao encontrada!\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
