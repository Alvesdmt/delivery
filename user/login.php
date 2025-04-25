<?php
require_once '../config/database.php';
session_start();

// Se o usuário já estiver logado, redireciona para a página inicial
if (isset($_SESSION['cliente_logado'])) {
    header('Location: user/carrinho');
    exit;
}

$erro = '';

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefone = $_POST['telefone'] ?? '';
    
    // Validar telefone
    if (empty($telefone)) {
        $erro = 'Por favor, informe seu telefone';
    } else {
        try {
            $pdo = getConnection();
            
            // Verificar se o cliente já existe
            $stmt = $pdo->prepare("SELECT id, telefone FROM clientes WHERE telefone = ?");
            $stmt->execute([$telefone]);
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                // Cliente existe, fazer login
                $_SESSION['cliente_logado'] = true;
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_telefone'] = $cliente['telefone'];
                
                header('Location: /');
                exit;
            } else {
                // Cliente não existe, criar novo
                $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, created_at) VALUES ('Cliente', 'cliente@email.com', ?, NOW())");
                $stmt->execute([$telefone]);
                
                $id = $pdo->lastInsertId();
                
                $_SESSION['cliente_logado'] = true;
                $_SESSION['cliente_id'] = $id;
                $_SESSION['cliente_telefone'] = $telefone;
                
                header('Location: /');
                exit;
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao processar login: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="bi bi-person-circle"></i>
                <h2 class="mt-3">Login</h2>
                <p class="text-muted">Digite seu telefone para continuar</p>
            </div>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control" id="telefone" name="telefone" 
                               placeholder="(00) 00000-0000" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="/" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Voltar para a página inicial
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#telefone').mask('(00) 00000-0000');
        });
    </script>
</body>
</html> 