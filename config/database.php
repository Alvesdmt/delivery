<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'delivery_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Função para conectar ao banco de dados
function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            )
        );
        return $pdo;
    } catch(PDOException $e) {
        die('Erro na conexão com o banco de dados: ' . $e->getMessage());
    }
}

// Função para testar a conexão
function testConnection() {
    try {
        $pdo = getConnection();
        echo "Conexão com o banco de dados estabelecida com sucesso!";
        return true;
    } catch(PDOException $e) {
        echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
        return false;
    }
}
?>
