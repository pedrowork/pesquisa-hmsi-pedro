# Configuração do Brevo (ex-Sendinblue)

## Como obter as credenciais SMTP do Brevo

### 1. Acesse o Dashboard do Brevo
- Faça login em: https://app.brevo.com
- Vá em **Configurações** → **SMTP & API**

### 2. Obtenha a SMTP Key
- Na seção **Chaves SMTP**, você verá suas chaves
- Se não tiver uma, clique em **Gerar uma nova chave SMTP**
- **IMPORTANTE**: Copie a chave SMTP (não a senha da conta)

### 3. Configure o `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=seu_email_brevo@example.com
MAIL_PASSWORD=xsmtp-sua-chave-smtp-aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seudominio.com.br
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Campos importantes

#### `MAIL_USERNAME`
- Use o **email da sua conta Brevo** (o email com que você se cadastrou)
- **NÃO** use um email qualquer (ex: `hmsitise@gmail.com`)

#### `MAIL_PASSWORD`
- Use a **SMTP Key** gerada no Brevo
- Formato: `xsmtp-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
- **NÃO** use a senha da sua conta

#### `MAIL_FROM_ADDRESS`
- Pode ser qualquer email do domínio verificado no Brevo
- Ou use o email da conta Brevo se não tiver domínio verificado

### 5. Verificar domínio (Recomendado para produção)

1. Vá em **Configurações** → **Domínios enviados**
2. Adicione seu domínio
3. Configure os registros DNS conforme instruções
4. Aguarde a verificação

### 6. Testar a configuração

```bash
php artisan config:clear
php artisan mail:test seu@email.com
```

## Solução de Problemas

### Erro: "535 5.7.8 Authentication failed"

**Causas:**
- `MAIL_USERNAME` não é o email da conta Brevo
- `MAIL_PASSWORD` não é a SMTP Key (está usando senha da conta)
- SMTP Key foi revogada ou expirada

**Solução:**
1. Verifique se `MAIL_USERNAME` = email da conta Brevo
2. Verifique se `MAIL_PASSWORD` = SMTP Key (começa com `xsmtp-`)
3. Gere uma nova SMTP Key no Brevo se necessário

### Erro: "Connection timeout"

**Causa:** Firewall bloqueando porta 587

**Solução:**
- Verifique firewall/antivírus
- Teste com porta 465 (requer `MAIL_ENCRYPTION=ssl`)

### Email não chega

**Verificações:**
1. Email está na caixa de spam?
2. Domínio `MAIL_FROM_ADDRESS` está verificado no Brevo?
3. Verifique logs: `storage/logs/laravel.log`
4. Verifique estatísticas no dashboard do Brevo

## Limites do Plano Free

- **300 emails/dia**
- **25.000 emails/mês**
- Domínios verificados: limitado

Para produção, considere upgrade do plano.
