<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login');
    exit();
}

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pedidos.php');
    exit();
}

$pedido_id = $_GET['id'];

try {
    $pdo = getConnection();
    
    // Buscar informações do pedido
    $stmt = $pdo->prepare("
        SELECT p.*, c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) {
        header('Location: pedidos.php');
        exit();
    }
    
    // Buscar itens do pedido
    $stmt = $pdo->prepare("
        SELECT pi.*, pr.nome as produto_nome, pr.imagem as produto_imagem
        FROM pedido_itens pi
        LEFT JOIN produtos pr ON pi.produto_id = pr.id
        WHERE pi.pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    $itens = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $erro = "Erro ao buscar detalhes do pedido: " . $e->getMessage();
}

require_once 'includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido #<?php echo $pedido_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .main-content {
            padding: 20px;
            margin-top: 80px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processando {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-enviado {
            background-color: #d4edda;
            color: #155724;
        }
        .status-entregue {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-cancelado {
            background-color: #f8d7da;
            color: #721c24;
        }
        .produto-imagem {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalhes do Pedido #<?php echo $pedido_id; ?></h2>
                <a href="pedidos.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <h4>Informações do Cliente</h4>
                        <div class="mb-3">
                            <strong>Nome:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong> <?php echo htmlspecialchars($pedido['cliente_email']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['cliente_telefone']); ?>
                        </div>
                    </div>

                    <div class="card">
                        <h4>Informações do Pedido</h4>
                        <div class="mb-3">
                            <strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="status-badge status-<?php echo strtolower($pedido['status']); ?>">
                                <?php echo ucfirst($pedido['status']); ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Forma de Pagamento:</strong> <?php echo ucfirst($pedido['forma_pagamento']); ?>
                        </div>
                        <?php if ($pedido['forma_pagamento'] == 'dinheiro' && $pedido['troco_para']): ?>
                            <div class="mb-3">
                                <strong>Troco para:</strong> R$ <?php echo number_format($pedido['troco_para'], 2, ',', '.'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <strong>Valor dos Produtos:</strong> R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Taxa de Entrega:</strong> R$ <?php echo number_format($pedido['taxa_entrega'], 2, ',', '.'); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Total:</strong> R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                        </div>
                    </div>

                    <div class="card">
                        <h4>Endereço de Entrega</h4>
                        <div class="mb-3">
                            <strong>Endereço:</strong> <?php echo htmlspecialchars($pedido['endereco_entrega']); ?>, 
                            <?php echo htmlspecialchars($pedido['numero_entrega']); ?>
                            <?php if ($pedido['complemento_entrega']): ?>
                                - <?php echo htmlspecialchars($pedido['complemento_entrega']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Bairro:</strong> <?php echo htmlspecialchars($pedido['bairro_entrega']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Cidade:</strong> <?php echo htmlspecialchars($pedido['cidade_entrega']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>CEP:</strong> <?php echo htmlspecialchars($pedido['cep_entrega']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <h4>Itens do Pedido</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($item['produto_imagem']): ?>
                                                        <img src="<?php echo htmlspecialchars($item['produto_imagem']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['produto_nome']); ?>"
                                                             class="produto-imagem me-2">
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($item['produto_nome']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo $item['quantidade']; ?></td>
                                            <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                            <td>R$ <?php echo number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 