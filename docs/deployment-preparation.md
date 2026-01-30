# Guia de Preparação para Deploy - Pesquisa HMSI

Este guia contém uma lista de verificação para preparar o ambiente de produção, focando em segurança e performance.

## 1. Requisitos do Servidor

Certifique-se de que o servidor atende aos requisitos mínimos:

- **PHP**: >= 8.2 (Recomendado 8.3 ou superior se possível)
- **Extensões PHP**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO_PgSQL, Tokenizer, XML, Redis.
- **Banco de Dados**: PostgreSQL 14+
- **Cache/Sessão**: Redis 6+
- **Servidor Web**: Nginx (recomendado) ou Apache
- **Gerenciador de Processos**: Supervisor (para filas e worker)
- **Node.js**: >= 18 (apenas para build dos assets)

## 2. Configuração de Ambiente (.env)

No ambiente de produção, garanta que as seguintes variáveis estejam configuradas corretamente:

### Segurança (Crítico)
- `.env`: `APP_ENV=production`
- `.env`: `APP_DEBUG=false` (NUNCA deixe `true` em produção)
- `.env`: `APP_KEY` (Gere uma nova se necessário: `php artisan key:generate`)

### URLs
- `.env`: `APP_URL=https://pesquisa.hmsi.com.br` (Use HTTPS)
- `.env`: `ASSET_URL=https://pesquisa.hmsi.com.br`

### Banco de Dados
- `.env`: `DB_CONNECTION=pgsql`
- `.env`: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Use senhas fortes e complexas)

### Redis (Cache/Queue/Session)
- `.env`: `REDIS_PASSWORD` (Defina uma senha forte no arquivo de config do Redis e no .env)
- `.env`: `CACHE_STORE=redis`
- `.env`: `QUEUE_CONNECTION=redis`
- `.env`: `SESSION_DRIVER=redis`

### Mail
- Configure um serviço de e-mail transacional confiável (Resend, SES, Mailgun). Evite usar SMTP do Google/Gmail em produção para alto volume.

## 3. Passos de Deploy

Execute estes passos na ordem durante o deploy:

### Build do Frontend
1. Instale dependências: `npm ci`
2. Compile os assets para produção: `npm run build`

### Configuração do Backend
1. Instale dependências PHP (sem dev):
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. Otimizações do Laravel (Execute a cada deploy):
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```
   *Nota: Se fizer alterações no .env, lembre-se de limpar o cache primeiro ou rodar `config:cache` novamente.*

3. Banco de Dados:
   ```bash
   php artisan migrate --force
   ```

4. Permissões de Arquivo:
   Garanta que o servidor web (ex: `www-data` ou `nginx`) tenha permissão de escrita em:
   - `storage/`
   - `bootstrap/cache/`

## 4. Segurança Adicional

### Nginx / Apache
- **Forçar HTTPS**: Redirecione todo tráfego HTTP para HTTPS.
- **Cabeçalhos de Segurança (Security Headers)**: Adicione headers como HSTS, X-Frame-Options, X-Content-Type-Options.
- **Desabilitar Listagem de Diretórios**.
- **Proteger arquivos sensíveis**: Bloqueie acesso direto a `.env`, `.git`, `composer.json`, `package.json`.

Exemplo de bloqueio Nginx:
```nginx
location ~ /\.(?!well-known).* {
    deny all;
}
```

### Firewall / Rede
- Liberar apenas as portas necessárias (80, 443, 22).
- Banco de Dados e Redis devem estar acessíveis apenas localmente (localhost) ou via rede privada (VPC), nunca expostos publicamente na internet.

## 5. Manutenção e Monitoramento
- Configure o **Supervisor** para manter a fila (`php artisan queue:work`) rodando.
- Configure o **Scheduler** no cron do sistema:
  ```bash
  * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
  ```
- Configure backups automáticos do banco de dados.
