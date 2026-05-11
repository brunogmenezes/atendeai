<?php
include(__DIR__ . "/../config.php");

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS public.pagamentos_compra (
        id SERIAL PRIMARY KEY,
        compra_id INT NOT NULL,
        conta_id INT NOT NULL,
        valor NUMERIC(10,2) NOT NULL,
        data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_compra FOREIGN KEY (compra_id) REFERENCES public.compras(id) ON DELETE CASCADE,
        CONSTRAINT fk_conta FOREIGN KEY (conta_id) REFERENCES public.contas(id)
    );
    ";
    
    $pdo->exec($sql);
    echo "Tabela pagamentos_compra criada com sucesso!\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
}
?>
