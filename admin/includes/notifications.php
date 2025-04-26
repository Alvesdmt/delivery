<?php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Caminho correto para o arquivo de banco de dados
require_once __DIR__ . '/../../config/database.php';

// Função para adicionar uma nova notificação
function addNotification($message, $playSound = true, $pedidoId = null, $redirectUrl = null) {
    if (!isset($_SESSION['notifications'])) {
        $_SESSION['notifications'] = [];
    }
    $_SESSION['notifications'][] = [
        'message' => $message,
        'time' => date('Y-m-d H:i:s'),
        'read' => false,
        'playSound' => $playSound,
        'pedidoId' => $pedidoId,
        'redirectUrl' => $redirectUrl
    ];
}

// Função para adicionar notificação de novo pedido
function addNewOrderNotification($pedidoId) {
    try {
        $pdo = getConnection();
        
        // Buscar informações do pedido e do cliente
        $stmt = $pdo->prepare("
            SELECT p.id, c.nome, c.telefone 
            FROM pedidos p 
            JOIN clientes c ON p.cliente_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$pedidoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido) {
            $message = sprintf(
                "Novo pedido #%s\nCliente: %s\nTelefone: %s",
                str_pad($pedidoId, 6, '0', STR_PAD_LEFT),
                $pedido['nome'],
                $pedido['telefone']
            );
            $redirectUrl = "detalhes_pedido.php?id=" . $pedidoId;
            addNotification($message, true, $pedidoId, $redirectUrl);
        }
    } catch (Exception $e) {
        error_log("Erro ao adicionar notificação: " . $e->getMessage());
        // Em caso de erro, adiciona uma notificação básica
        $message = "Novo pedido #" . str_pad($pedidoId, 6, '0', STR_PAD_LEFT) . " recebido!";
        $redirectUrl = "detalhes_pedido.php?id=" . $pedidoId;
        addNotification($message, true, $pedidoId, $redirectUrl);
    }
}

// Função para obter notificações não lidas
function getUnreadNotifications() {
    if (!isset($_SESSION['notifications'])) {
        return [];
    }
    return array_filter($_SESSION['notifications'], function($notification) {
        return !$notification['read'];
    });
}

// Função para marcar notificações como lidas
function markNotificationsAsRead() {
    if (isset($_SESSION['notifications'])) {
        foreach ($_SESSION['notifications'] as &$notification) {
            $notification['read'] = true;
        }
    }
}

// Verificar se há novos pedidos
function checkNewOrders() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pedidos WHERE status = 'pendente'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

// Processar ações
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get':
            // Verificar novos pedidos
            if (checkNewOrders()) {
                addNotification("Novo pedido recebido!", true);
            }
            
            $notifications = getUnreadNotifications();
            echo json_encode([
                'count' => count($notifications),
                'notifications' => $notifications,
                'hasSound' => count(array_filter($notifications, function($n) { return $n['playSound']; })) > 0
            ]);
            break;
            
        case 'mark_read':
            markNotificationsAsRead();
            echo json_encode(['success' => true]);
            break;
    }
    exit;
}
?> 