<?php
require_once '../config/database.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_logado'])) {
    header('Location: login.php');
    exit();
}

// Verifica se foi fornecido um ID de pedido
if (!isset($_GET['id'])) {
    header('Location: carrinho.php');
    exit();
}

$pedidoId = $_GET['id'];

// Busca os detalhes do pedido
$pdo = getConnection();
$stmt = $pdo->prepare("
    SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone
    FROM pedidos p
    JOIN clientes c ON p.cliente_id = c.id
    WHERE p.id = ? AND p.cliente_id = ?
");
$stmt->execute([$pedidoId, $_SESSION['cliente_id']]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header('Location: carrinho.php');
    exit();
}

// Busca os itens do pedido
$stmt = $pdo->prepare("
    SELECT pi.*, pr.nome as produto_nome, pr.imagem
    FROM pedido_itens pi
    JOIN produtos pr ON pi.produto_id = pr.id
    WHERE pi.pedido_id = ?
");
$stmt->execute([$pedidoId]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2ecc71;
            --dark-green: #27ae60;
            --light-green: #e8f5e9;
            --gray-bg: #f8f9fa;
        }

        body {
            background-color: var(--gray-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .success-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            margin: 20px 0;
        }

        .success-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }

        .check-icon {
            color: var(--primary-green);
            animation: pulse 2s infinite;
            background: var(--light-green);
            padding: 20px;
            border-radius: 50%;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .order-number {
            background: var(--light-green);
            padding: 10px 20px;
            border-radius: 30px;
            display: inline-block;
            font-weight: bold;
            color: var(--dark-green);
            font-size: 1.1rem;
        }

        .status-badge {
            background: var(--light-green);
            color: var(--dark-green);
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.3s ease;
        }

        .order-item:hover {
            background-color: var(--light-green);
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            color: var(--dark-green);
            text-align: right;
            min-width: 100px;
        }

        .total-section {
            background: var(--light-green);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-label {
            color: #666;
        }

        .total-value {
            font-weight: 600;
            color: var(--dark-green);
        }

        .final-total {
            font-size: 1.3rem;
            color: var(--primary-green);
        }

        .btn-primary {
            background-color: var(--primary-green);
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            max-width: 300px;
        }

        .btn-primary:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        .section-title {
            color: var(--dark-green);
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-green);
            border-radius: 3px;
        }

        .delivery-address {
            background: var(--light-green);
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid #e0e0e0;
        }

        @media (max-width: 768px) {
            .success-card {
                margin: 10px;
                border-radius: 12px;
            }

            .check-icon {
                padding: 15px;
                font-size: 3rem;
            }

            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-image {
                width: 100%;
                height: 150px;
                margin-bottom: 10px;
            }

            .item-price {
                text-align: left;
                margin-top: 10px;
                width: 100%;
            }

            .total-section {
                padding: 15px;
            }

            .btn-primary {
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card success-card">
                    <div class="card-body text-center p-4 p-md-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill check-icon" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="card-title mb-4" style="color: var(--primary-green);">Pedido Confirmado!</h2>
                        <p class="lead mb-4">Seu pedido foi recebido e está sendo processado.</p>
                        
                        <div class="mt-4 mb-4">
                            <h5 class="section-title">Detalhes do Pedido</h5>
                            <div class="order-number mb-2">#<?php echo $pedidoId; ?></div>
                            <div class="status-badge"><?php echo ucfirst($pedido['status']); ?></div>
                        </div>

                        <div class="order-items">
                            <h5 class="section-title">Itens do Pedido</h5>
                            <?php foreach ($itens as $item): ?>
                            <div class="order-item">
                                <img src="/../admin/uploads/<?php echo htmlspecialchars($item['imagem']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['produto_nome']); ?>" 
                                     class="item-image"
                                     onerror="this.src='../assets/images/no-image.png'">
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['produto_nome']); ?></div>
                                    <div class="item-quantity">Quantidade: <?php echo $item['quantidade']; ?></div>
                                </div>
                                <div class="item-price">
                                    R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="total-section">
                            <div class="total-row">
                                <span class="total-label">Subtotal</span>
                                <span class="total-value">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Taxa de Entrega</span>
                                <span class="total-value">R$ <?php echo number_format($pedido['taxa_entrega'], 2, ',', '.'); ?></span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Total</span>
                                <span class="total-value final-total">R$ <?php echo number_format($pedido['valor_total'] + $pedido['taxa_entrega'], 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <div class="delivery-address mt-4">
                            <h5 class="section-title">Endereço de Entrega</h5>
                            <p class="mb-0">
                                <?php echo htmlspecialchars($pedido['endereco_entrega']); ?>, 
                                <?php echo htmlspecialchars($pedido['numero_entrega']); ?>
                                <?php if (!empty($pedido['complemento_entrega'])): ?>
                                    - <?php echo htmlspecialchars($pedido['complemento_entrega']); ?>
                                <?php endif; ?>
                                <br>
                                <?php echo htmlspecialchars($pedido['bairro_entrega']); ?> - 
                                <?php echo htmlspecialchars($pedido['cidade_entrega']); ?>
                                <br>
                                CEP: <?php echo htmlspecialchars($pedido['cep_entrega']); ?>
                            </p>
                        </div>

                        <div class="mt-4">
                            <a href="../index.php" class="btn btn-primary">Voltar para a Página Inicial</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 