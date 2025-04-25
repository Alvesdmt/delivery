<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Validar dados recebidos
if (!isset($_POST['pedido_id']) || !isset($_POST['novo_status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit();
}

$pedido_id = $_POST['pedido_id'];
$novo_status = $_POST['novo_status'];

// Validar status
$status_permitidos = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
if (!in_array($novo_status, $status_permitidos)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit();
}

try {
    $pdo = getConnection();
    
    // Verificar se o pedido existe
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id = ?");
    $stmt->execute([$pedido_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Pedido não encontrado');
    }
    
    // Atualizar status
    $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->execute([$novo_status, $pedido_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 