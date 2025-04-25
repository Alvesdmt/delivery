<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login');
    exit();
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se é edição ou cadastro
    if (isset($_POST['edit_product'])) {
        // Processar edição
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $descricao = !empty($_POST['descricao']) ? $_POST['descricao'] : null;
        $preco = $_POST['preco'];
        $imagem = isset($_FILES['imagem']) ? $_FILES['imagem'] : null;
        
        try {
            $pdo = getConnection();
            
            // Buscar produto atual para manter a imagem se não for alterada
            $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
            $produto_atual = $stmt->fetch();
            $relative_path = $produto_atual['imagem'];
            
            // Upload da nova imagem se fornecida
            if ($imagem && $imagem['size'] > 0) {
                $target_dir = __DIR__ . "/uploads/";
                
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $target_file = $target_dir . time() . '_' . basename($imagem["name"]);
                $relative_path = "uploads/" . time() . '_' . basename($imagem["name"]);
                
                if (!getimagesize($imagem["tmp_name"])) {
                    $erro = "O arquivo selecionado não é uma imagem válida.";
                }
                else if ($imagem["size"] > 5000000) {
                    $erro = "O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
                }
                else if (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), ["jpg", "jpeg", "png", "gif"])) {
                    $erro = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
                }
                else if (!move_uploaded_file($imagem["tmp_name"], $target_file)) {
                    $erro = "Erro ao fazer upload da imagem. Verifique as permissões do diretório.";
                }
            }
            
            if (!isset($erro)) {
                $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, imagem = ? WHERE id = ?");
                $stmt->execute([$nome, $descricao, $preco, $relative_path, $id]);
                
                header('Location: produtos.php?success=3');
                exit();
            }
        } catch(PDOException $e) {
            $erro = "Erro ao atualizar o produto: " . $e->getMessage();
        }
    } else {
        // Processar cadastro
        $nome = $_POST['nome'];
        $descricao = !empty($_POST['descricao']) ? $_POST['descricao'] : null;
        $preco = $_POST['preco'];
        $imagem = isset($_FILES['imagem']) ? $_FILES['imagem'] : null;
        
        $relative_path = null;
        
        // Upload da imagem se fornecida
        if ($imagem && $imagem['size'] > 0) {
            $target_dir = __DIR__ . "/uploads/";
            
            // Criar diretório se não existir
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . time() . '_' . basename($imagem["name"]);
            $relative_path = "uploads/" . time() . '_' . basename($imagem["name"]);
            
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
        }
        
        if (!isset($erro)) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $descricao, $preco, $relative_path]);
                
                header('Location: produtos.php?success=1');
                exit();
            } catch(PDOException $e) {
                $erro = "Erro ao cadastrar o produto: " . $e->getMessage();
            }
        }
    }
}

// Processar exclusão de produto
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: produtos.php?success=2');
        exit();
    } catch(PDOException $e) {
        $erro = "Erro ao excluir o produto: " . $e->getMessage();
    }
}

// Buscar produtos cadastrados
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
    $produtos = $stmt->fetchAll();
} catch(PDOException $e) {
    $erro = "Erro ao buscar produtos: " . $e->getMessage();
}

require_once 'includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos</title>
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
            margin-bottom: 30px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            margin-top: 10px;
            border-radius: 4px;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-image {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Estilos do Modal */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-title {
            color: #2c3e50;
            font-weight: 600;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
        }

        .current-image-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .current-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .image-preview-container {
            margin-top: 1rem;
            text-align: center;
        }

        .edit-image-preview {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            background-color: #3498db;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .btn-close {
            opacity: 0.5;
        }

        .btn-close:hover {
            opacity: 0.75;
        }

        @media (max-width: 576px) {
            .modal-dialog {
                margin: 1rem;
            }
            
            .modal-content {
                border-radius: 8px;
            }
            
            .current-image {
                max-width: 100px;
                max-height: 100px;
            }
            
            .edit-image-preview {
                max-width: 100px;
                max-height: 100px;
            }
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

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                if ($_GET['success'] == 1) {
                                    echo "Produto cadastrado com sucesso!";
                                } elseif ($_GET['success'] == 2) {
                                    echo "Produto excluído com sucesso!";
                                } elseif ($_GET['success'] == 3) {
                                    echo "Produto atualizado com sucesso!";
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço</label>
                                <input type="number" class="form-control" id="preco" name="preco" step="0.01" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem do Produto (Opcional)</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*" onchange="previewImage(this)">
                                <img id="imagePreview" class="preview-image d-none">
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Cadastrar Produto
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <h3 class="mb-4">Produtos Cadastrados</h3>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Preço</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($produtos) && count($produtos) > 0): ?>
                                        <?php foreach ($produtos as $produto): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($produto['imagem']): ?>
                                                        <img src="<?php echo $produto['imagem']; ?>" class="product-image" alt="<?php echo $produto['nome']; ?>">
                                                    <?php else: ?>
                                                        <i class="bi bi-image text-muted"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($produto['descricao'] ?? ''); ?></td>
                                                <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                                            data-descricao="<?php echo htmlspecialchars($produto['descricao'] ?? ''); ?>"
                                                            data-preco="<?php echo $produto['preco']; ?>"
                                                            data-imagem="<?php echo $produto['imagem']; ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <a href="produtos.php?delete=<?php echo $produto['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhum produto cadastrado</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="edit_product" value="1">
                        
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_preco" class="form-label">Preço</label>
                            <input type="number" class="form-control" id="edit_preco" name="preco" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_imagem" class="form-label">Imagem do Produto (Opcional)</label>
                            <div id="current_image_container" class="current-image-container">
                                <p class="mb-2">Imagem atual:</p>
                                <img id="current_image" class="current-image" alt="Imagem atual">
                            </div>
                            <input type="file" class="form-control" id="edit_imagem" name="imagem" accept="image/*" onchange="previewEditImage(this)">
                            <div class="image-preview-container">
                                <img id="edit_imagePreview" class="edit-image-preview" alt="Prévia da nova imagem">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
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

        function previewEditImage(input) {
            const preview = document.getElementById('edit_imagePreview');
            preview.style.display = 'block';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Inicializar o modal de edição
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const nome = button.getAttribute('data-nome');
                    const descricao = button.getAttribute('data-descricao');
                    const preco = button.getAttribute('data-preco');
                    const imagem = button.getAttribute('data-imagem');

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_nome').value = nome;
                    document.getElementById('edit_descricao').value = descricao;
                    document.getElementById('edit_preco').value = preco;

                    const currentImage = document.getElementById('current_image');
                    const currentImageContainer = document.getElementById('current_image_container');
                    const editImagePreview = document.getElementById('edit_imagePreview');
                    
                    if (imagem) {
                        currentImage.src = imagem;
                        currentImageContainer.style.display = 'block';
                    } else {
                        currentImageContainer.style.display = 'none';
                    }
                    
                    editImagePreview.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html> 