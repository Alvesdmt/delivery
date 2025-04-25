<?php
require_once 'config/database.php';
session_start();

// Estabelecer conexão com o banco de dados
$pdo = getConnection();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery - Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <?php if (isset($_SESSION['cliente_logado'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/pedidos">
                                <i class="bi bi-bag"></i> Meus Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user/carrinho">
                                <i class="bi bi-cart"></i> Carrinho
                                <span id="contador-carrinho" class="badge bg-danger" style="display: none;">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user/conta">
                                <i class="bi bi-person"></i> <?php echo $_SESSION['cliente_nome']; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user/logout">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/login">
                                <i class="bi bi-person"></i> Entrar/Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold">Delivery Rápido e Seguro</h1>
                    <p class="lead">Peça seus produtos favoritos e receba em casa com toda segurança e qualidade.</p>
                    <a href="/#produtos" class="btn btn-light btn-lg">Ver Produtos</a>
                </div>
                <div class="col-md-6">
                    <img src="assets/images/delivery-hero.png" alt="Delivery" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Produtos Section -->
    <div id="produtos" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Nossos Produtos</h2>
            
            <!-- Filtros e Busca -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="busca" class="form-control" placeholder="Buscar produtos...">
                        <button class="btn btn-outline-primary" type="button" id="btn-buscar">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <select id="ordenar" class="form-select" style="max-width: 200px;">
                            <option value="nome">Ordenar por nome</option>
                            <option value="preco_asc">Menor preço</option>
                            <option value="preco_desc">Maior preço</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Produtos -->
            <div class="row" id="lista-produtos">
                <?php
                try {
                    // Verificar conexão
                    if (!$pdo) {
                        throw new Exception("Erro na conexão com o banco de dados");
                    }

                    // Testar a consulta
                    $stmt = $pdo->query("SELECT * FROM produtos WHERE status = 1 ORDER BY nome ASC");
                    
                    if (!$stmt) {
                        throw new Exception("Erro na consulta SQL: " . implode(" ", $pdo->errorInfo()));
                    }

                    $totalProdutos = $stmt->rowCount();
                    
                    if ($totalProdutos > 0) {
                        while ($produto = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            // Debug: mostrar dados do produto
                            echo "<!-- Debug Produto: " . print_r($produto, true) . " -->";
                            
                            // Verificar se o arquivo existe
                            $caminhoImagem = "admin/uploads/" . $produto['imagem'];
                            $imagemExiste = file_exists($caminhoImagem);
                            echo "<!-- Imagem existe: " . ($imagemExiste ? 'Sim' : 'Não') . " -->";
                            echo "<!-- Caminho completo: " . realpath($caminhoImagem) . " -->";
                ?>
                <div class="col-md-4 mb-4 produto-item" 
                     data-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                     data-preco="<?php echo $produto['preco']; ?>">
                    <div class="card h-100">
                        <img src="admin/uploads/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                            <p class="card-text">
                                <strong class="text-primary">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong>
                            </p>
                            <?php if (isset($_SESSION['cliente_logado'])): ?>
                                <button class="btn btn-primary w-100" onclick="adicionarAoCarrinho(<?php echo $produto['id']; ?>)">
                                    <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                                </button>
                            <?php else: ?>
                                <a href="user/login" class="btn btn-primary w-100">
                                    <i class="bi bi-person"></i> Faça login para comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
                        }
                    } else {
                        echo '<div class="col-12 text-center"><p class="text-muted">Nenhum produto encontrado no banco de dados.</p></div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="col-12 text-center"><p class="text-danger">Erro ao carregar produtos: ' . $e->getMessage() . '</p></div>';
                } catch (Exception $e) {
                    echo '<div class="col-12 text-center"><p class="text-danger">Erro: ' . $e->getMessage() . '</p></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Sobre Nós</h5>
                    <p>Delivery rápido e seguro para você.</p>
                </div>
                <div class="col-md-4">
                    <h5>Contato</h5>
                    <p>
                        <i class="bi bi-telephone"></i> (00) 0000-0000<br>
                        <i class="bi bi-envelope"></i> contato@delivery.com
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Redes Sociais</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Delivery. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscaInput = document.getElementById('busca');
            const ordenarSelect = document.getElementById('ordenar');
            const listaProdutos = document.getElementById('lista-produtos');
            const produtos = Array.from(document.querySelectorAll('.produto-item'));

            function filtrarProdutos() {
                const termoBusca = buscaInput.value.toLowerCase();
                const ordem = ordenarSelect.value;

                produtos.forEach(produto => {
                    const nome = produto.dataset.nome.toLowerCase();
                    const preco = parseFloat(produto.dataset.preco);
                    const exibir = nome.includes(termoBusca);
                    produto.style.display = exibir ? 'block' : 'none';
                });

                ordenarProdutos(ordem);
            }

            function ordenarProdutos(ordem) {
                const produtosVisiveis = produtos.filter(p => p.style.display !== 'none');
                
                produtosVisiveis.sort((a, b) => {
                    switch(ordem) {
                        case 'nome':
                            return a.dataset.nome.localeCompare(b.dataset.nome);
                        case 'preco_asc':
                            return parseFloat(a.dataset.preco) - parseFloat(b.dataset.preco);
                        case 'preco_desc':
                            return parseFloat(b.dataset.preco) - parseFloat(a.dataset.preco);
                        default:
                            return 0;
                    }
                });

                produtosVisiveis.forEach(produto => {
                    listaProdutos.appendChild(produto);
                });
            }

            buscaInput.addEventListener('input', filtrarProdutos);
            ordenarSelect.addEventListener('change', filtrarProdutos);
        });

        function adicionarAoCarrinho(idProduto) {
            // Verifica se já existe um carrinho no localStorage
            let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            
            // Verifica se o produto já está no carrinho
            const produtoExistente = carrinho.find(item => item.id === idProduto);
            
            if (produtoExistente) {
                // Se o produto já existe, incrementa a quantidade
                produtoExistente.quantidade += 1;
            } else {
                // Se não existe, adiciona novo item
                carrinho.push({
                    id: idProduto,
                    quantidade: 1
                });
            }
            
            // Salva o carrinho atualizado no localStorage
            localStorage.setItem('carrinho', JSON.stringify(carrinho));
            
            // Mostra mensagem de sucesso
            alert('Produto adicionado ao carrinho!');
            
            // Atualiza o contador do carrinho
            const contador = document.getElementById('contador-carrinho');
            if (contador) {
                const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
                contador.style.display = totalItens > 0 ? 'inline' : 'none';
                contador.textContent = totalItens;
            }
        }
    </script>
</body>
</html>