# Script para configurar .env para desenvolvimento local
# Ajusta configurações para usar php artisan serve sem erros SSL

$ErrorActionPreference = "Stop"

Write-Host "=== Configurando .env para desenvolvimento local ===" -ForegroundColor Cyan

$envFile = ".env"

if (-not (Test-Path $envFile)) {
    Write-Host "ERRO: Arquivo .env não encontrado!" -ForegroundColor Red
    Write-Host "Copie o .env.example para .env primeiro." -ForegroundColor Yellow
    exit 1
}

Write-Host "`nAjustando configurações..." -ForegroundColor Yellow

# Backup do .env
$backupFile = ".env.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"
Copy-Item $envFile $backupFile
Write-Host "Backup criado: $backupFile" -ForegroundColor Green

# Lê o conteúdo do .env
$content = Get-Content $envFile -Raw

# Ajusta APP_ENV para local (opcional, pode manter production)
# $content = $content -replace '(?m)^APP_ENV=.*', 'APP_ENV=local'

# Ajusta APP_DEBUG
$content = $content -replace '(?m)^APP_DEBUG=.*', 'APP_DEBUG=true'

# Ajusta APP_URL para http://127.0.0.1:8000
$content = $content -replace '(?m)^APP_URL=.*', 'APP_URL=http://127.0.0.1:8000'

# Ajusta SESSION_SECURE_COOKIE para false (importante!)
$content = $content -replace '(?m)^SESSION_SECURE_COOKIE=.*', 'SESSION_SECURE_COOKIE=false'

# Salva o arquivo
$content | Set-Content $envFile -Encoding UTF8

Write-Host "`nConfigurações ajustadas:" -ForegroundColor Green
Write-Host "  - APP_DEBUG=true" -ForegroundColor Cyan
Write-Host "  - APP_URL=http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "  - SESSION_SECURE_COOKIE=false" -ForegroundColor Cyan
Write-Host "`nLimpe os caches do Laravel:" -ForegroundColor Yellow
Write-Host "  php artisan config:clear" -ForegroundColor White
Write-Host "  php artisan cache:clear" -ForegroundColor White
Write-Host "`nE limpe os cookies do navegador para localhost:8000" -ForegroundColor Yellow
Write-Host "`nAgora você pode usar 'php artisan serve' sem erros SSL!" -ForegroundColor Green
