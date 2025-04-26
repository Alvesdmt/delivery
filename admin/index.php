<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login');
    exit();
}

require_once 'includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../assets/js/notifications.js"></script>
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
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
        }

        .card h3 {
            color: var(--primary-color);
            font-size: 18px;
            margin-bottom: 20px;
        }

        /* Estilos para as estatísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-item i {
            font-size: 24px;
            color: var(--accent-color);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        /* Estilos para atividades recentes */
        .activities-list {
            margin-top: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #f0f2f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
        }

        .activity-details p {
            margin: 0;
            font-size: 14px;
            color: #333;
        }

        .activity-details small {
            color: #666;
            font-size: 12px;
        }

        /* Estilos para tarefas */
        .tasks-list {
            margin-top: 15px;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .task-item label {
            font-size: 14px;
            color: #333;
            margin: 0;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                margin-left: 0;
                margin-top: var(--header-height);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-header {
                padding: 15px;
            }

            .card {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h2>Dashboard</h2>
        </div>

        <div class="card">
            <h3>Estatísticas</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="stat-info">
                        <span class="stat-value">150</span>
                        <span class="stat-label">Pedidos</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <span class="stat-value">1.2k</span>
                        <span class="stat-label">Clientes</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-box"></i>
                    <div class="stat-info">
                        <span class="stat-value">324</span>
                        <span class="stat-label">Produtos</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="stat-info">
                        <span class="stat-value">R$ 45k</span>
                        <span class="stat-label">Receita</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h3>Atividades Recentes</h3>
                    <div class="activities-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="activity-details">
                                <p>Novo pedido #12345</p>
                                <small>Há 5 minutos</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-details">
                                <p>Novo cliente cadastrado</p>
                                <small>Há 15 minutos</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="activity-details">
                                <p>Pedido #12344 enviado</p>
                                <small>Há 30 minutos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <h3>Tarefas</h3>
                    <div class="tasks-list">
                        <div class="task-item">
                            <input type="checkbox" id="task1">
                            <label for="task1">Revisar novos pedidos</label>
                        </div>
                        <div class="task-item">
                            <input type="checkbox" id="task2">
                            <label for="task2">Atualizar estoque</label>
                        </div>
                        <div class="task-item">
                            <input type="checkbox" id="task3">
                            <label for="task3">Responder mensagens</label>
                        </div>
                        <div class="task-item">
                            <input type="checkbox" id="task4">
                            <label for="task4">Preparar relatório mensal</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>