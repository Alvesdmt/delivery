#!/bin/bash

# Função para sincronizar
sync_repo() {
    echo "🔄 Iniciando sincronização automática..."
    
    # Força o pull para garantir que está atualizado
    git fetch origin
    git reset --hard origin/master
    
    # Adiciona todos os arquivos
    git add --all
    
    # Verifica se há mudanças
    if [[ $(git status --porcelain) ]]; then
        echo "📝 Encontradas mudanças para sincronizar"
        
        # Faz o commit com data e hora
        git commit -m "Sincronização automática - $(date '+%d/%m/%Y %H:%M:%S')"
        
        # Força o push
        git push -f origin master
        echo "✅ Sincronização concluída com sucesso!"
    else
        echo "✅ Nenhuma mudança para sincronizar"
    fi
}

echo "👀 Iniciando monitoramento automático..."
echo "Pressione Ctrl+C para parar"

# Loop infinito para monitorar mudanças
while true; do
    # Verifica mudanças a cada 10 segundos
    if [[ $(git status --porcelain) ]]; then
        sync_repo
    else
        echo "⏳ Aguardando mudanças... $(date '+%H:%M:%S')"
    fi
    sleep 10
done 