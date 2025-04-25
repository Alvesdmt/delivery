<?php
require_once '../config/database.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_logado'])) {
    header('Location: login.php');
    exit();
}

// Verifica se o ID do pedido foi fornecido
if (!isset($_GET['id'])) {
    header('Location: pedidos.php');
    exit();
}

// Busca os dados do pedido
$stmt = $pdo->prepare("
    SELECT p.*, c.nome as cliente_nome, c.telefone, c.endereco 
    FROM pedidos p 
    JOIN clientes c ON p.cliente_id = c.id 
    WHERE p.id = ? AND p.cliente_id = ?
");
$stmt->execute([$_GET['id'], $_SESSION['cliente_id']]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: pedidos.php');
    exit();
}

// Busca os itens do pedido
$stmt = $pdo->prepare("
    SELECT pi.*, pr.nome as produto_nome 
    FROM pedido_itens pi 
    JOIN produtos pr ON pi.produto_id = pr.id 
    WHERE pi.pedido_id = ?
");
$stmt->execute([$pedido['id']]);
$itens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h1 class="card-title">Pedido Confirmado!</h1>
                        <p class="lead">Seu pedido foi recebido e está sendo processado.</p>
                        
                        <div class="alert alert-info mt-4">
                            <h5 class="alert-heading">Detalhes do Pedido</h5>
                            <p class="mb-0">
                                Número do Pedido: <strong>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></strong><br>
                                Status: <span class="badge bg-warning"><?php echo ucfirst($pedido['status']); ?></span>
                            </p>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Itens do Pedido</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Preço Unitário</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($itens as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                            <td><?php echo $item['quantidade']; ?></td>
                                            <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                            <td>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                            <td>R$ <?php echo number_format($pedido['total'] - 5, 2, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Taxa de Entrega:</strong></td>
                                            <td>R$ 5,00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Informações de Entrega</h5>
                            <p class="mb-1">
                                <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?><br>
                                <strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['telefone']); ?><br>
                                <strong>Endereço:</strong> <?php echo nl2br(htmlspecialchars($pedido['endereco'])); ?>
                            </p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="pedidos.php" class="btn btn-primary">Ver Meus Pedidos</a>
                            <a href="/" class="btn btn-outline-primary">Voltar para a Loja</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 