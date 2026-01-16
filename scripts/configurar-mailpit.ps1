# Script PowerShell para configurar Mailpit no .env

$envFile = ".env"

if (-not (Test-Path $envFile)) {
    Write-Host "Arquivo .env nao encontrado!" -ForegroundColor Red
    exit 1
}

Write-Host "Configurando Mailpit no .env..." -ForegroundColor Cyan

$content = Get-Content $envFile -Raw

# Configurações de email
$mailConfig = @"
# Configuração de Email - Mailpit
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@pesquisa-hmsi.local
MAIL_FROM_NAME="Pesquisa HMSI"
"@

# Remover configurações antigas de email se existirem
$lines = Get-Content $envFile
$newLines = @()
$inMailSection = $false
$mailSectionAdded = $false

foreach ($line in $lines) {
    if ($line -match '^#.*[Mm]ail|^MAIL_') {
        if (-not $mailSectionAdded) {
            $newLines += $mailConfig
            $mailSectionAdded = $true
        }
        $inMailSection = $true
        continue
    }
    
    if ($inMailSection -and $line -eq '') {
        $inMailSection = $false
    }
    
    if (-not $inMailSection) {
        $newLines += $line
    }
}

# Se não encontrou seção de email, adicionar no final
if (-not $mailSectionAdded) {
    $newLines += ""
    $newLines += $mailConfig
}

$newLines | Set-Content $envFile

Write-Host "Configuracao concluida!" -ForegroundColor Green
Write-Host ""
Write-Host "Proximos passos:" -ForegroundColor Yellow
Write-Host "1. Limpar cache: docker-compose exec php php artisan config:clear"
Write-Host "2. Testar email: docker-compose exec php php artisan mail:test"
Write-Host "3. Verificar no Mailpit: http://localhost:8025"
