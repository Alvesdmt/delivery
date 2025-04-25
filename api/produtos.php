<?php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    // Verifica se os IDs foram fornecidos
    if (!isset($_GET['ids']) || empty($_GET['ids'])) {
        throw new Exception('IDs dos produtos não fornecidos');
    }

    $pdo = getConnection();
    if (!$pdo) {
        throw new Exception('Erro ao conectar com o banco de dados');
    }
    
    // Limpa e valida os IDs
    $ids = array_filter(explode(',', $_GET['ids']), 'is_numeric');
    if (empty($ids)) {
        throw new Exception('Nenhum ID válido fornecido');
    }
    
    // Prepara a query com placeholders
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, nome, descricao, preco, imagem FROM produtos WHERE id IN ($placeholders) AND status = 1");
    
    if (!$stmt->execute($ids)) {
        throw new Exception('Erro ao executar a consulta');
    }
    
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($produtos)) {
        throw new Exception('Nenhum produto encontrado');
    }

    // Adiciona informações de debug para cada produto
    foreach ($produtos as &$produto) {
        $caminhoImagem = "../admin/uploads/" . $produto['imagem'];
        $produto['debug'] = [
            'imagem_original' => $produto['imagem'],
            'caminho_completo' => $caminhoImagem,
            'arquivo_existe' => file_exists($caminhoImagem) ? 'sim' : 'não'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $produtos,
        'debug' => [
            'diretorio_atual' => __DIR__,
            'diretorio_uploads' => realpath(__DIR__ . "/../admin/uploads")
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'ids_recebidos' => $_GET['ids'] ?? 'nenhum',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} 