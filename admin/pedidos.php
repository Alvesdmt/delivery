<?php
require_once '../config/database.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login');
    exit();
}

// Buscar pedidos cadastrados
try {
    $pdo = getConnection();
    
    // Buscar contagem de pedidos por status
    $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'hoje';
    $sql_contagem = "SELECT 
        COUNT(CASE WHEN status = 'pendente' AND ";
    
    switch($periodo) {
        case 'hoje':
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
        case 'ontem':
            $sql_contagem .= "DATE(data) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'semana':
            $sql_contagem .= "YEARWEEK(data) = YEARWEEK(CURDATE())";
            break;
        case 'mes':
            $sql_contagem .= "MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
            break;
        default:
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
    }
    
    $sql_contagem .= " THEN 1 END) as total_pendentes,
        COUNT(CASE WHEN status = 'processando' AND ";
    
    switch($periodo) {
        case 'hoje':
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
        case 'ontem':
            $sql_contagem .= "DATE(data) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'semana':
            $sql_contagem .= "YEARWEEK(data) = YEARWEEK(CURDATE())";
            break;
        case 'mes':
            $sql_contagem .= "MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
            break;
        default:
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
    }
    
    $sql_contagem .= " THEN 1 END) as total_processando,
        COUNT(CASE WHEN status = 'enviado' AND ";
    
    switch($periodo) {
        case 'hoje':
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
        case 'ontem':
            $sql_contagem .= "DATE(data) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'semana':
            $sql_contagem .= "YEARWEEK(data) = YEARWEEK(CURDATE())";
            break;
        case 'mes':
            $sql_contagem .= "MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
            break;
        default:
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
    }
    
    $sql_contagem .= " THEN 1 END) as total_enviados,
        COUNT(CASE WHEN status = 'entregue' AND ";
    
    switch($periodo) {
        case 'hoje':
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
        case 'ontem':
            $sql_contagem .= "DATE(data) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'semana':
            $sql_contagem .= "YEARWEEK(data) = YEARWEEK(CURDATE())";
            break;
        case 'mes':
            $sql_contagem .= "MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
            break;
        default:
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
    }
    
    $sql_contagem .= " THEN 1 END) as total_entregues,
        COUNT(CASE WHEN ";
    
    switch($periodo) {
        case 'hoje':
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
        case 'ontem':
            $sql_contagem .= "DATE(data) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'semana':
            $sql_contagem .= "YEARWEEK(data) = YEARWEEK(CURDATE())";
            break;
        case 'mes':
            $sql_contagem .= "MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
            break;
        default:
            $sql_contagem .= "DATE(data) = CURDATE()";
            break;
    }
    
    $sql_contagem .= " THEN 1 END) as total_hoje
    FROM pedidos";
    
    $stmt = $pdo->query($sql_contagem);
    $contagem = $stmt->fetch();

    // Buscar pedidos com filtro
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'hoje';
    $sql = "SELECT p.*, c.nome as cliente_nome, c.telefone 
            FROM pedidos p 
            LEFT JOIN clientes c ON p.cliente_id = c.id";
    
    switch($filtro) {
        case 'pendente':
            $sql .= " WHERE p.status = 'pendente'";
            break;
        case 'processando':
            $sql .= " WHERE p.status = 'processando'";
            break;
        case 'enviado':
            $sql .= " WHERE p.status = 'enviado'";
            break;
        case 'entregue':
            $sql .= " WHERE p.status = 'entregue'";
            break;
        case 'hoje':
        default:
            switch($periodo) {
                case 'hoje':
                    $sql .= " WHERE DATE(p.data) = CURDATE()";
                    break;
                case 'ontem':
                    $sql .= " WHERE DATE(p.data) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                    break;
                case 'semana':
                    $sql .= " WHERE YEARWEEK(p.data) = YEARWEEK(CURDATE())";
                    break;
                case 'mes':
                    $sql .= " WHERE MONTH(p.data) = MONTH(CURDATE()) AND YEAR(p.data) = YEAR(CURDATE())";
                    break;
                default:
                    $sql .= " WHERE DATE(p.data) = CURDATE()";
                    break;
            }
            break;
    }
    
    $sql .= " ORDER BY p.data DESC";
    $stmt = $pdo->query($sql);
    $pedidos = $stmt->fetchAll();
} catch(PDOException $e) {
    $erro = "Erro ao buscar pedidos: " . $e->getMessage();
}

require_once 'includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #7209b7;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            padding: 20px;
            margin-top: 80px;
        }

        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }

        .dashboard-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0;
        }

        .dashboard-subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            cursor: pointer;
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            transition: var(--transition);
        }

        .dashboard-card[href*="hoje"]::before {
            background-color: var(--primary-color);
        }

        .dashboard-card[href*="pendente"]::before {
            background-color: var(--warning-color);
        }

        .dashboard-card[href*="processando"]::before {
            background-color: var(--info-color);
        }

        .dashboard-card[href*="enviado"]::before {
            background-color: var(--success-color);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card:hover::before {
            width: 8px;
        }

        .card-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .card-title {
            font-size: 1rem;
            color: #666;
            margin-bottom: 5px;
        }

        .card-subtitle {
            font-size: 0.8rem;
            color: #999;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-top: 30px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #eee;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pendente {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
        }

        .status-processando {
            background-color: rgba(114, 9, 183, 0.1);
            color: var(--info-color);
        }

        .status-enviado {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }

        .status-entregue {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .action-buttons .btn {
            padding: 0.35rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background-color: var(--light-bg);
            border-radius: 15px 15px 0 0;
        }

        .form-select {
            border-radius: 8px;
            border: 1px solid #eee;
            padding: 0.5rem 1rem;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="dashboard-title">Dashboard de Pedidos</h2>
                        <p class="dashboard-subtitle">Gerencie e acompanhe todos os pedidos do sistema</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 text-muted">Atualizado em: <?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                </div>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>

            <!-- Cards do Dashboard -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <div class="btn-group" role="group">
                        <a href="?filtro=hoje&periodo=hoje" class="btn btn-outline-primary <?php echo ($periodo == 'hoje') ? 'active' : ''; ?>">Hoje</a>
                        <a href="?filtro=hoje&periodo=ontem" class="btn btn-outline-primary <?php echo ($periodo == 'ontem') ? 'active' : ''; ?>">Ontem</a>
                        <a href="?filtro=hoje&periodo=semana" class="btn btn-outline-primary <?php echo ($periodo == 'semana') ? 'active' : ''; ?>">Esta Semana</a>
                        <a href="?filtro=hoje&periodo=mes" class="btn btn-outline-primary <?php echo ($periodo == 'mes') ? 'active' : ''; ?>">Este Mês</a>
                    </div>
                </div>
                <div class="col">
                    <a href="?filtro=hoje&periodo=<?php echo $periodo; ?>" class="text-decoration-none">
                        <div class="dashboard-card">
                            <div class="card-number" id="total-hoje"><?php echo $contagem['total_hoje']; ?></div>
                            <div class="card-title">Pedidos <?php 
                                switch($periodo) {
                                    case 'hoje': echo 'Hoje'; break;
                                    case 'ontem': echo 'Ontem'; break;
                                    case 'semana': echo 'Esta Semana'; break;
                                    case 'mes': echo 'Este Mês'; break;
                                }
                            ?></div>
                            <div class="card-subtitle">Total de pedidos do período</div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="?filtro=pendente&periodo=<?php echo $periodo; ?>" class="text-decoration-none">
                        <div class="dashboard-card">
                            <div class="card-number" id="total-pendentes"><?php echo $contagem['total_pendentes']; ?></div>
                            <div class="card-title">Pedidos Pendentes</div>
                            <div class="card-subtitle">Aguardando processamento</div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="?filtro=processando&periodo=<?php echo $periodo; ?>" class="text-decoration-none">
                        <div class="dashboard-card">
                            <div class="card-number" id="total-processando"><?php echo $contagem['total_processando']; ?></div>
                            <div class="card-title">Em Processamento</div>
                            <div class="card-subtitle">Em preparação</div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="?filtro=enviado&periodo=<?php echo $periodo; ?>" class="text-decoration-none">
                        <div class="dashboard-card">
                            <div class="card-number" id="total-enviados"><?php echo $contagem['total_enviados']; ?></div>
                            <div class="card-title">Pedidos Enviados</div>
                            <div class="card-subtitle">Em rota de entrega</div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="?filtro=entregue&periodo=<?php echo $periodo; ?>" class="text-decoration-none">
                        <div class="dashboard-card">
                            <div class="card-number" id="total-entregues"><?php echo $contagem['total_entregues']; ?></div>
                            <div class="card-title">Pedidos Entregues</div>
                            <div class="card-subtitle">Concluídos com sucesso</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Tabela de Pedidos -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Telefone</th>
                                <th>Data</th>
                                <th>Total</th>
                                <th>Forma de Pagamento</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo $pedido['id']; ?></td>
                                    <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['telefone']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data'])); ?></td>
                                    <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                    <td><?php echo ucfirst($pedido['forma_pagamento']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($pedido['status']); ?>">
                                            <?php echo ucfirst($pedido['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="detalhes_pedido.php?id=<?php echo $pedido['id']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-success" 
                                                title="Atualizar status"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#statusModal"
                                                data-pedido-id="<?php echo $pedido['id']; ?>">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                title="Imprimir Nota Fiscal"
                                                data-bs-toggle="modal"
                                                data-bs-target="#imprimirModal"
                                                data-pedido-id="<?php echo $pedido['id']; ?>">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Atualização de Status -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atualizar Status do Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm" method="POST">
                        <input type="hidden" name="pedido_id" id="pedido_id">
                        <div class="mb-3">
                            <label for="novo_status" class="form-label">Novo Status</label>
                            <select class="form-select" id="novo_status" name="novo_status" required>
                                <option value="pendente">Pendente</option>
                                <option value="processando">Processando</option>
                                <option value="enviado">Enviado</option>
                                <option value="entregue">Entregue</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="salvarStatus">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Impressão -->
    <div class="modal fade" id="imprimirModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imprimir Nota Fiscal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="imprimirForm" method="POST">
                        <input type="hidden" name="pedido_id" id="pedido_id_impressao">
                        <div class="mb-3">
                            <label for="tipo_impressao" class="form-label">Tipo de Impressão</label>
                            <select class="form-select" id="tipo_impressao" name="tipo_impressao" required>
                                <option value="nota_fiscal">Nota Fiscal</option>
                                <option value="comprovante">Comprovante de Pedido</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="impressora" class="form-label">Impressora</label>
                            <select class="form-select" id="impressora" name="impressora" required>
                                <option value="fiscal">Impressora Fiscal</option>
                                <option value="termica">Impressora Térmica</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="downloadNota">Download PDF</button>
                    <button type="button" class="btn btn-primary" id="imprimirNota">Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuração do WebSocket
        const ws = new WebSocket('ws://localhost:8080');
        
        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (data.type === 'pedido_atualizado') {
                atualizarDashboard();
            }
        };

        // Função para atualizar os dados do dashboard
        function atualizarDashboard() {
            fetch('atualizar_dashboard.php')
                .then(response => response.json())
                .then(data => {
                    // Atualizar os cards
                    document.getElementById('total-hoje').textContent = data.total_hoje;
                    document.getElementById('total-pendentes').textContent = data.total_pendentes;
                    document.getElementById('total-processando').textContent = data.total_processando;
                    document.getElementById('total-enviados').textContent = data.total_enviados;
                    document.getElementById('total-entregues').textContent = data.total_entregues;
                    
                    // Atualizar a data/hora da última atualização
                    document.querySelector('.text-muted').textContent = 'Atualizado em: ' + new Date().toLocaleString('pt-BR');

                    // Atualizar a tabela de pedidos
                    if (data.pedidos) {
                        const tbody = document.querySelector('table tbody');
                        tbody.innerHTML = '';
                        
                        data.pedidos.forEach(pedido => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>#${pedido.id}</td>
                                <td>${pedido.cliente_nome}</td>
                                <td>${pedido.telefone}</td>
                                <td>${new Date(pedido.data).toLocaleString('pt-BR')}</td>
                                <td>R$ ${pedido.total.toFixed(2).replace('.', ',')}</td>
                                <td>${pedido.forma_pagamento.charAt(0).toUpperCase() + pedido.forma_pagamento.slice(1)}</td>
                                <td>
                                    <span class="status-badge status-${pedido.status.toLowerCase()}">
                                        ${pedido.status.charAt(0).toUpperCase() + pedido.status.slice(1)}
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="detalhes_pedido.php?id=${pedido.id}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Ver detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-success" 
                                            title="Atualizar status"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#statusModal"
                                            data-pedido-id="${pedido.id}">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-info"
                                            title="Imprimir Nota Fiscal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#imprimirModal"
                                            data-pedido-id="${pedido.id}">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                })
                .catch(error => console.error('Erro ao atualizar dashboard:', error));
        }

        // Atualizar a cada 30 segundos como fallback
        setInterval(atualizarDashboard, 30000);

        // Inicializar o modal de status
        const statusModal = document.getElementById('statusModal');
        if (statusModal) {
            statusModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const pedidoId = button.getAttribute('data-pedido-id');
                const modalInput = statusModal.querySelector('#pedido_id');
                modalInput.value = pedidoId;
            });
        }

        // Salvar novo status
        document.getElementById('salvarStatus').addEventListener('click', function() {
            const form = document.getElementById('statusForm');
            const formData = new FormData(form);
            
            fetch('atualizar_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar o dashboard após mudança de status
                    atualizarDashboard();
                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(statusModal);
                    modal.hide();
                } else {
                    alert('Erro ao atualizar status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar status');
            });
        });

        // Inicializar o modal de impressão
        const imprimirModal = document.getElementById('imprimirModal');
        if (imprimirModal) {
            imprimirModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const pedidoId = button.getAttribute('data-pedido-id');
                const modalInput = imprimirModal.querySelector('#pedido_id_impressao');
                modalInput.value = pedidoId;
            });
        }

        // Imprimir nota fiscal
        document.getElementById('imprimirNota').addEventListener('click', function() {
            const form = document.getElementById('imprimirForm');
            const formData = new FormData(form);
            formData.append('acao', 'imprimir');
            
            fetch('imprimir_nota.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Nota fiscal enviada para impressão com sucesso!');
                    const modal = bootstrap.Modal.getInstance(imprimirModal);
                    modal.hide();
                } else {
                    alert('Erro ao imprimir nota fiscal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao imprimir nota fiscal');
            });
        });

        // Download da nota fiscal
        document.getElementById('downloadNota').addEventListener('click', function() {
            const form = document.getElementById('imprimirForm');
            const formData = new FormData(form);
            formData.append('acao', 'download');
            
            fetch('imprimir_nota.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    return response.blob();
                }
                throw new Error('Erro ao baixar nota fiscal');
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'nota_fiscal.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao baixar nota fiscal');
            });
        });
    </script>
</body>
</html>