<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$telefone = $_GET['telefone'] ?? '';

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE telefone = ?");
    $stmt->execute([$telefone]);
    
    echo json_encode([
        'exists' => $stmt->rowCount() > 0
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao verificar telefone']);
} 