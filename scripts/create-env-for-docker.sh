#!/bin/bash
# Criar .env para Docker com PostgreSQL

cat > .env << 'EOF'
APP_NAME="Pesquisa HMSI"
APP_ENV=local
APP_KEY=base64:9lfIf7qRhrszL0B+K7+q7p3xmPBtD+MYUTTFM2TR500=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=pesquisa_hmsi
DB_USERNAME=postgres
DB_PASSWORD=8u@xveRoot

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
EOF

echo "✅ Arquivo .env criado!"
