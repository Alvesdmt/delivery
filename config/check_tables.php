<?php
require_once 'database.php';

try {
    $pdo = getConnection();
    
    // Verifica e cria a tabela de clientes se não existir
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            endereco VARCHAR(255),
            numero VARCHAR(10),
            complemento VARCHAR(255),
            bairro VARCHAR(100),
            cidade VARCHAR(100),
            cep VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Verifica e cria a tabela de produtos se não existir
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            imagem VARCHAR(255),
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Verifica e cria a tabela de pedidos se não existir
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            endereco_entrega VARCHAR(255) NOT NULL,
            numero_entrega VARCHAR(10) NOT NULL,
            complemento_entrega VARCHAR(255),
            bairro_entrega VARCHAR(100) NOT NULL,
            cidade_entrega VARCHAR(100) NOT NULL,
            cep_entrega VARCHAR(10) NOT NULL,
            valor_total DECIMAL(10,2) NOT NULL,
            taxa_entrega DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pendente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        )
    ");

    // Verifica e cria a tabela de itens do pedido se não existir
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedido_itens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id INT NOT NULL,
            produto_id INT NOT NULL,
            quantidade INT NOT NULL,
            preco_unitario DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
            FOREIGN KEY (produto_id) REFERENCES produtos(id)
        )
    ");

    echo "Tabelas verificadas e criadas com sucesso!";
} catch (PDOException $e) {
    die("Erro ao verificar/criar tabelas: " . $e->getMessage());
}
?> 