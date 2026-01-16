# Configurar Mailpit - Guia Rápido

## Problema Identificado

O Laravel está usando `log` em vez de `smtp` porque as variáveis de ambiente não estão configuradas no arquivo `.env`.

## Solução

### 1. Verificar/Criar arquivo .env

Certifique-se de que existe um arquivo `.env` na raiz do projeto. Se não existir, copie do `.env.example`:

```bash
cp .env.example .env
```

### 2. Adicionar/Atualizar variáveis de email no .env

Abra o arquivo `.env` e adicione ou atualize as seguintes linhas:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@pesquisa-hmsi.local
MAIL_FROM_NAME="Pesquisa HMSI"
```

### 3. Limpar cache de configuração

Após alterar o `.env`, limpe o cache de configuração:

```bash
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan config:cache
```

### 4. Testar o envio de email

Execute o comando de teste:

```bash
docker-compose exec php php artisan mail:test seu-email@example.com
```

### 5. Verificar no Mailpit

Acesse http://localhost:8025 e verifique se o email apareceu na caixa de entrada.

## Comandos Úteis

```bash
# Ver configuração atual
docker-compose exec php php artisan mail:test

# Limpar cache
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan cache:clear

# Reiniciar containers (se necessário)
docker-compose restart php
```

## Verificação Rápida

Para verificar se está configurado corretamente:

```bash
docker-compose exec php php artisan tinker
```

No tinker, execute:

```php
config('mail.default')  // Deve retornar 'smtp'
config('mail.mailers.smtp.host')  // Deve retornar 'mailpit'
config('mail.mailers.smtp.port')  // Deve retornar 1025
```
