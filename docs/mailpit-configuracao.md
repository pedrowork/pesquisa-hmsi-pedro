# Configuração do Mailpit para Envio de Emails

O Mailpit é uma ferramenta de teste de emails que captura todos os emails enviados pela aplicação e os exibe em uma interface web.

## Configuração

### 1. Variáveis de Ambiente (.env)

Adicione as seguintes variáveis no seu arquivo `.env`:

```env
# Configuração de Email - Mailpit
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@pesquisa-hmsi.local
MAIL_FROM_NAME="Pesquisa HMSI"
```

### 2. Acessar a Interface do Mailpit

Após iniciar os containers Docker, acesse a interface web do Mailpit em:

- **URL Local**: http://localhost:8025
- **URL VPS**: http://SEU_IP:8025 (se a porta estiver exposta)

### 3. Iniciar os Containers

```bash
docker-compose up -d
```

O Mailpit será iniciado automaticamente junto com os outros serviços.

### 4. Verificar se está funcionando

1. Acesse a interface do Mailpit (http://localhost:8025)
2. Registre um novo usuário na aplicação
3. O email de verificação aparecerá na interface do Mailpit
4. Clique no email para visualizar o conteúdo completo
5. Use o link de verificação diretamente do Mailpit

## Portas

- **1025**: Porta SMTP (usada pelo Laravel para enviar emails)
- **8025**: Porta da Interface Web (para visualizar os emails)

## Notas

- O Mailpit é ideal para desenvolvimento e testes
- Todos os emails enviados são capturados e não são realmente enviados
- Os emails ficam armazenados na memória do container (serão perdidos ao reiniciar)
- Para produção, configure um serviço de email real (SMTP, Mailgun, SendGrid, etc.)

## Alternativas para Produção

Se precisar usar um serviço real de email em produção, você pode:

1. **SMTP Genérico**: Configure com as credenciais do seu provedor SMTP
2. **Mailgun**: `MAIL_MAILER=mailgun`
3. **SendGrid**: `MAIL_MAILER=smtp` com credenciais do SendGrid
4. **Amazon SES**: `MAIL_MAILER=ses`
5. **Postmark**: `MAIL_MAILER=postmark`
