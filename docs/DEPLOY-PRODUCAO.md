# Guia de Deploy em Produção - Pesquisa HMSI

## Pré-requisitos

- VPS com Ubuntu/Debian
- Docker e Docker Compose instalados
- Nginx instalado no host (para proxy reverso)
- Domínio configurado (opcional, para SSL)

## Estrutura de Volumes (Produção)

Em produção, o código vem da imagem Docker (buildada). Os volumes persistem apenas dados dinâmicos:

- `app_storage` - Uploads, logs, cache de arquivos
- `app_cache` - Cache do Laravel (bootstrap)
- `app_public` - Assets públicos (JS/CSS compilados)
- `postgres_data` - Banco de dados PostgreSQL
- `redis_data` - Cache Redis
- `nginx_logs` - Logs do Nginx

## Deploy Passo a Passo

### 1. Clonar o Projeto na VPS

```bash
cd ~/projetos
git clone <seu-repositorio> pesquisa-hmsi-pedro
cd pesquisa-hmsi-pedro
```

### 2. Configurar Variáveis de Ambiente

```bash
# Criar .env
cp .env.example .env

# Editar configurações
nano .env
```

**Configurações obrigatórias no .env:**

```env
APP_NAME="Pesquisa HMSI"
APP_ENV=production
APP_KEY=  # Será gerado automaticamente
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Banco de Dados
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=pesquisa_hmsi
DB_USERNAME=postgres
DB_PASSWORD=SUA_SENHA_FORTE_AQUI

# Cache e Sessões
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Fila
QUEUE_CONNECTION=redis

# Email (SMTP real para produção)
MAIL_MAILER=smtp
MAIL_HOST=smtp.seudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@seudominio.com
MAIL_PASSWORD=senha-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Executar Script de Deploy Automatizado

```bash
# Dar permissão de execução
chmod +x deploy-prod.sh

# Executar
./deploy-prod.sh
```

O script irá:
1. Verificar/criar .env
2. Parar containers existentes
3. Fazer build das imagens
4. Subir containers
5. Gerar APP_KEY
6. Copiar arquivos públicos
7. Executar migrations
8. Criar usuário admin (opcional)
9. Otimizar para produção

### 4. Verificar Status

```bash
# Ver containers rodando
docker-compose ps

# Ver logs em tempo real
docker-compose logs -f --tail=50

# Ver apenas logs do PHP
docker-compose logs php --tail=100
```

## Configurar Proxy Reverso (Nginx Host)

### Criar arquivo de configuração:

```bash
sudo nano /etc/nginx/sites-available/pesquisa-hmsi
```

### Conteúdo:

```nginx
server {
    listen 80;
    server_name seu-dominio.com;

    # Logs
    access_log /var/log/nginx/pesquisa-hmsi-access.log;
    error_log /var/log/nginx/pesquisa-hmsi-error.log;

    # Proxy para container Docker
    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

### Ativar e recarregar:

```bash
sudo ln -s /etc/nginx/sites-available/pesquisa-hmsi /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Configurar SSL (Let's Encrypt)

```bash
# Instalar Certbot (se não estiver instalado)
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Obter certificado SSL
sudo certbot --nginx -d seu-dominio.com

# Renovação automática já configurada via systemd timer
```

## Acessar a Aplicação

- **Sem domínio:** `http://IP-DA-VPS:8080`
- **Com Nginx proxy:** `http://seu-dominio.com`
- **Com SSL:** `https://seu-dominio.com`

**Credenciais padrão do admin:**
- Email: `admin@admin.com`
- Senha: `password` (alterar após primeiro login!)

## Comandos Úteis

### Gerenciamento de Containers

```bash
# Ver status
docker-compose ps

# Ver logs
docker-compose logs -f --tail=50

# Reiniciar um serviço específico
docker-compose restart php
docker-compose restart nginx

# Parar tudo
docker-compose down

# Subir tudo novamente
docker-compose up -d
```

### Laravel Artisan

```bash
# Executar comandos artisan
docker-compose exec php php artisan <comando>

# Exemplos:
docker-compose exec php php artisan migrate --force
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan queue:restart
```

### Backup do Banco de Dados

```bash
# Backup
docker-compose exec postgres pg_dump -U postgres pesquisa_hmsi > backup-$(date +%Y%m%d).sql

# Restaurar
cat backup-20260118.sql | docker-compose exec -T postgres psql -U postgres pesquisa_hmsi
```

### Ver Recursos dos Containers

```bash
docker stats
```

## Troubleshooting

### Container não inicia

```bash
# Ver logs detalhados
docker-compose logs <nome-do-container> --tail=100

# Verificar healthcheck
docker inspect pesquisa-hmsi-php | grep -A 10 Health
```

### Erro "vendor/autoload.php not found"

Isso indica que os volumes estão sobrescrevendo o código da imagem. Verifique o docker-compose.yml - **não** deve ter bind-mount `.:/var/www/html` em produção.

### Porta já em uso

```bash
# Ver quem está usando a porta
sudo lsof -i :8080

# Mudar a porta no docker-compose.yml
nano docker-compose.yml
# Altere "8080:80" para outra porta disponível
```

### Problemas de permissão em storage/

```bash
# Ajustar permissões dentro do container
docker-compose exec php chown -R www-data:www-data /var/www/html/storage
docker-compose exec php chmod -R 755 /var/www/html/storage
```

## Atualizar a Aplicação

```bash
# 1. Fazer pull das últimas mudanças
git pull origin main

# 2. Rebuild da imagem
docker-compose build --no-cache php

# 3. Parar e remover containers antigos
docker-compose down

# 4. Subir com nova imagem
docker-compose up -d

# 5. Executar migrations (se houver)
docker-compose exec php php artisan migrate --force

# 6. Limpar cache
docker-compose exec php php artisan optimize:clear
docker-compose exec php php artisan optimize
```

## Monitoramento

### Logs

```bash
# Logs em tempo real
docker-compose logs -f

# Logs do Laravel (dentro do container)
docker-compose exec php tail -f storage/logs/laravel.log
```

### Performance

```bash
# CPU e memória dos containers
docker stats

# Espaço em disco dos volumes
docker system df -v
```

## Segurança

### ✅ Boas Práticas Implementadas

- ✅ Portas do banco e redis não expostas externamente
- ✅ Containers isolados em rede privada
- ✅ Código vem da imagem (imutável)
- ✅ Variáveis sensíveis via .env
- ✅ APP_DEBUG=false em produção

### 🔒 Recomendações Adicionais

1. **Firewall:**
   ```bash
   sudo ufw allow 22/tcp
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```

2. **Fail2ban:** Instalar para proteção contra brute force

3. **Backups Automáticos:** Configurar cron para backup diário do banco

4. **Monitoramento:** Considerar Sentry, New Relic ou similar

## Suporte

Em caso de problemas, verificar:
1. Logs dos containers: `docker-compose logs`
2. Status dos healthchecks: `docker-compose ps`
3. Arquivo .env configurado corretamente
4. Portas não estão em conflito
