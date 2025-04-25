<?php
require_once '../config/database.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_logado'])) {
    header('Location: login.php');
    exit();
}

// Inicializa variáveis
$produtos = [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .produto-item {
            transition: all 0.3s ease;
        }
        .produto-item:hover {
            background-color: #f8f9fa;
        }
        .img-thumbnail {
            border: 1px solid #dee2e6;
            padding: 0.25rem;
            background-color: #fff;
            border-radius: 0.25rem;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Meu Carrinho</h1>
        
        <div id="carrinho-vazio" class="alert alert-info" style="display: none;">
            Seu carrinho está vazio. <a href="/">Voltar para a loja</a>
        </div>

        <div id="carrinho-conteudo" style="display: none;">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body" id="lista-produtos">
                            <!-- Produtos serão inseridos aqui via JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Resumo do Pedido</h5>
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
                                <strong id="total">R$ 5,00</strong>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100">Finalizar Pedido</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            console.log('Conteúdo do carrinho:', carrinho);
            
            if (carrinho.length === 0) {
                document.getElementById('carrinho-vazio').style.display = 'block';
                document.getElementById('carrinho-conteudo').style.display = 'none';
                return;
            }

            document.getElementById('carrinho-vazio').style.display = 'none';
            document.getElementById('carrinho-conteudo').style.display = 'block';

            // Buscar produtos do carrinho
            fetch(`../api/produtos.php?ids=${carrinho.map(item => item.id).join(',')}`)
                .then(response => {
                    console.log('URL da requisição:', `../api/produtos.php?ids=${carrinho.map(item => item.id).join(',')}`);
                    console.log('Status da resposta:', response.status);
                    return response.json();
                })
                .then(response => {
                    console.log('Resposta completa:', response);
                    
                    if (!response.success) {
                        throw new Error(response.error || 'Erro ao carregar produtos');
                    }
                    
                    const produtos = response.data;
                    if (!produtos || produtos.length === 0) {
                        throw new Error('Nenhum produto encontrado');
                    }
                    
                    const listaProdutos = document.getElementById('lista-produtos');
                    let subtotal = 0;

                    produtos.forEach(produto => {
                        const item = carrinho.find(i => i.id === produto.id);
                        const totalItem = produto.preco * item.quantidade;
                        subtotal += totalItem;

                        // Log para debug da imagem
                        console.log('Dados da imagem do produto:', {
                            id: produto.id,
                            nome: produto.nome,
                            imagem: produto.imagem,
                            debug: produto.debug
                        });

                        const produtoHTML = `
                            <div class="produto-item mb-3 border-bottom pb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img src="../admin/uploads/${produto.imagem}" 
                                             alt="${produto.nome}" 
                                             class="img-thumbnail" 
                                             style="width: 100px; height: 100px; object-fit: cover;"
                                             onerror="this.src='../assets/images/no-image.png'">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">${produto.nome}</h5>
                                                <p class="text-muted mb-0">R$ ${parseFloat(produto.preco).toFixed(2)}</p>
                                                <p class="text-muted small mb-0">${produto.descricao || ''}</p>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="atualizarQuantidade(${produto.id}, -1)">-</button>
                                                    <span class="mx-2">${item.quantidade}</span>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="atualizarQuantidade(${produto.id}, 1)">+</button>
                                                </div>
                                                <button class="btn btn-sm btn-danger" onclick="removerItem(${produto.id})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-end mt-2">
                                            <strong>Total: R$ ${(produto.preco * item.quantidade).toFixed(2)}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        listaProdutos.innerHTML += produtoHTML;
                    });

                    // Atualizar totais
                    document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2)}`;
                    document.getElementById('total').textContent = `R$ ${(subtotal + 5).toFixed(2)}`;
                })
                .catch(error => {
                    console.error('Erro detalhado:', error);
                    document.getElementById('carrinho-vazio').style.display = 'block';
                    document.getElementById('carrinho-conteudo').style.display = 'none';
                    document.getElementById('carrinho-vazio').innerHTML = `
                        Erro ao carregar os produtos do carrinho. <br>
                        Por favor, tente novamente. <br>
                        <a href="/" class="btn btn-primary mt-2">Voltar para a loja</a>
                    `;
                });
        });

        function atualizarQuantidade(produtoId, delta) {
            let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            const item = carrinho.find(i => i.id === produtoId);
            
            if (item) {
                item.quantidade += delta;
                if (item.quantidade <= 0) {
                    carrinho = carrinho.filter(i => i.id !== produtoId);
                }
                localStorage.setItem('carrinho', JSON.stringify(carrinho));
                location.reload();
            }
        }
        
        function removerItem(produtoId) {
            let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            carrinho = carrinho.filter(i => i.id !== produtoId);
            localStorage.setItem('carrinho', JSON.stringify(carrinho));
            location.reload();
        }
    </script>
</body>
</html> 