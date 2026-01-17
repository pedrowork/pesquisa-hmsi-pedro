# Script para executar seeders no PostgreSQL dentro do Docker
# Garante que DB_PASSWORD está definida corretamente

$ErrorActionPreference = "Stop"

Write-Host "=== Executando Seeders no PostgreSQL (Docker) ===" -ForegroundColor Cyan

# Define a senha do banco (ajuste se necessário)
$dbPassword = "wug2fvh2CJX@jwq5dck"

# Verifica se o container PHP está rodando
Write-Host "Verificando containers..." -ForegroundColor Yellow
$phpContainer = docker ps --filter "name=pesquisa-hmsi-php" --format "{{.Names}}"
if (-not $phpContainer) {
    Write-Host "ERRO: Container 'pesquisa-hmsi-php' não está rodando!" -ForegroundColor Red
    Write-Host "Execute: docker-compose up -d php" -ForegroundColor Yellow
    exit 1
}

Write-Host "Container PHP encontrado: $phpContainer" -ForegroundColor Green

# Limpa cache de configuração
Write-Host "`nLimpando cache de configuração..." -ForegroundColor Yellow
docker-compose exec -e "DB_PASSWORD=$dbPassword" php php artisan config:clear

# Executa os seeders
Write-Host "`nExecutando seeders..." -ForegroundColor Yellow
docker-compose exec -e "DB_PASSWORD=$dbPassword" php php artisan db:seed --force

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n=== Seeders executados com sucesso! ===" -ForegroundColor Green
} else {
    Write-Host "`n=== ERRO ao executar seeders! ===" -ForegroundColor Red
    exit 1
}
