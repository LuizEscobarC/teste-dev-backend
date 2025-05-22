#!/bin/bash

# Script para remover o diretório vendor do controle de versão do Git
# mantendo os arquivos localmente

# Cores para formatação de texto
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Verificando se o diretório vendor está no controle de versão do Git...${NC}"

# Verificar se o diretório vendor está sendo rastreado pelo Git
VENDOR_FILES=$(git ls-files | grep "^vendor/")

if [ -z "$VENDOR_FILES" ]; then
    echo -e "${GREEN}O diretório vendor já não está sob controle de versão do Git.${NC}"
    
    # Verificando .gitignore
    if grep -q "/vendor" .gitignore; then
        echo -e "${GREEN}A regra para ignorar o diretório vendor já existe no .gitignore.${NC}"
    else
        echo -e "${YELLOW}Adicionando regra para ignorar o diretório vendor no .gitignore...${NC}"
        echo "/vendor" >> .gitignore
        git add .gitignore
        git commit -m "Adicionar vendor ao .gitignore"
        echo -e "${GREEN}Regra adicionada ao .gitignore e alterações commitadas.${NC}"
    fi
    
    exit 0
fi

# Verificando .gitignore
if ! grep -q "/vendor" .gitignore; then
    echo -e "${YELLOW}Adicionando regra para ignorar o diretório vendor no .gitignore...${NC}"
    echo "/vendor" >> .gitignore
    git add .gitignore
    git commit -m "Adicionar vendor ao .gitignore"
    echo -e "${GREEN}Regra adicionada ao .gitignore e alterações commitadas.${NC}"
fi

echo -e "${YELLOW}Removendo o diretório vendor do controle de versão do Git...${NC}"
echo -e "(Os arquivos permanecerão no sistema de arquivos local)"

# Remover o diretório vendor do controle de versão do Git, mantendo os arquivos locais
git rm -r --cached vendor/

# Comitar as alterações
git commit -m "Remover diretório vendor do controle de versão do Git"

echo -e "${GREEN}Diretório vendor removido com sucesso do controle de versão do Git!${NC}"
echo -e "${GREEN}Os arquivos vendor permanecem no sistema de arquivos local.${NC}"
