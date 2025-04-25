#!/bin/bash

# Mostra o status atual
echo "Verificando status do repositório..."
git status

# Pega as últimas alterações do GitHub
echo "Sincronizando com o GitHub..."
git pull origin master

# Adiciona todos os arquivos
echo "Adicionando arquivos..."
git add .

# Faz o commit com a data e hora atual
echo "Criando commit..."
git commit -m "Sincronização automática - $(date '+%d/%m/%Y %H:%M:%S')"

# Faz o push para o repositório remoto
echo "Enviando para o GitHub..."
git push origin master

echo "✅ Sincronização automática concluída com sucesso!" 