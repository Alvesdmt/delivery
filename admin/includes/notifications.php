<?php
session_start();
require_once 'db.php';

// Função para adicionar uma nova notificação
function addNotification($message) {
    if (!isset($_SESSION['notifications'])) {
        $_SESSION['notifications'] = [];
    }
    $_SESSION['notifications'][] = [
        'message' => $message,
        'time' => date('Y-m-d H:i:s'),
        'read' => false
    ];
}

// Função para adicionar notificação de novo pedido
function addNewOrderNotification($pedidoId) {
    addNotification("Novo pedido #" . str_pad($pedidoId, 6, '0', STR_PAD_LEFT) . " recebido!");
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
    global $db;
    $newOrders = $db->query("SELECT COUNT(*) as count FROM pedidos WHERE status = 'pendente'")->fetch_assoc();
    return $newOrders['count'] > 0;
}

// Processar ações
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get':
            // Verificar novos pedidos
            if (checkNewOrders()) {
                addNotification("Novo pedido recebido!");
            }
            
            $notifications = getUnreadNotifications();
            echo json_encode([
                'count' => count($notifications),
                'notifications' => $notifications
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