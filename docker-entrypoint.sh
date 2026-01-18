#!/bin/sh
set -e

echo "🚀 Iniciando container PHP..."

# Copiar public/ se o volume estiver vazio
if [ ! "$(ls -A /var/www/html/public)" ]; then
    echo "📦 Copiando arquivos públicos para volume..."
    cp -r /var/www/html-original/public/* /var/www/html/public/
fi

# Criar estrutura de storage se não existir
if [ ! -d "/var/www/html/storage/app" ]; then
    echo "📁 Criando estrutura de storage..."
    mkdir -p /var/www/html/storage/{app,framework,logs}
    mkdir -p /var/www/html/storage/framework/{cache,sessions,testing,views}
    mkdir -p /var/www/html/storage/app/public
fi

# Criar estrutura de bootstrap/cache se não existir
if [ ! -d "/var/www/html/bootstrap/cache" ]; then
    echo "📁 Criando bootstrap/cache..."
    mkdir -p /var/www/html/bootstrap/cache
fi

# Ajustar permissões
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Container pronto!"

# Executar o comando original do container
exec "$@"
