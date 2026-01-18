#!/bin/bash

# Script de Deploy em Produção - Pesquisa HMSI
# Execute na VPS após git clone

set -e  # Parar em caso de erro

echo "==================================="
echo "Deploy Pesquisa HMSI - Produção"
echo "==================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar se está no diretório correto
if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}Erro: docker-compose.yml não encontrado!${NC}"
    echo "Execute este script no diretório raiz do projeto."
    exit 1
fi

# 1. Verificar arquivo .env
echo -e "${YELLOW}[1/8] Verificando arquivo .env...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Arquivo .env criado a partir do .env.example${NC}"
        echo -e "${YELLOW}⚠ IMPORTANTE: Edite o arquivo .env e configure:${NC}"
        echo "   - APP_KEY (será gerado depois)"
        echo "   - DB_PASSWORD"
        echo "   - APP_URL"
        echo "   - Configurações de email (se necessário)"
        echo ""
        echo "Pressione ENTER após configurar o .env ou Ctrl+C para sair..."
        read
    else
        echo -e "${RED}✗ Arquivo .env.example não encontrado!${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ Arquivo .env já existe${NC}"
fi

# 2. Parar containers existentes (se houver)
echo -e "${YELLOW}[2/8] Parando containers existentes...${NC}"
docker-compose down 2>/dev/null || true
echo -e "${GREEN}✓ Containers parados${NC}"

# 3. Limpar volumes antigos (CUIDADO: isso apaga dados!)
read -p "Deseja limpar volumes antigos? Isso apagará TODOS os dados! (s/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${YELLOW}Removendo volumes...${NC}"
    docker volume rm pesquisa-hmsi-pedro_app_storage 2>/dev/null || true
    docker volume rm pesquisa-hmsi-pedro_app_cache 2>/dev/null || true
    docker volume rm pesquisa-hmsi-pedro_app_public 2>/dev/null || true
    docker volume rm pesquisa-hmsi-pedro_postgres_data 2>/dev/null || true
    docker volume rm pesquisa-hmsi-pedro_redis_data 2>/dev/null || true
    echo -e "${GREEN}✓ Volumes removidos${NC}"
fi

# 4. Build das imagens
echo -e "${YELLOW}[3/8] Fazendo build das imagens Docker...${NC}"
docker-compose build --no-cache php
echo -e "${GREEN}✓ Build concluído${NC}"

# 5. Subir containers
echo -e "${YELLOW}[4/8] Subindo containers...${NC}"
docker-compose up -d
echo -e "${GREEN}✓ Containers iniciados${NC}"

# Aguardar containers ficarem saudáveis
echo -e "${YELLOW}Aguardando containers ficarem saudáveis (até 60s)...${NC}"
sleep 10
for i in {1..10}; do
    if docker-compose ps | grep -q "unhealthy"; then
        echo "Aguardando... ($i/10)"
        sleep 5
    else
        break
    fi
done

# 6. Gerar APP_KEY
echo -e "${YELLOW}[5/8] Gerando APP_KEY...${NC}"
docker-compose exec -T php php artisan key:generate --force
echo -e "${GREEN}✓ APP_KEY gerada${NC}"

# 7. Copiar arquivos públicos para o volume
echo -e "${YELLOW}[6/8] Copiando arquivos públicos para volume...${NC}"
docker-compose exec -T php cp -r /var/www/html/public/* /var/www/html/public/ 2>/dev/null || true
echo -e "${GREEN}✓ Arquivos públicos copiados${NC}"

# 8. Executar migrations
echo -e "${YELLOW}[7/8] Executando migrations...${NC}"
read -p "Deseja executar as migrations? (S/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Nn]$ ]]; then
    docker-compose exec -T php php artisan migrate --force
    echo -e "${GREEN}✓ Migrations executadas${NC}"
    
    # Criar usuário admin
    read -p "Deseja criar o usuário admin? (S/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        docker-compose exec -T php php artisan db:seed --class=AdminSeeder --force
        echo -e "${GREEN}✓ Usuário admin criado${NC}"
    fi
fi

# 9. Otimizar para produção
echo -e "${YELLOW}[8/8] Otimizando para produção...${NC}"
docker-compose exec -T php php artisan config:cache
docker-compose exec -T php php artisan route:cache
docker-compose exec -T php php artisan view:cache
docker-compose exec -T php php artisan optimize
echo -e "${GREEN}✓ Otimizações aplicadas${NC}"

# Status final
echo ""
echo -e "${GREEN}==================================="
echo "Deploy Concluído com Sucesso!"
echo "===================================${NC}"
echo ""
echo "Containers rodando:"
docker-compose ps
echo ""
echo -e "${YELLOW}Próximos passos:${NC}"
echo "1. Acesse: http://$(hostname -I | awk '{print $1}'):8080"
echo "2. Configure o Nginx do host para proxy reverso (opcional)"
echo "3. Configure SSL com certbot (opcional)"
echo ""
echo -e "${YELLOW}Comandos úteis:${NC}"
echo "  Ver logs:       docker-compose logs -f --tail=50"
echo "  Ver containers: docker-compose ps"
echo "  Reiniciar:      docker-compose restart"
echo "  Parar:          docker-compose down"
echo ""
