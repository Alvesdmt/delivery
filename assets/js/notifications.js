// Criar o elemento de áudio
const notificationSound = new Audio('assets/audio/notification.mp3');

// Função para tocar o som de notificação
function playNotificationSound() {
    notificationSound.play().catch(error => {
        console.log('Erro ao tocar som:', error);
    });
}

// Função para verificar novas notificações
function checkNotifications() {
    fetch('admin/includes/notifications.php?action=get')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Atualiza o contador de notificações
            const counter = document.getElementById('notification-counter');
            if (counter) {
                counter.textContent = data.count;
                counter.style.display = data.count > 0 ? 'inline' : 'none';
            }

            // Atualiza a lista de notificações
            const list = document.getElementById('notification-list');
            if (list) {
                if (data.notifications && data.notifications.length > 0) {
                    list.innerHTML = data.notifications.map(notification => `
                        <div class="notification-item ${notification.read ? '' : 'unread'}" data-url="${notification.redirectUrl || ''}">
                            <div class="notification-content">
                                <div class="notification-message">${notification.message}</div>
                                <div class="notification-time">${notification.time}</div>
                            </div>
                        </div>
                    `).join('');

                    // Adiciona eventos de clique para as notificações
                    document.querySelectorAll('.notification-item').forEach(item => {
                        const url = item.getAttribute('data-url');
                        if (url) {
                            item.style.cursor = 'pointer';
                            item.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Marca a notificação como lida
                                fetch('admin/includes/notifications.php?action=mark_read')
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Erro ao marcar notificação como lida');
                                        }
                                        // Redireciona para a página do pedido
                                        window.location.href = url;
                                    })
                                    .catch(error => {
                                        console.error('Erro:', error);
                                        // Mesmo com erro, tenta redirecionar
                                        window.location.href = url;
                                    });
                            });
                        }
                    });
                } else {
                    list.innerHTML = '<div class="notification-item"><div>Nenhuma notificação</div></div>';
                }
            }

            // Toca o som se houver novas notificações com som
            if (data.hasSound) {
                playNotificationSound();
            }
        })
        .catch(error => {
            console.error('Erro ao verificar notificações:', error);
            const list = document.getElementById('notification-list');
            if (list) {
                list.innerHTML = '<div class="notification-item"><div>Erro ao carregar notificações</div></div>';
            }
        });
}

// Verifica notificações a cada 30 segundos
setInterval(checkNotifications, 30000);

// Verifica notificações imediatamente quando o script carrega
document.addEventListener('DOMContentLoaded', checkNotifications); 