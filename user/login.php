<?php
require_once '../config/database.php';
session_start();

// Se o usuário já estiver logado, redireciona para a página inicial
if (isset($_SESSION['cliente_logado'])) {
    header('Location: carrinho');
    exit;
}

$erro = '';

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $erro = 'Token de segurança inválido';
    } else {
        $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        // Validação básica
        if (empty($telefone) || strlen($telefone) < 10) {
            $erro = 'Telefone inválido';
        } else {
            try {
                $pdo = getConnection();
                
                // Verifica se o cliente já existe
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE telefone = ?");
                $stmt->execute([$telefone]);
                $cliente = $stmt->fetch();

                if ($cliente) {
                    // Cliente existe, faz login
                    $_SESSION['cliente_logado'] = true;
                    $_SESSION['cliente_id'] = $cliente['id'];
                    $_SESSION['cliente_nome'] = $cliente['nome'];
                    $_SESSION['cliente_telefone'] = $cliente['telefone'];
                } else {
                    // Validação do nome para novo usuário
                    if (empty($nome) || strlen($nome) < 3) {
                        $erro = 'Nome inválido';
                    } else {
                        // Cliente novo, cadastra
                        $stmt = $pdo->prepare("
                            INSERT INTO clientes (nome, telefone, email) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$nome, $telefone, empty($email) ? null : $email]);

                        $_SESSION['cliente_logado'] = true;
                        $_SESSION['cliente_id'] = $pdo->lastInsertId();
                        $_SESSION['cliente_nome'] = $nome;
                        $_SESSION['cliente_telefone'] = $telefone;
                    }
                }

                if (empty($erro)) {
                    header('Location: carrinho');
                    exit;
                }
            } catch (PDOException $e) {
                $erro = "Erro ao processar login/cadastro: " . $e->getMessage();
            }
        }
    }
}

// Gera token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Entrar / Cadastrar</h3>
                        
                        <?php if (!empty($erro)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
                        <?php endif; ?>

                        <form method="POST" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" 
                                       required placeholder="(00) 00000-0000">
                            </div>
                            
                            <div class="mb-3" id="nomeField" style="display: none;">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       placeholder="Seu nome completo">
                            </div>

                            <div class="mb-3" id="emailField" style="display: none;">
                                <label for="email" class="form-label">Email (opcional)</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Seu email (opcional)">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Continuar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('telefone').addEventListener('blur', async function() {
            const telefone = this.value.replace(/\D/g, '');
            if (telefone.length >= 10) {
                // Verifica se o telefone já existe
                const response = await fetch(`verificar_telefone.php?telefone=${telefone}`);
                const data = await response.json();
                
                const nomeField = document.getElementById('nomeField');
                const nomeInput = document.getElementById('nome');
                const emailField = document.getElementById('emailField');
                const emailInput = document.getElementById('email');
                
                if (!data.exists) {
                    // Novo usuário, mostra campos de nome e email
                    nomeField.style.display = 'block';
                    nomeInput.required = true;
                    emailField.style.display = 'block';
                    emailInput.required = false;
                } else {
                    // Usuário existente, esconde campos de nome e email
                    nomeField.style.display = 'none';
                    nomeInput.required = false;
                    emailField.style.display = 'none';
                    emailInput.required = false;
                }
            }
        });

        // Máscara para o telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                e.target.value = value;
            }
        });
    </script>
</body>
</html> 