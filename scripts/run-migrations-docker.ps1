# Script para executar migrations no PostgreSQL via Docker

Write-Host "🚀 Executando migrations no PostgreSQL (Docker)..." -ForegroundColor Green
Write-Host ""

# Variáveis de ambiente do banco
$env:DB_CONNECTION = "pgsql"
$env:DB_HOST = "postgres"
$env:DB_PORT = "5432"
$env:DB_DATABASE = "pesquisa_hmsi"
$env:DB_USERNAME = "postgres"
$env:DB_PASSWORD = "wug2fvh2CJX@jwq5dck"

# Verificar se container está rodando
$phpRunning = docker ps --filter "name=pesquisa-hmsi-php" --format "{{.Names}}"
if (-not $phpRunning) {
    Write-Host "❌ Container PHP não está rodando. Iniciando..." -ForegroundColor Yellow
    docker start pesquisa-hmsi-php
    Start-Sleep -Seconds 3
}

$postgresRunning = docker ps --filter "name=pesquisa-hmsi-postgres" --format "{{.Names}}"
if (-not $postgresRunning) {
    Write-Host "❌ Container PostgreSQL não está rodando. Iniciando..." -ForegroundColor Yellow
    docker start pesquisa-hmsi-postgres
    Start-Sleep -Seconds 5
}

# Executar migrations
Write-Host "📋 Executando migrations..." -ForegroundColor Green
docker exec -e DB_CONNECTION=pgsql -e DB_HOST=postgres -e DB_PORT=5432 -e DB_DATABASE=pesquisa_hmsi -e DB_USERNAME=postgres -e DB_PASSWORD=$env:DB_PASSWORD pesquisa-hmsi-php php artisan migrate --force

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Migrations executadas com sucesso!" -ForegroundColor Green
    
    # Perguntar se quer executar seeders
    $runSeeders = Read-Host "Deseja executar os seeders? (s/N)"
    if ($runSeeders -eq "s" -or $runSeeders -eq "S") {
        Write-Host "🌱 Executando seeders..." -ForegroundColor Green
        docker exec -e DB_CONNECTION=pgsql -e DB_HOST=postgres -e DB_PORT=5432 -e DB_DATABASE=pesquisa_hmsi -e DB_USERNAME=postgres -e DB_PASSWORD=$env:DB_PASSWORD pesquisa-hmsi-php php artisan db:seed --force
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Seeders executados com sucesso!" -ForegroundColor Green
        }
    }
} else {
    Write-Host ""
    Write-Host "❌ Erro ao executar migrations!" -ForegroundColor Red
    exit 1
}
