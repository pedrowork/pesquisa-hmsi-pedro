# Script PowerShell para configurar e executar migrations no PostgreSQL via Docker

Write-Host "🚀 Configurando PostgreSQL e executando migrations no Docker..." -ForegroundColor Green
Write-Host ""

# 1. Verificar se docker-compose existe
if (-not (Get-Command docker-compose -ErrorAction SilentlyContinue)) {
    Write-Host "❌ docker-compose não encontrado!" -ForegroundColor Red
    exit 1
}

# 2. Verificar se arquivo docker-compose.yml existe
if (-not (Test-Path "docker-compose.yml")) {
    Write-Host "❌ docker-compose.yml não encontrado!" -ForegroundColor Red
    exit 1
}

# 3. Iniciar PostgreSQL
Write-Host "📦 Iniciando container PostgreSQL..." -ForegroundColor Green
docker-compose up -d postgres

# 4. Aguardar PostgreSQL estar pronto
Write-Host "⏳ Aguardando PostgreSQL estar pronto..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

$maxAttempts = 30
$attempt = 0
$ready = $false

while ($attempt -lt $maxAttempts) {
    $result = docker exec pesquisa-hmsi-postgres pg_isready -U postgres 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ PostgreSQL está pronto!" -ForegroundColor Green
        $ready = $true
        break
    }
    $attempt++
    Write-Host "." -NoNewline
    Start-Sleep -Seconds 2
}

if (-not $ready) {
    Write-Host "❌ Timeout: PostgreSQL não ficou pronto" -ForegroundColor Red
    exit 1
}
Write-Host ""

# 5. Iniciar container PHP
Write-Host "📦 Iniciando container PHP..." -ForegroundColor Green
docker-compose up -d php

# 6. Aguardar PHP estar pronto
Write-Host "⏳ Aguardando PHP estar pronto..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# 7. Verificar/criar banco de dados
Write-Host "🗄️  Verificando banco de dados..." -ForegroundColor Green
docker exec pesquisa-hmsi-php php artisan db:show 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Banco não existe ou não conecta. Verificando conexão..." -ForegroundColor Yellow
}

# 8. Executar migrations
Write-Host "📋 Executando migrations..." -ForegroundColor Green
docker exec pesquisa-hmsi-php php artisan migrate --force

Write-Host ""
Write-Host "✅ Migrations executadas com sucesso!" -ForegroundColor Green

# 9. Perguntar se quer executar seeders
$runSeeders = Read-Host "Deseja executar os seeders? (s/N)"
if ($runSeeders -eq "s" -or $runSeeders -eq "S") {
    Write-Host "🌱 Executando seeders..." -ForegroundColor Green
    docker exec pesquisa-hmsi-php php artisan db:seed --force
    Write-Host "✅ Seeders executados!" -ForegroundColor Green
}

Write-Host ""
Write-Host "✅ Setup concluído!" -ForegroundColor Green
