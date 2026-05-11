<?php
include("config.php");

$sql = "
CREATE TABLE IF NOT EXISTS compras (
    id SERIAL PRIMARY KEY,
    fornecedor VARCHAR(255),
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    usuario_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS itens_compra (
    id SERIAL PRIMARY KEY,
    compra_id INTEGER REFERENCES compras(id) ON DELETE CASCADE,
    produto_id INTEGER REFERENCES produtos(id),
    quantidade INTEGER NOT NULL,
    preco_custo DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL
);
";

try {
    $pdo->exec($sql);
    echo "Tables created successfully or already exist.";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
