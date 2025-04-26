<?php
// Definir o diretório base
define('BASE_DIR', dirname(__DIR__));

// Incluir o arquivo de configuração do banco de dados
require_once BASE_DIR . '/config/database.php';

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'] ?? null;
    $novo_status = $_POST['novo_status'] ?? null;

    if (!$pedido_id || !$novo_status) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
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

        // Notificar os clientes via WebSocket
        try {
            $ws = new WebSocket\Client("ws://localhost:8080");
            $ws->send(json_encode(['type' => 'pedido_atualizado']));
            $ws->close();
        } catch (Exception $e) {
            // Log do erro, mas continua o processo
            error_log("Erro ao notificar via WebSocket: " . $e->getMessage());
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
} 