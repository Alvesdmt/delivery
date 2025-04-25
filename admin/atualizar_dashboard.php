<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

try {
    $pdo = getConnection();
    
    // Buscar contagem de pedidos por status
    $stmt = $pdo->query("SELECT 
        COUNT(CASE WHEN status = 'pendente' THEN 1 END) as total_pendentes,
        COUNT(CASE WHEN status = 'processando' THEN 1 END) as total_processando,
        COUNT(CASE WHEN status = 'enviado' THEN 1 END) as total_enviados,
        COUNT(CASE WHEN status = 'entregue' THEN 1 END) as total_entregues,
        COUNT(CASE WHEN DATE(data) = CURDATE() THEN 1 END) as total_hoje
    FROM pedidos");
    
    $contagem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($contagem);
    
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?> 