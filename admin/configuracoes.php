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
    <title>Configurações - Painel Administrativo</title>
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

        .card h3 {
            color: var(--primary-color);
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .config-section {
            margin-bottom: 30px;
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-text {
            color: #666;
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

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--accent-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                margin-left: 0;
                margin-top: var(--header-height);
            }

            .page-header {
                padding: 15px;
            }

            .card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h2>Configurações</h2>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h3>Configurações Gerais</h3>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Nome do Estabelecimento</label>
                            <input type="text" class="form-control" value="Meu Delivery">
                            <div class="form-text">Este nome será exibido em todo o sistema</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email de Contato</label>
                            <input type="email" class="form-control" value="contato@meudelivery.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="tel" class="form-control" value="(11) 99999-9999">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Endereço</label>
                            <textarea class="form-control" rows="3">Rua Exemplo, 123 - Bairro - Cidade/UF</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <h3>Configurações de Entrega</h3>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Taxa de Entrega</label>
                            <input type="number" class="form-control" value="5.00" step="0.01">
                            <div class="form-text">Valor base para entregas</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Raio de Entrega (km)</label>
                            <input type="number" class="form-control" value="10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tempo Médio de Entrega (min)</label>
                            <input type="number" class="form-control" value="45">
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Entrega Grátis</label>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                            <div class="form-text">Ativar entrega grátis acima de um valor</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Valor Mínimo para Entrega Grátis</label>
                            <input type="number" class="form-control" value="50.00" step="0.01">
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Horário de Funcionamento</h3>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Horário de Abertura</label>
                            <input type="time" class="form-control" value="09:00">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Horário de Fechamento</label>
                            <input type="time" class="form-control" value="23:00">
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Status do Estabelecimento</label>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                            <div class="form-text">Ativar/Desativar o recebimento de pedidos</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 