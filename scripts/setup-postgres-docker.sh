#!/bin/bash

# Script para configurar e executar migrations no PostgreSQL via Docker

set -e

echo "🚀 Configurando PostgreSQL e executando migrations no Docker..."
echo ""

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Verificar se docker-compose existe
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ docker-compose não encontrado!${NC}"
    exit 1
fi

# 2. Verificar se arquivo docker-compose.yml existe
if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}❌ docker-compose.yml não encontrado!${NC}"
    exit 1
fi

# 3. Iniciar PostgreSQL
echo -e "${GREEN}📦 Iniciando container PostgreSQL...${NC}"
docker-compose up -d postgres

# 4. Aguardar PostgreSQL estar pronto
echo -e "${YELLOW}⏳ Aguardando PostgreSQL estar pronto...${NC}"
sleep 5

max_attempts=30
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if docker exec pesquisa-hmsi-postgres pg_isready -U postgres &> /dev/null; then
        echo -e "${GREEN}✅ PostgreSQL está pronto!${NC}"
        break
    fi
    attempt=$((attempt + 1))
    echo -n "."
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo -e "${RED}❌ Timeout: PostgreSQL não ficou pronto${NC}"
    exit 1
fi

# 5. Iniciar container PHP
echo -e "${GREEN}📦 Iniciando container PHP...${NC}"
docker-compose up -d php

# 6. Aguardar PHP estar pronto
echo -e "${YELLOW}⏳ Aguardando PHP estar pronto...${NC}"
sleep 5

# 7. Verificar/criar banco de dados
echo -e "${GREEN}🗄️  Verificando banco de dados...${NC}"
docker exec pesquisa-hmsi-php php artisan db:show || {
    echo -e "${YELLOW}⚠️  Banco não existe ou não conecta. Verificando conexão...${NC}"
}

# 8. Executar migrations
echo -e "${GREEN}📋 Executando migrations...${NC}"
docker exec pesquisa-hmsi-php php artisan migrate --force

echo ""
echo -e "${GREEN}✅ Migrations executadas com sucesso!${NC}"

# 9. Perguntar se quer executar seeders
read -p "Deseja executar os seeders? (s/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${GREEN}🌱 Executando seeders...${NC}"
    docker exec pesquisa-hmsi-php php artisan db:seed --force
    echo -e "${GREEN}✅ Seeders executados!${NC}"
fi

echo ""
echo -e "${GREEN}✅ Setup concluído!${NC}"
