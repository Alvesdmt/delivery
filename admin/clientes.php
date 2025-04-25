<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --header-height: 60px;
            --sidebar-width: 250px;
        }

        .main-content {
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
            background-color: #f5f6fa;
            margin-left: var(--sidebar-width);
            margin-top: calc(var(--header-height) + 20px);
        }

        .page-header {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            font-size: 24px;
            color: var(--primary-color);
            margin: 0;
        }

        .search-box {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: white;
            font-size: 14px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background-color: white;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .action-buttons .btn {
            padding: 5px 10px;
            margin: 0 2px;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                margin-left: 0;
                margin-top: var(--header-height);
            }

            .table-responsive {
                margin: 0 -15px;
            }
            
            .btn-primary {
                width: 100%;
                margin-bottom: 15px;
            }

            .table td, .table th {
                padding: 8px;
                font-size: 14px;
            }

            .action-buttons {
                display: flex;
                gap: 5px;
            }

            .page-header {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }

            .page-header .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="search-box">
            <input type="text" placeholder="Pesquisar clientes..." class="form-control">
        </div>

        <div class="page-header">
            <h2>Clientes</h2>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#clienteModal">
                <i class="bi bi-person-plus-fill"></i>
                <span>Novo Cliente</span>
            </button>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Endereço</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM clientes ORDER BY id DESC";
                        $result = $conn->query($sql);
                        
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['nome'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['telefone'] . "</td>";
                                echo "<td>" . $row['endereco'] . "</td>";
                                echo "<td>" . date('d/m/Y', strtotime($row['data_cadastro'])) . "</td>";
                                echo "<td class='action-buttons'>";
                                echo "<button class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></button>";
                                echo "<button class='btn btn-sm btn-danger'><i class='bi bi-trash'></i></button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Nenhum cliente cadastrado</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro -->
    <div class="modal fade" id="clienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Novo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="endereco" class="form-label">Endereço</label>
                            <textarea class="form-control" id="endereco" name="endereco" rows="3" required></textarea>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Cadastrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 