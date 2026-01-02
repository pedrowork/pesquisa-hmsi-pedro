#!/bin/bash

# Script de Deploy Automatizado para Pesquisa HMSI
# Uso: ./deploy.sh

set -e

echo "🚀 Iniciando deploy da aplicação Pesquisa HMSI..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: Execute este script no diretório raiz da aplicação${NC}"
    exit 1
fi

# Verificar se .env existe
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  Arquivo .env não encontrado. Copiando de .env.example...${NC}"
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${YELLOW}⚠️  Por favor, configure o arquivo .env antes de continuar${NC}"
        exit 1
    else
        echo -e "${RED}❌ Arquivo .env.example não encontrado${NC}"
        exit 1
    fi
fi

# Backup do banco de dados (se MySQL/MariaDB)
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2)
if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    echo -e "${YELLOW}📦 Criando backup do banco de dados...${NC}"
    DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
    
    BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
    mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE" 2>/dev/null || {
        echo -e "${YELLOW}⚠️  Não foi possível criar backup automático. Continuando...${NC}"
    }
fi

# Atualizar dependências PHP
echo -e "${GREEN}📦 Instalando/atualizando dependências PHP...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Atualizar dependências Node.js
echo -e "${GREEN}📦 Instalando/atualizando dependências Node.js...${NC}"
npm install --production

# Build dos assets
echo -e "${GREEN}🔨 Compilando assets para produção...${NC}"
npm run build

# Executar migrações
echo -e "${GREEN}🗄️  Executando migrações do banco de dados...${NC}"
php artisan migrate --force

# Limpar caches antigos
echo -e "${GREEN}🧹 Limpando caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Recriar caches
echo -e "${GREEN}⚡ Otimizando aplicação...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Ajustar permissões
echo -e "${GREEN}🔐 Ajustando permissões...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || {
    echo -e "${YELLOW}⚠️  Não foi possível alterar proprietário (pode precisar de sudo)${NC}"
}

# Verificar se Supervisor está configurado
if command -v supervisorctl &> /dev/null; then
    echo -e "${GREEN}🔄 Reiniciando workers da fila...${NC}"
    sudo supervisorctl restart pesquisa-hmsi-queue:* 2>/dev/null || {
        echo -e "${YELLOW}⚠️  Supervisor não configurado ou workers não encontrados${NC}"
    }
fi

# Recarregar PHP-FPM
echo -e "${GREEN}🔄 Recarregando PHP-FPM...${NC}"
sudo systemctl reload php8.2-fpm 2>/dev/null || {
    echo -e "${YELLOW}⚠️  Não foi possível recarregar PHP-FPM (pode não estar instalado)${NC}"
}

echo -e "${GREEN}✅ Deploy concluído com sucesso!${NC}"
echo -e "${GREEN}🌐 Acesse sua aplicação e verifique se está funcionando corretamente${NC}"

