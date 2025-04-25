<?php
require_once 'includes/layout.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Atualiza o título da página
    document.querySelector('h1').textContent = 'Dashboard';
    
    // Atualiza o conteúdo dos cards
    const cards = document.querySelectorAll('.card');
    
    // Card de Estatísticas
    cards[0].innerHTML = `
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
    `;

    // Card de Atividades Recentes
    cards[1].innerHTML = `
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
    `;

    // Card de Tarefas
    cards[2].innerHTML = `
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
    `;
});
</script>

<style>
/* Estilos para as estatísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.stat-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-item i {
    font-size: 24px;
    color: var(--primary-color);
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 20px;
    font-weight: bold;
    color: #333;
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
    padding: 12px 0;
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
    color: var(--primary-color);
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
    padding: 10px 0;
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
    cursor: pointer;
}

/* Responsividade */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>