<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['cliente_logado'])) {
    header('Location: login');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Buscar pedidos do cliente
$stmt = $pdo->prepare("
    SELECT p.*, 
           COUNT(pi.id) as total_itens,
           SUM(pi.quantidade * pi.preco_unitario) as valor_total
    FROM pedidos p
    LEFT JOIN pedido_itens pi ON p.id = pi.pedido_id
    WHERE p.cliente_id = ?
    GROUP BY p.id
    ORDER BY p.data DESC
");
$stmt->execute([$cliente_id]);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-bag-check"></i> Delivery
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#produtos">Produtos</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="pedidos">
                            <i class="bi bi-bag"></i> Meus Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="conta">
                            <i class="bi bi-person"></i> <?php echo $_SESSION['cliente_nome']; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Meus Pedidos</h2>
        
        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info">
                Você ainda não fez nenhum pedido.
                <a href="/#produtos" class="alert-link">Ver produtos</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pedido #</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data'])); ?></td>
                            <td><?php echo $pedido['total_itens']; ?> itens</td>
                            <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($pedido['status']) {
                                        'pendente' => 'warning',
                                        'preparando' => 'info',
                                        'entregue' => 'success',
                                        'cancelado' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="verDetalhes(<?php echo $pedido['id']; ?>)">
                                    <i class="bi bi-eye"></i> Detalhes
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Detalhes do Pedido -->
    <div class="modal fade" id="pedidoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pedidoDetalhes">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalhes(pedidoId) {
            fetch(`/api/pedidos/${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    const modal = new bootstrap.Modal(document.getElementById('pedidoModal'));
                    document.getElementById('pedidoDetalhes').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informações do Pedido</h6>
                                <p><strong>Número:</strong> #${String(pedidoId).padStart(6, '0')}</p>
                                <p><strong>Data:</strong> ${new Date(data.data).toLocaleString()}</p>
                                <p><strong>Status:</strong> ${data.status}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Itens do Pedido</h6>
                                <ul class="list-group">
                                    ${data.itens.map(item => `
                                        <li class="list-group-item">
                                            ${item.quantidade}x ${item.produto_nome} - 
                                            R$ ${item.preco_unitario.toFixed(2)}
                                        </li>
                                    `).join('')}
                                </ul>
                                <div class="mt-3">
                                    <h6>Total: R$ ${data.total.toFixed(2)}</h6>
                                </div>
                            </div>
                        </div>
                    `;
                    modal.show();
                });
        }
    </script>
</body>
</html> 