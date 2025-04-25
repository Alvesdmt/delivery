-- Tabela de Configurações
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_estabelecimento VARCHAR(255) NOT NULL,
    email_contato VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    endereco TEXT NOT NULL,
    taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    raio_entrega INT NOT NULL DEFAULT 0,
    tempo_medio_entrega INT NOT NULL DEFAULT 0,
    entrega_gratis TINYINT(1) NOT NULL DEFAULT 0,
    valor_minimo_entrega_gratis DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    horario_abertura TIME NOT NULL,
    horario_fechamento TIME NOT NULL,
    status_estabelecimento TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configuração padrão
INSERT INTO configuracoes (
    nome_estabelecimento,
    email_contato,
    telefone,
    endereco,
    taxa_entrega,
    raio_entrega,
    tempo_medio_entrega,
    entrega_gratis,
    valor_minimo_entrega_gratis,
    horario_abertura,
    horario_fechamento,
    status_estabelecimento
) VALUES (
    'Meu Delivery',
    'contato@meudelivery.com',
    '(11) 99999-9999',
    'Rua Exemplo, 123 - Bairro - Cidade/UF',
    5.00,
    10,
    45,
    1,
    50.00,
    '09:00:00',
    '23:00:00',
    1
); 