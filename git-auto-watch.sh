#!/bin/bash

# Função para sincronizar
sync_repo() {
    echo "🔄 Iniciando sincronização automática..."
    
    # Pega as últimas alterações
    git pull origin master
    
    # Adiciona todos os arquivos
    git add .
    
    # Verifica se há mudanças para commitar
    if git diff-index --quiet HEAD --; then
        echo "✅ Nenhuma mudança para sincronizar"
    else
        # Faz o commit com data e hora
        git commit -m "Sincronização automática - $(date '+%d/%m/%Y %H:%M:%S')"
        
        # Faz o push
        git push origin master
        echo "✅ Sincronização concluída com sucesso!"
    fi
}

echo "👀 Iniciando monitoramento automático..."
echo "Pressione Ctrl+C para parar"

# Loop infinito para monitorar mudanças
while true; do
    # Verifica mudanças a cada 30 segundos
    if git diff-index --quiet HEAD --; then
        echo "⏳ Aguardando mudanças... $(date '+%H:%M:%S')"
    else
        sync_repo
    fi
    sleep 30
done 