#!/bin/bash

# Script de setup para o ambiente de desenvolvimento da API Laravel

# Cores para formatação de texto
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando setup do ambiente de desenvolvimento...${NC}"

# Verificar se docker e docker-compose estão instalados
if ! command -v docker &> /dev/null || ! command -v docker compose &> /dev/null; then
    echo -e "${RED}Docker e/ou Docker Compose não estão instalados!${NC}"
    echo "Por favor, instale o Docker e o Docker Compose antes de continuar."
    echo "Instruções: https://docs.docker.com/get-docker/"
    exit 1
fi

# Criar .env a partir do .env.example se não existir
if [ ! -f .env ]; then
    echo -e "${YELLOW}Criando arquivo .env a partir do .env.example...${NC}"
    cp .env.example .env 2>/dev/null || echo -e "${YELLOW}Criando arquivo .env vazio...${NC}"
    touch .env
fi

# Definir permissões adequadas para os diretórios
echo -e "${YELLOW}Configurando permissões...${NC}"
mkdir -p storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Construir e iniciar os containers Docker
echo -e "${YELLOW}Construindo e iniciando os containers Docker...${NC}"
docker compose build --no-cache

# Iniciar os containers em modo detached
echo -e "${YELLOW}Iniciando containers...${NC}"
docker compose up -d

# Aguardar o container PHP estar pronto
echo -e "${YELLOW}Aguardando containers iniciarem...${NC}"
sleep 10

# Instalar dependências do Composer
echo -e "${YELLOW}Instalando dependências do Composer...${NC}"
docker compose exec app-estech composer install

# Gerar chave da aplicação se necessário
echo -e "${YELLOW}Gerando chave da aplicação...${NC}"
docker compose exec app-estech php artisan key:generate --ansi

# Executar migrações do banco de dados
echo -e "${YELLOW}Executando migrações do banco de dados...${NC}"
docker compose exec app-estech php artisan migrate:fresh --seed

# Limpar cache
echo -e "${YELLOW}Limpando cache...${NC}"
docker compose exec app-estech php artisan optimize:clear

# Criar endpoint de health check
echo -e "${YELLOW}Criando endpoint de health check...${NC}"
docker compose exec app-estech php artisan make:controller Api/HealthController

# Informações finais
echo -e "${GREEN}Ambiente configurado com sucesso!${NC}"
echo -e "API disponível em: ${GREEN}http://localhost:8000/api${NC}"
echo ""
echo -e "Comandos úteis:"
echo -e "  - ${YELLOW}docker compose ps${NC} - Lista os containers em execução"
echo -e "  - ${YELLOW}docker compose logs -f${NC} - Exibe logs em tempo real"
echo -e "  - ${YELLOW}docker compose down${NC} - Para e remove os containers"
echo -e "  - ${YELLOW}docker compose exec app-estech bash${NC} - Acessa o shell do container PHP"
