<?php
require_once '../config/database.php';
session_start();

// Configuração para exibir erros durante o desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para enviar resposta JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_logado'])) {
    sendJsonResponse(['error' => 'Usuário não está logado'], 401);
}

// Obtém os dados do cliente logado
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$_SESSION['cliente_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        sendJsonResponse(['error' => 'Cliente não encontrado'], 404);
    }
} catch (Exception $e) {
    error_log("Erro ao buscar cliente: " . $e->getMessage());
    sendJsonResponse(['error' => 'Erro ao buscar dados do cliente'], 500);
}

// Verifica se já existe endereço salvo
$stmt = $pdo->prepare("
    SELECT endereco, numero, complemento, bairro, cidade, cep 
    FROM clientes 
    WHERE id = ?
");
$stmt->execute([$_SESSION['cliente_id']]);
$endereco = $stmt->fetch(PDO::FETCH_ASSOC);

// Gera token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            sendJsonResponse(['error' => 'Token de segurança inválido'], 403);
        }

        // Validação dos campos obrigatórios
        $camposObrigatorios = ['endereco', 'numero', 'bairro', 'cidade', 'cep', 'forma_pagamento', 'itens'];
        foreach ($camposObrigatorios as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                sendJsonResponse(['error' => "Campo obrigatório não preenchido: $campo"], 400);
            }
        }

        // Validação do JSON dos itens
        $itens = json_decode($_POST['itens'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['error' => 'Erro ao decodificar itens do carrinho'], 400);
        }

        if (empty($itens)) {
            sendJsonResponse(['error' => 'Carrinho vazio'], 400);
        }

        $pdo->beginTransaction();

        // Atualiza o endereço do cliente
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET endereco = ?, 
                numero = ?,
                complemento = ?,
                bairro = ?,
                cidade = ?,
                cep = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['endereco'],
            $_POST['numero'],
            $_POST['complemento'] ?? '',
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['cep'],
            $_SESSION['cliente_id']
        ]);

        // Calcula o valor total do pedido
        $valorTotal = 0;
        
        // Busca os preços dos produtos
        $produtoIds = array_column($itens, 'id');
        $placeholders = str_repeat('?,', count($produtoIds) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, preco FROM produtos WHERE id IN ($placeholders)");
        $stmt->execute($produtoIds);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcula o valor total
        foreach ($itens as $item) {
            $produto = array_filter($produtos, function($p) use ($item) { return $p['id'] == $item['id']; });
            $produto = reset($produto);
            if (!$produto) {
                throw new Exception("Produto não encontrado: {$item['id']}");
            }
            $valorTotal += $produto['preco'] * $item['quantidade'];
        }

        // Insere o pedido
        $stmt = $pdo->prepare("
            INSERT INTO pedidos (
                cliente_id,
                valor_total,
                taxa_entrega,
                total,
                forma_pagamento,
                troco_para,
                endereco_entrega,
                numero_entrega,
                complemento_entrega,
                bairro_entrega,
                cidade_entrega,
                cep_entrega,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')
        ");

        $taxaEntrega = 5.00;
        $total = $valorTotal + $taxaEntrega;
        $trocoPara = ($_POST['forma_pagamento'] === 'dinheiro' && isset($_POST['troco_para'])) ? 
                     floatval($_POST['troco_para']) : 
                     null;

        $stmt->execute([
            $_SESSION['cliente_id'],
            $valorTotal,
            $taxaEntrega,
            $total,
            $_POST['forma_pagamento'],
            $trocoPara,
            $_POST['endereco'],
            $_POST['numero'],
            $_POST['complemento'] ?? '',
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['cep']
        ]);

        $pedidoId = $pdo->lastInsertId();

        // Insere os itens do pedido
        $stmt = $pdo->prepare("
            INSERT INTO pedido_itens (
                pedido_id,
                produto_id,
                quantidade,
                preco_unitario,
                subtotal
            ) VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($itens as $item) {
            $produto = array_filter($produtos, function($p) use ($item) { return $p['id'] == $item['id']; });
            $produto = reset($produto);
            $subtotal = $produto['preco'] * $item['quantidade'];
            
            $stmt->execute([
                $pedidoId,
                $item['id'],
                $item['quantidade'],
                $produto['preco'],
                $subtotal
            ]);
        }

        $pdo->commit();
        
        // Adiciona notificação de novo pedido
        require_once '../admin/includes/notifications.php';
        addNewOrderNotification($pedidoId);
        
        sendJsonResponse(['success' => true, 'pedido_id' => $pedidoId]);

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        error_log("Erro no checkout: " . $e->getMessage());
        error_log("Dados do pedido: " . print_r([
            'cliente_id' => $_SESSION['cliente_id'],
            'valor_total' => $valorTotal ?? 0,
            'taxa_entrega' => $taxaEntrega ?? 0,
            'total' => $total ?? 0,
            'forma_pagamento' => $_POST['forma_pagamento'] ?? '',
            'troco_para' => $trocoPara ?? null,
            'endereco' => $_POST['endereco'] ?? '',
            'numero' => $_POST['numero'] ?? '',
            'complemento' => $_POST['complemento'] ?? '',
            'bairro' => $_POST['bairro'] ?? '',
            'cidade' => $_POST['cidade'] ?? '',
            'cep' => $_POST['cep'] ?? ''
        ], true));
        
        sendJsonResponse([
            'error' => 'Erro ao processar o pedido. Por favor, tente novamente.',
            'debug' => $e->getMessage()
        ], 500);
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
    <style>
        :root {
            --primary-color: #2EA44F;
            --primary-hover: #2C974B;
            --secondary-color: #F8F9FA;
            --text-color: #333333;
            --border-color: #E9ECEF;
        }

        body {
            background-color: #F8F9FA;
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .card-title {
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.25rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 164, 79, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .produto-item {
            transition: all 0.3s ease;
        }

        .produto-item:hover {
            transform: translateX(5px);
        }

        .img-thumbnail {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.05);
        }

        .input-group-text {
            background-color: var(--secondary-color);
            border: 1px solid var(--border-color);
        }

        .nav-link {
            color: var(--text-color);
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .total-section {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .total-section h5 {
            color: var(--text-color);
            font-weight: 600;
        }

        .total-section .total-value {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .payment-method {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: rgba(46, 164, 79, 0.05);
        }

        .payment-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Finalizar Pedido</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Endereço de Entrega</h5>
                        <form id="checkout-form" action="checkout.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">CEP</label>
                                    <input type="text" class="form-control" name="cep" id="cep" required
                                           value="<?php echo htmlspecialchars($cliente['cep'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" class="form-control" name="endereco" id="endereco" required
                                           value="<?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Número</label>
                                    <input type="text" class="form-control" name="numero" id="numero" required
                                           value="<?php echo htmlspecialchars($cliente['numero'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Complemento</label>
                                <input type="text" class="form-control" name="complemento" id="complemento"
                                       value="<?php echo htmlspecialchars($cliente['complemento'] ?? ''); ?>">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" class="form-control" name="bairro" id="bairro" required
                                           value="<?php echo htmlspecialchars($cliente['bairro'] ?? ''); ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" class="form-control" name="cidade" id="cidade" required
                                           value="<?php echo htmlspecialchars($cliente['cidade'] ?? ''); ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Forma de Pagamento</h5>
                        <div class="payment-method" onclick="selectPaymentMethod('pix')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="forma_pagamento" id="pix" value="pix" required>
                                <label class="form-check-label" for="pix">
                                    <i class="bi bi-qr-code payment-icon"></i> PIX
                                </label>
                            </div>
                        </div>
                        <div class="payment-method" onclick="selectPaymentMethod('cartao')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="forma_pagamento" id="cartao" value="cartao">
                                <label class="form-check-label" for="cartao">
                                    <i class="bi bi-credit-card payment-icon"></i> Cartão (na entrega)
                                </label>
                            </div>
                        </div>
                        <div class="payment-method" onclick="selectPaymentMethod('dinheiro')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="forma_pagamento" id="dinheiro" value="dinheiro">
                                <label class="form-check-label" for="dinheiro">
                                    <i class="bi bi-cash payment-icon"></i> Dinheiro
                                </label>
                            </div>
                        </div>
                        <div id="troco-group" class="mt-3" style="display: none;">
                            <label for="troco" class="form-label">Troco para quanto?</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="troco" name="troco_para" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Itens do Pedido</h5>
                        <div id="lista-produtos">
                            <!-- Produtos serão inseridos aqui via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="total-section">
                    <h5 class="mb-4">Resumo do Pedido</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">R$ 0,00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Taxa de Entrega:</span>
                        <span>R$ 5,00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="total-value" id="total">R$ 5,00</strong>
                    </div>
                    <button type="button" class="btn btn-primary w-100" onclick="finalizarPedido()">
                        Confirmar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            
            if (carrinho.length === 0) {
                alert('Seu carrinho está vazio. Adicione produtos antes de finalizar o pedido.');
                window.location.href = 'carrinho.php';
                return;
            }

            // Verifica se o usuário está logado
            fetch('verificar_login.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logado) {
                        window.location.href = 'login.php?redirect=checkout';
                        return;
                    }

                    // Carregar produtos do carrinho
                    return fetch(`../api/produtos.php?ids=${carrinho.map(item => item.id).join(',')}`);
                })
                .then(response => {
                    if (!response) return;
                    return response.json();
                })
                .then(response => {
                    if (!response) return;
                    
                    if (!response.success) {
                        throw new Error(response.error || 'Erro ao carregar produtos');
                    }
                    
                    const produtos = response.data;
                    const listaProdutos = document.getElementById('lista-produtos');
                    let subtotal = 0;

                    produtos.forEach(produto => {
                        const item = carrinho.find(i => i.id === produto.id);
                        if (!item) return;

                        const totalItem = produto.preco * item.quantidade;
                        subtotal += totalItem;

                        const produtoHTML = `
                            <div class="produto-item mb-3 border-bottom pb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img src="../admin/uploads/${produto.imagem}" 
                                             alt="${produto.nome}" 
                                             class="img-thumbnail" 
                                             style="width: 80px; height: 80px; object-fit: cover;"
                                             onerror="this.src='../assets/images/no-image.png'">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${produto.nome}</h6>
                                        <p class="text-muted mb-0">
                                            ${item.quantidade}x R$ ${parseFloat(produto.preco).toFixed(2)}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-0">R$ ${totalItem.toFixed(2)}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        listaProdutos.insertAdjacentHTML('beforeend', produtoHTML);
                    });

                    // Atualiza totais
                    document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2)}`;
                    const total = subtotal + 5.00; // Taxa de entrega fixa
                    document.getElementById('total').textContent = `R$ ${total.toFixed(2)}`;
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar produtos: ' + error.message);
                    window.location.href = 'carrinho.php';
                });

            // Controle do campo de troco
            const formaPagamentoInputs = document.querySelectorAll('input[name="forma_pagamento"]');
            const trocoGroup = document.getElementById('troco-group');
            const trocoInput = document.getElementById('troco');

            formaPagamentoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === 'dinheiro') {
                        trocoGroup.style.display = 'block';
                        trocoInput.required = true;
                    } else {
                        trocoGroup.style.display = 'none';
                        trocoInput.required = false;
                        trocoInput.value = '';
                    }
                });
            });
        });

        function finalizarPedido() {
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];

            if (carrinho.length === 0) {
                alert('Seu carrinho está vazio.');
                return;
            }

            // Validar forma de pagamento
            const formaPagamento = document.querySelector('input[name="forma_pagamento"]:checked');
            if (!formaPagamento) {
                alert('Por favor, selecione uma forma de pagamento.');
                return;
            }

            // Validar troco se for pagamento em dinheiro
            if (formaPagamento.value === 'dinheiro') {
                const troco = document.getElementById('troco').value;
                const total = parseFloat(document.getElementById('total').textContent.replace('R$ ', '').replace(',', '.'));
                
                if (!troco) {
                    alert('Por favor, informe o valor para troco.');
                    return;
                }
                
                if (parseFloat(troco) < total) {
                    alert('O valor para troco deve ser maior que o valor total do pedido.');
                    return;
                }
            }

            // Adiciona os itens do carrinho e forma de pagamento ao formData
            formData.append('itens', JSON.stringify(carrinho));
            formData.append('forma_pagamento', formaPagamento.value);
            if (formaPagamento.value === 'dinheiro') {
                formData.append('troco_para', document.getElementById('troco').value);
            }

            // Mostra loading
            const btnConfirmar = document.querySelector('.btn-primary');
            const btnText = btnConfirmar.innerHTML;
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';

            fetch('checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || `HTTP error! status: ${response.status}`);
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    localStorage.removeItem('carrinho');
                    window.location.href = `pedido_sucesso.php?id=${data.pedido_id}`;
                } else {
                    throw new Error(data.error || 'Erro ao finalizar pedido');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao finalizar pedido: ' + error.message);
            })
            .finally(() => {
                // Restaura o botão
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = btnText;
            });
        }

        function selectPaymentMethod(method) {
            document.getElementById(method).checked = true;
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }

        // Busca endereço pelo CEP
        document.getElementById('cep').addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                        }
                    });
            }
        });
    </script>
</body>
</html> 