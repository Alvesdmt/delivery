<?php
require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // Criar tabela pedido_produtos
    $sql = "CREATE TABLE IF NOT EXISTS pedido_produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        produto_id INT NOT NULL,
        quantidade INT NOT NULL,
        preco DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
    )";
    
    $pdo->exec($sql);
    echo "Tabela pedido_produtos criada com sucesso!\n";
    
} catch(PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
} 