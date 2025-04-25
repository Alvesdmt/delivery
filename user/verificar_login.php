<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'logado' => isset($_SESSION['cliente_logado']),
    'cliente_id' => $_SESSION['cliente_id'] ?? null,
    'cliente_nome' => $_SESSION['cliente_nome'] ?? null
]);
?> 