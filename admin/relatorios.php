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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportCards = document.querySelectorAll('.report-card');
            const dataInicial = document.querySelector('input[placeholder="Data Inicial"]');
            const dataFinal = document.querySelector('input[placeholder="Data Final"]');
            const periodoSelect = document.querySelector('select.form-select');
            const btnFiltrar = document.querySelector('.btn-primary');

            // Função para atualizar datas baseado no período selecionado
            function atualizarDatasPorPeriodo() {
                const hoje = new Date();
                const dataFinal = new Date();
                let dataInicial = new Date();

                switch(periodoSelect.value) {
                    case 'hoje':
                        dataInicial.setHours(0, 0, 0, 0);
                        break;
                    case 'semana':
                        dataInicial.setDate(dataInicial.getDate() - 7);
                        break;
                    case 'mes':
                        dataInicial.setMonth(dataInicial.getMonth() - 1);
                        break;
                    case 'ano':
                        dataInicial.setFullYear(dataInicial.getFullYear() - 1);
                        break;
                }

                document.querySelector('input[placeholder="Data Inicial"]').value = dataInicial.toISOString().split('T')[0];
                document.querySelector('input[placeholder="Data Final"]').value = dataFinal.toISOString().split('T')[0];
            }

            // Evento para mudança de período
            periodoSelect.addEventListener('change', atualizarDatasPorPeriodo);

            // Função para carregar relatório
            function carregarRelatorio(tipo) {
                const formData = new FormData();
                formData.append('tipo_relatorio', tipo);
                formData.append('data_inicial', dataInicial.value);
                formData.append('data_final', dataFinal.value);

                fetch('processar_relatorios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    exibirRelatorio(tipo, data);
                })
                .catch(error => {
                    console.error('Erro ao carregar relatório:', error);
                    alert('Erro ao carregar relatório. Por favor, tente novamente.');
                });
            }

            // Função para exibir relatório
            function exibirRelatorio(tipo, data) {
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'modalRelatorio';
                modal.innerHTML = `
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Relatório de ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                ${gerarCabecalhoTabela(tipo)}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${gerarCorpoTabela(tipo, data)}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    <canvas id="graficoRelatorio"></canvas>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="button" class="btn btn-primary" onclick="exportarRelatorio('${tipo}')">Exportar</button>
                            </div>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();

                // Remover modal do DOM após fechar
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });

                // Gerar gráfico
                gerarGrafico(tipo, data);
            }

            // Função para gerar cabeçalho da tabela
            function gerarCabecalhoTabela(tipo) {
                const cabecalhos = {
                    vendas: ['Data', 'Total de Pedidos', 'Valor Total', 'Valor Médio'],
                    clientes: ['Nome', 'Email', 'Telefone', 'Total de Pedidos', 'Valor Total Gasto'],
                    produtos: ['Nome', 'Preço', 'Quantidade Vendida', 'Valor Total'],
                    financeiro: ['Data', 'Receita Total', 'Valor Cancelamentos', 'Total Cancelamentos', 'Total Pedidos'],
                    avaliacoes: ['Nota', 'Comentário', 'Cliente', 'Data', 'Total Avaliações', 'Média Geral'],
                    entregas: ['ID Pedido', 'Data', 'Status', 'Tempo Entrega', 'Cliente', 'Endereço', 'Total Entregas', 'Tempo Médio']
                };

                return cabecalhos[tipo].map(coluna => `<th>${coluna}</th>`).join('');
            }

            // Função para gerar corpo da tabela
            function gerarCorpoTabela(tipo, data) {
                return data.map(item => {
                    const linhas = {
                        vendas: [item.data, item.total_pedidos, item.valor_total, item.valor_medio],
                        clientes: [item.nome, item.email, item.telefone, item.total_pedidos, item.valor_total_gasto],
                        produtos: [item.nome, item.preco, item.quantidade_vendida, item.valor_total],
                        financeiro: [item.data, item.receita_total, item.valor_cancelamentos, item.total_cancelamentos, item.total_pedidos],
                        avaliacoes: [item.nota, item.comentario, item.cliente_nome, item.data_pedido, item.total_avaliacoes, item.media_geral],
                        entregas: [item.pedido_id, item.data_pedido, item.status, item.tempo_entrega, item.cliente_nome, item.endereco, item.total_entregas, item.tempo_medio_entrega]
                    };

                    return `<tr>${linhas[tipo].map(celula => `<td>${celula}</td>`).join('')}</tr>`;
                }).join('');
            }

            // Função para gerar gráfico
            function gerarGrafico(tipo, data) {
                const ctx = document.getElementById('graficoRelatorio');
                let config;

                switch(tipo) {
                    case 'vendas':
                        config = {
                            type: 'line',
                            data: {
                                labels: data.map(item => item.data),
                                datasets: [{
                                    label: 'Valor Total',
                                    data: data.map(item => item.valor_total),
                                    borderColor: 'rgb(75, 192, 192)',
                                    tension: 0.1
                                }]
                            }
                        };
                        break;
                    case 'produtos':
                        config = {
                            type: 'bar',
                            data: {
                                labels: data.map(item => item.nome),
                                datasets: [{
                                    label: 'Quantidade Vendida',
                                    data: data.map(item => item.quantidade_vendida),
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                                }]
                            }
                        };
                        break;
                    case 'avaliacoes':
                        config = {
                            type: 'pie',
                            data: {
                                labels: data.map(item => `Nota ${item.nota}`),
                                datasets: [{
                                    data: data.map(item => item.total_avaliacoes),
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.5)',
                                        'rgba(54, 162, 235, 0.5)',
                                        'rgba(255, 206, 86, 0.5)',
                                        'rgba(75, 192, 192, 0.5)',
                                        'rgba(153, 102, 255, 0.5)'
                                    ]
                                }]
                            }
                        };
                        break;
                }

                if (config) {
                    new Chart(ctx, config);
                }
            }

            // Adicionar evento de clique aos cards de relatório
            reportCards.forEach(card => {
                card.addEventListener('click', function() {
                    const tipo = this.querySelector('h4').textContent.toLowerCase();
                    carregarRelatorio(tipo);
                });
            });

            // Adicionar evento de clique ao botão filtrar
            btnFiltrar.addEventListener('click', function() {
                const cardAtivo = document.querySelector('.report-card.active');
                if (cardAtivo) {
                    const tipo = cardAtivo.querySelector('h4').textContent.toLowerCase();
                    carregarRelatorio(tipo);
                }
            });
        });

        // Função para exportar relatório
        function exportarRelatorio(tipo) {
            const table = document.querySelector('.table');
            const html = table.outerHTML;
            const url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.download = `relatorio_${tipo}.xls`;
            link.href = url;
            link.click();
        }
    </script>
</body>
</html> 