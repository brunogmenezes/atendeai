<?php
require_once 'config.php';

$sql = file_get_contents(__DIR__ . '/migration_categorias_financeiro.sql');

try {
    $pdo->exec($sql);
    echo "Migração executada com sucesso!\n";
    
    // Verificar categorias criadas
    $stmt = $pdo->query("SELECT id, nome, tipo FROM categorias_financeiro ORDER BY tipo, id");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Categorias criadas:\n";
    foreach ($cats as $c) {
        $tipo = $c['tipo'] == 1 ? 'Entrada' : 'Saída';
        echo "  [{$tipo}] {$c['nome']}\n";
    }
    
    // Verificar coluna categoria_id
    $stmt2 = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='financeiro' AND column_name='categoria_id'");
    $col = $stmt2->fetch();
    if ($col) {
        echo "\nColuna categoria_id adicionada à tabela financeiro com sucesso!\n";
    } else {
        echo "\nERRO: Coluna categoria_id não encontrada!\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
