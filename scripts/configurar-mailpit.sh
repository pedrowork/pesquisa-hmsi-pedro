#!/bin/bash

# Script para configurar Mailpit no .env

ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Arquivo .env não encontrado!"
    exit 1
fi

echo "📧 Configurando Mailpit no .env..."

# Verificar se as variáveis já existem
if grep -q "MAIL_MAILER=" "$ENV_FILE"; then
    echo "⚠️  MAIL_MAILER já existe, atualizando..."
    sed -i.bak 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' "$ENV_FILE"
else
    echo "➕ Adicionando MAIL_MAILER..."
    echo "" >> "$ENV_FILE"
    echo "# Configuração de Email - Mailpit" >> "$ENV_FILE"
    echo "MAIL_MAILER=smtp" >> "$ENV_FILE"
fi

if grep -q "MAIL_HOST=" "$ENV_FILE"; then
    sed -i.bak 's/^MAIL_HOST=.*/MAIL_HOST=mailpit/' "$ENV_FILE"
else
    echo "MAIL_HOST=mailpit" >> "$ENV_FILE"
fi

if grep -q "MAIL_PORT=" "$ENV_FILE"; then
    sed -i.bak 's/^MAIL_PORT=.*/MAIL_PORT=1025/' "$ENV_FILE"
else
    echo "MAIL_PORT=1025" >> "$ENV_FILE"
fi

if ! grep -q "MAIL_USERNAME=" "$ENV_FILE"; then
    echo "MAIL_USERNAME=" >> "$ENV_FILE"
fi

if ! grep -q "MAIL_PASSWORD=" "$ENV_FILE"; then
    echo "MAIL_PASSWORD=" >> "$ENV_FILE"
fi

if grep -q "MAIL_ENCRYPTION=" "$ENV_FILE"; then
    sed -i.bak 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=null/' "$ENV_FILE"
else
    echo "MAIL_ENCRYPTION=null" >> "$ENV_FILE"
fi

if grep -q "MAIL_FROM_ADDRESS=" "$ENV_FILE"; then
    sed -i.bak 's/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=noreply@pesquisa-hmsi.local/' "$ENV_FILE"
else
    echo "MAIL_FROM_ADDRESS=noreply@pesquisa-hmsi.local" >> "$ENV_FILE"
fi

if grep -q "MAIL_FROM_NAME=" "$ENV_FILE"; then
    sed -i.bak 's/^MAIL_FROM_NAME=.*/MAIL_FROM_NAME="Pesquisa HMSI"/' "$ENV_FILE"
else
    echo 'MAIL_FROM_NAME="Pesquisa HMSI"' >> "$ENV_FILE"
fi

# Remover arquivos de backup
rm -f "$ENV_FILE.bak"

echo "✅ Configuração concluída!"
echo ""
echo "📋 Próximos passos:"
echo "1. Limpar cache: docker-compose exec php php artisan config:clear"
echo "2. Testar email: docker-compose exec php php artisan mail:test"
echo "3. Verificar no Mailpit: http://localhost:8025"
