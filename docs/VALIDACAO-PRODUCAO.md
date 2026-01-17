# Validação de Configurações para Produção

## ⚠️ Configurações Críticas - Bloqueantes

### 1. Arquivo .env deve conter:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br
APP_KEY=base64:... (gerada com: php artisan key:generate)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pesquisa_hmsi
DB_USERNAME=pesquisa_user
DB_PASSWORD=senha_forte_minimo_12_caracteres

SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### 2. Validação Automática

Execute o comando de validação:

```bash
php artisan config:validate-production
```

### 3. Checklist Manual

- [ ] `APP_ENV=production` (não `local` ou `development`)
- [ ] `APP_DEBUG=false` (não `true`)
- [ ] `APP_URL` começa com `https://`
- [ ] `APP_KEY` está definida e começa com `base64:`
- [ ] `DB_CONNECTION=mysql` ou `pgsql` (não `sqlite`)
- [ ] Credenciais de banco configuradas (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] `SESSION_SECURE_COOKIE=true` (obrigatório com HTTPS)
- [ ] Senha do banco com pelo menos 12 caracteres

### 4. Comandos Rápidos

```bash
# Gerar APP_KEY
php artisan key:generate

# Verificar configurações
php artisan config:show app.env
php artisan config:show app.debug
php artisan config:show database.default

# Limpar e recriar cache (após mudanças no .env)
php artisan config:clear
php artisan config:cache
```

### 5. Erros Comuns

**Erro:** `APP_ENV=local`
- **Solução:** Alterar para `APP_ENV=production`

**Erro:** `APP_DEBUG=true`
- **Solução:** Alterar para `APP_DEBUG=false`

**Erro:** `DB_CONNECTION=sqlite`
- **Solução:** Alterar para `DB_CONNECTION=mysql` e configurar credenciais MySQL

**Erro:** `APP_URL=http://...` (sem HTTPS)
- **Solução:** Alterar para `APP_URL=https://...` e configurar SSL

**Erro:** `SESSION_SECURE_COOKIE=false` com HTTPS
- **Solução:** Alterar para `SESSION_SECURE_COOKIE=true`
