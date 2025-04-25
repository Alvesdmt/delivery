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
    <title>Relatórios - Painel Administrativo</title>
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
        }

        .page-header h2 {
            font-size: 24px;
            color: var(--primary-color);
            margin: 0;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background-color: white;
            padding: 20px;
        }

        .report-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .report-card:hover {
            transform: translateY(-5px);
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .report-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .report-info h4 {
            margin: 0;
            color: var(--primary-color);
            font-size: 18px;
        }

        .report-info p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-item {
            flex: 1;
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

            .filters {
                flex-direction: column;
            }

            .page-header {
                padding: 15px;
            }

            .report-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h2>Relatórios</h2>
        </div>

        <div class="filters">
            <div class="filter-item">
                <select class="form-select">
                    <option value="">Período</option>
                    <option value="hoje">Hoje</option>
                    <option value="semana">Última Semana</option>
                    <option value="mes">Último Mês</option>
                    <option value="ano">Último Ano</option>
                </select>
            </div>
            <div class="filter-item">
                <input type="date" class="form-control" placeholder="Data Inicial">
            </div>
            <div class="filter-item">
                <input type="date" class="form-control" placeholder="Data Final">
            </div>
            <div class="filter-item">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>
                    Filtrar
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="report-info">
                        <h4>Vendas</h4>
                        <p>Relatório detalhado de vendas</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="report-info">
                        <h4>Clientes</h4>
                        <p>Análise de clientes e comportamento</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-box"></i>
                    </div>
                    <div class="report-info">
                        <h4>Produtos</h4>
                        <p>Desempenho e estoque de produtos</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="report-info">
                        <h4>Financeiro</h4>
                        <p>Relatório financeiro completo</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-star"></i>
                    </div>
                    <div class="report-info">
                        <h4>Avaliações</h4>
                        <p>Feedback e satisfação dos clientes</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="report-info">
                        <h4>Entregas</h4>
                        <p>Desempenho das entregas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 