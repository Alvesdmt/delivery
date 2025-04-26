<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Função para gerar relatório de vendas
function gerarRelatorioVendas($dataInicial, $dataFinal) {
    $pdo = getConnection();
    $sql = "SELECT 
                DATE(p.data_pedido) as data,
                COUNT(*) as total_pedidos,
                SUM(p.valor_total) as valor_total,
                AVG(p.valor_total) as valor_medio
            FROM pedidos p
            WHERE p.data_pedido BETWEEN :data_inicial AND :data_final
            GROUP BY DATE(p.data_pedido)
            ORDER BY data DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicial' => $dataInicial,
        ':data_final' => $dataFinal
    ]);
    
    return $stmt->fetchAll();
}

// Função para gerar relatório de clientes
function gerarRelatorioClientes($dataInicial, $dataFinal) {
    $pdo = getConnection();
    $sql = "SELECT 
                c.nome,
                c.email,
                c.telefone,
                COUNT(p.id) as total_pedidos,
                SUM(p.valor_total) as valor_total_gasto
            FROM clientes c
            LEFT JOIN pedidos p ON c.id = p.cliente_id
            WHERE p.data_pedido BETWEEN :data_inicial AND :data_final
            GROUP BY c.id
            ORDER BY valor_total_gasto DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicial' => $dataInicial,
        ':data_final' => $dataFinal
    ]);
    
    return $stmt->fetchAll();
}

// Função para gerar relatório de produtos
function gerarRelatorioProdutos($dataInicial, $dataFinal) {
    $pdo = getConnection();
    $sql = "SELECT 
                pr.nome,
                pr.preco,
                SUM(pp.quantidade) as quantidade_vendida,
                SUM(pp.quantidade * pp.preco_unitario) as valor_total
            FROM produtos pr
            LEFT JOIN pedido_produtos pp ON pr.id = pp.produto_id
            LEFT JOIN pedidos p ON pp.pedido_id = p.id
            WHERE p.data_pedido BETWEEN :data_inicial AND :data_final
            GROUP BY pr.id
            ORDER BY quantidade_vendida DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicial' => $dataInicial,
        ':data_final' => $dataFinal
    ]);
    
    return $stmt->fetchAll();
}

// Função para gerar relatório financeiro
function gerarRelatorioFinanceiro($dataInicial, $dataFinal) {
    $pdo = getConnection();
    $sql = "SELECT 
                DATE(p.data_pedido) as data,
                SUM(p.valor_total) as receita_total,
                SUM(CASE WHEN p.status = 'cancelado' THEN p.valor_total ELSE 0 END) as valor_cancelamentos,
                COUNT(CASE WHEN p.status = 'cancelado' THEN 1 END) as total_cancelamentos,
                COUNT(*) as total_pedidos
            FROM pedidos p
            WHERE p.data_pedido BETWEEN :data_inicial AND :data_final
            GROUP BY DATE(p.data_pedido)
            ORDER BY data DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicial' => $dataInicial,
        ':data_final' => $dataFinal
    ]);
    
    return $stmt->fetchAll();
}

// Função para gerar relatório de avaliações
function gerarRelatorioAvaliacoes($dataInicial, $dataFinal) {
    $pdo = getConnection();
    $sql = "SELECT 
                a.nota,
                a.comentario,
                c.nome as cliente_nome,
                p.data_pedido,
                COUNT(*) as total_avaliacoes,
                AVG(a.nota) as media_geral
            FROM avaliacoes a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN pedidos p ON a.pedido_id = p.id
            WHERE p.data_pedido BETWEEN :data_inicial AND :data_final
            GROUP BY a.nota
            ORDER BY a.nota DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicial' => $dataInicial,
        ':data_final' => $dataFinal
    ]);
    
    return $stmt->fetchAll();
}

// Função para gerar relatório de entregas
function gerarRelatorioEntregas($dataInicial, $dataFinal) {
    $pdo = getConnection();
    $sql = "SELECT 
                p.id as pedido_id,
                p.data_pedido,
                p.status,
                p.tempo_entrega,
                c.nome as cliente_nome,
                c.endereco,
                COUNT(*) as total_entregas,
                AVG(p.tempo_entrega) as tempo_medio_entrega
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE p.data_pedido BETWEEN :data_inicial AND :data_final
            GROUP BY p.status
            ORDER BY p.data_pedido DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicial' => $dataInicial,
        ':data_final' => $dataFinal
    ]);
    
    return $stmt->fetchAll();
}

// Processar requisição AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoRelatorio = $_POST['tipo_relatorio'] ?? '';
    $dataInicial = $_POST['data_inicial'] ?? date('Y-m-d', strtotime('-30 days'));
    $dataFinal = $_POST['data_final'] ?? date('Y-m-d');
    
    $resultado = [];
    
    switch ($tipoRelatorio) {
        case 'vendas':
            $resultado = gerarRelatorioVendas($dataInicial, $dataFinal);
            break;
        case 'clientes':
            $resultado = gerarRelatorioClientes($dataInicial, $dataFinal);
            break;
        case 'produtos':
            $resultado = gerarRelatorioProdutos($dataInicial, $dataFinal);
            break;
        case 'financeiro':
            $resultado = gerarRelatorioFinanceiro($dataInicial, $dataFinal);
            break;
        case 'avaliacoes':
            $resultado = gerarRelatorioAvaliacoes($dataInicial, $dataFinal);
            break;
        case 'entregas':
            $resultado = gerarRelatorioEntregas($dataInicial, $dataFinal);
            break;
        default:
            http_response_code(400);
            echo json_encode(['erro' => 'Tipo de relatório inválido']);
            exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit;
}
?> 