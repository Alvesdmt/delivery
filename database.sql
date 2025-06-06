-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS delivery_db;
USE delivery_db;

-- Tabela de usuários (admin)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    endereco TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    imagem VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    destaque TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'preparando', 'entregue', 'cancelado') DEFAULT 'pendente',
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Tabela de itens do pedido
CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha) VALUES 
('Administrador', 'admin@delivery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir categorias de exemplo
INSERT INTO categorias (nome, descricao) VALUES 
('Pizzas', 'As melhores pizzas da cidade'),
('Lanches', 'Lanches deliciosos e suculentos'),
('Bebidas', 'Bebidas geladas e refrescantes'),
('Sobremesas', 'Doces e sobremesas para todos os gostos');

-- Inserir produtos de exemplo
INSERT INTO produtos (categoria_id, nome, descricao, preco, destaque) VALUES 
(1, 'Pizza Margherita', 'Molho de tomate, mussarela, manjericão', 45.90, 1),
(1, 'Pizza Calabresa', 'Molho de tomate, mussarela, calabresa, cebola', 49.90, 1),
(2, 'X-Burger', 'Pão, hambúrguer, queijo, alface, tomate', 25.90, 1),
(2, 'X-Salada', 'Pão, hambúrguer, queijo, alface, tomate, maionese', 27.90, 0),
(3, 'Coca-Cola 2L', 'Refrigerante Coca-Cola 2 litros', 12.90, 0),
(3, 'Suco Natural', 'Suco natural de laranja 500ml', 8.90, 1),
(4, 'Sorvete', 'Sorvete de creme com calda de chocolate', 15.90, 1),
(4, 'Pudim', 'Pudim de leite condensado', 12.90, 0); 