<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_cadastro.php');
    exit();
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $imagem = $_FILES['imagem'];
    
    // Upload da imagem
    $target_dir = __DIR__ . "/uploads/"; // Caminho absoluto para o diretório de uploads
    
    // Criar diretório se não existir
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . time() . '_' . basename($imagem["name"]);
    $relative_path = "uploads/" . time() . '_' . basename($imagem["name"]); // Caminho relativo para o banco de dados
    
    // Verificar se o arquivo é uma imagem
    if (!getimagesize($imagem["tmp_name"])) {
        $erro = "O arquivo selecionado não é uma imagem válida.";
    }
    // Verificar tamanho do arquivo (máximo 5MB)
    else if ($imagem["size"] > 5000000) {
        $erro = "O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
    }
    // Verificar extensão do arquivo
    else if (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), ["jpg", "jpeg", "png", "gif"])) {
        $erro = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
    }
    // Tentar fazer o upload
    else if (!move_uploaded_file($imagem["tmp_name"], $target_file)) {
        $erro = "Erro ao fazer upload da imagem. Verifique as permissões do diretório.";
    }
    else {
        // Verificar conexão com o banco de dados
        if ($conn->connect_error) {
            $erro = "Erro na conexão com o banco de dados: " . $conn->connect_error;
        } else {
            // Inserir no banco de dados
            $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                $erro = "Erro ao preparar a query: " . $conn->error;
            } else {
                $stmt->bind_param("ssds", $nome, $descricao, $preco, $relative_path);
                
                if ($stmt->execute()) {
                    header('Location: produtos.php?success=1');
                    exit();
                } else {
                    $erro = "Erro ao cadastrar o produto: " . $stmt->error;
                }
            }
        }
    }
}

require_once 'includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .main-content {
            padding: 20px;
            margin-top: 80px;
        }
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            margin-top: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="form-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Cadastrar Novo Produto</h2>
                            <a href="produtos.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>

                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?php echo $erro; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço</label>
                                <input type="number" class="form-control" id="preco" name="preco" step="0.01" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem do Produto</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*" required onchange="previewImage(this)">
                                <img id="imagePreview" class="preview-image d-none">
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Cadastrar Produto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.classList.remove('d-none');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html> 