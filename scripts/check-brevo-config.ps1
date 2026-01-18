# Script para verificar configuração do Brevo
Write-Host "=== Verificacao de Configuracao Brevo ===" -ForegroundColor Cyan
Write-Host ""

# Verifica se o .env existe
if (-not (Test-Path ".env")) {
    Write-Host "ERRO: Arquivo .env nao encontrado!" -ForegroundColor Red
    exit 1
}

# Lê o .env
$envContent = Get-Content ".env" | Where-Object { $_ -match "^MAIL_" }

Write-Host "Configuracoes encontradas:" -ForegroundColor Yellow
$envContent | ForEach-Object {
    if ($_ -match "MAIL_PASSWORD") {
        Write-Host "  MAIL_PASSWORD=***" -ForegroundColor Gray
    } else {
        Write-Host "  $_" -ForegroundColor White
    }
}

Write-Host ""
Write-Host "Verificacoes:" -ForegroundColor Yellow

# Verifica MAIL_USERNAME
$usernameLine = $envContent | Where-Object { $_ -match "^MAIL_USERNAME=" }
if ($usernameLine) {
    $username = ($usernameLine -split "=")[1].Trim()
    if ($username -eq "hmsitise@gmail.com") {
        Write-Host "  ⚠️  MAIL_USERNAME: $username" -ForegroundColor Yellow
        Write-Host "     → Este email pode nao ser o correto para o Brevo" -ForegroundColor Yellow
        Write-Host "     → Deve ser o email da conta Brevo OU o formato mostrado no painel" -ForegroundColor Yellow
        Write-Host "     → No painel do Brevo, veja o campo 'Fazer login' (ex: a04b9a001@smtp-brevo.com)" -ForegroundColor Yellow
    } else {
        Write-Host "  ✅ MAIL_USERNAME configurado" -ForegroundColor Green
    }
} else {
    Write-Host "  ❌ MAIL_USERNAME nao encontrado no .env" -ForegroundColor Red
}

# Verifica MAIL_PASSWORD
$passwordLine = $envContent | Where-Object { $_ -match "^MAIL_PASSWORD=" }
if ($passwordLine) {
    $password = ($passwordLine -split "=")[1].Trim()
    if ($password -match "^xsmtp") {
        Write-Host "  ✅ MAIL_PASSWORD: Formato SMTP Key correto (xsmtp...)" -ForegroundColor Green
    } else {
        Write-Host "  ❌ MAIL_PASSWORD: Nao parece ser uma SMTP Key" -ForegroundColor Red
        Write-Host "     → Deve comecar com 'xsmtpsib-' ou 'xsmtp-'" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ❌ MAIL_PASSWORD nao encontrado no .env" -ForegroundColor Red
}

# Verifica outras configurações
$hostLine = $envContent | Where-Object { $_ -match "^MAIL_HOST=" }
if ($hostLine -and ($hostLine -match "brevo|sendinblue")) {
    Write-Host "  ✅ MAIL_HOST: Configurado para Brevo" -ForegroundColor Green
} else {
    Write-Host "  ⚠️  MAIL_HOST: Verifique se esta como 'smtp-relay.brevo.com'" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Para testar apos corrigir:" -ForegroundColor Cyan
Write-Host "  php artisan config:clear" -ForegroundColor White
Write-Host "  php artisan mail:test seu@email.com" -ForegroundColor White
