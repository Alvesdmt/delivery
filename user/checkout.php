<?php
require_once '../config/database.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_logado'])) {
    header('Location: login.php');
    exit();
}

// Busca os dados do cliente
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$_SESSION['cliente_id']]);
$cliente = $stmt->fetch();

// Busca os produtos do carrinho
$carrinho = isset($_COOKIE['carrinho']) ? json_decode($_COOKIE['carrinho'], true) : [];
$produtos = [];
$total = 0;

if (!empty($carrinho)) {
    $ids = array_map(function($item) { return $item['id']; }, $carrinho);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $produtos = $stmt->fetchAll();
    
    // Calcula o total
    foreach ($produtos as $produto) {
        $item = array_filter($carrinho, function($i) use ($produto) {
            return $i['id'] == $produto['id'];
        });
        $item = reset($item);
        $total += $produto['preco'] * $item['quantidade'];
    }
}

// Processa o pedido se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Insere o pedido
        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, total, status) VALUES (?, ?, 'pendente')");
        $stmt->execute([$_SESSION['cliente_id'], $total + 5]);
        $pedidoId = $pdo->lastInsertId();
        
        // Insere os itens do pedido
        $stmt = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        foreach ($produtos as $produto) {
            $item = array_filter($carrinho, function($i) use ($produto) {
                return $i['id'] == $produto['id'];
            });
            $item = reset($item);
            $stmt->execute([$pedidoId, $produto['id'], $item['quantidade'], $produto['preco']]);
        }
        
        $pdo->commit();
        
        // Limpa o carrinho
        setcookie('carrinho', '', time() - 3600, '/');
        
        // Redireciona para a página de sucesso
        header('Location: pedido-sucesso.php?id=' . $pedidoId);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = "Erro ao processar o pedido. Por favor, tente novamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Finalizar Pedido</h1>
        
        <?php if (empty($produtos)): ?>
            <div class="alert alert-info">
                Seu carrinho está vazio. <a href="/">Voltar para a loja</a>
            </div>
        <?php else: ?>
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Informações de Entrega</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                                           value="<?php echo htmlspecialchars($cliente['telefone']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="endereco" class="form-label">Endereço de Entrega</label>
                                    <textarea class="form-control" id="endereco" name="endereco" rows="3" required><?php echo htmlspecialchars($cliente['endereco']); ?></textarea>
                                </div>
                                
                                <h5 class="card-title mt-4">Resumo do Pedido</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                                <th>Preço</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($produtos as $produto): 
                                                $item = array_filter($carrinho, function($i) use ($produto) {
                                                    return $i['id'] == $produto['id'];
                                                });
                                                $item = reset($item);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                                <td><?php echo $item['quantidade']; ?></td>
                                                <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                                <td>R$ <?php echo number_format($produto['preco'] * $item['quantidade'], 2, ',', '.'); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                                <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Taxa de Entrega:</strong></td>
                                                <td>R$ 5,00</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td>R$ <?php echo number_format($total + 5, 2, ',', '.'); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Confirmar Pedido</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 