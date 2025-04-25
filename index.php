<?php
require_once 'config/database.php';
session_start();
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
            <div class="row">
                <?php
                $stmt = $pdo->query("SELECT * FROM produtos WHERE status = 1");
                while ($produto = $stmt->fetch()) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $produto['imagem'] ?: 'assets/images/produto-padrao.jpg'; ?>" 
                             class="card-img-top" 
                             alt="<?php echo $produto['nome']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $produto['nome']; ?></h5>
                            <p class="card-text"><?php echo $produto['descricao']; ?></p>
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
                <?php } ?>
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
    <script src="assets/js/main.js"></script>
</body>
</html>