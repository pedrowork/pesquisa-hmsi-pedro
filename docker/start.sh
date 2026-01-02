#!/bin/bash

# Script de inicialização do Docker Compose
# Garante que os containers iniciem automaticamente

set -e

echo "🚀 Iniciando aplicação Pesquisa HMSI..."

# Verificar se Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Iniciando Docker..."
    sudo systemctl start docker
    sleep 5
fi

# Navegar para o diretório do projeto
cd "$(dirname "$0")/.."

# Verificar se docker-compose.yml existe
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Arquivo docker-compose.yml não encontrado!"
    exit 1
fi

# Parar containers existentes (se houver)
echo "🛑 Parando containers existentes..."
docker-compose down 2>/dev/null || true

# Iniciar containers
echo "▶️  Iniciando containers..."
docker-compose up -d --build

# Aguardar serviços ficarem prontos
echo "⏳ Aguardando serviços ficarem prontos..."
sleep 10

# Verificar status
echo "📊 Status dos containers:"
docker-compose ps

# Verificar saúde dos serviços
echo "🏥 Verificando saúde dos serviços..."
docker-compose ps --format "table {{.Name}}\t{{.Status}}"

echo "✅ Aplicação iniciada com sucesso!"
echo "🌐 Acesse: http://localhost (ou https://localhost se SSL configurado)"

