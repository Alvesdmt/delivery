#!/bin/bash

# Adiciona todos os arquivos
git add .

# Faz o commit com a data e hora atual
git commit -m "Atualização automática - $(date '+%d/%m/%Y %H:%M:%S')"

# Faz o push para o repositório remoto
git push origin master

echo "Push automático concluído!" 