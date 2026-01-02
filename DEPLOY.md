# Guia de Deploy em Produção - Pesquisa HMSI

Este documento contém as instruções completas para fazer o deploy da aplicação em uma VPS.

## 📋 Pré-requisitos

### Servidor VPS
- **Sistema Operacional**: Ubuntu 22.04 LTS ou superior (recomendado)
- **RAM**: Mínimo 2GB (recomendado 4GB+)
- **Disco**: Mínimo 20GB de espaço livre
- **Acesso**: SSH com permissões de root ou sudo

### Software Necessário
- PHP 8.2 ou superior
- Composer 2.x
- Node.js 18+ e npm
- Nginx
- MySQL/MariaDB ou PostgreSQL (para produção)
- Certbot (para SSL/HTTPS)

## 🚀 Passo a Passo do Deploy

### 1. Preparar o Servidor

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependências básicas
sudo apt install -y software-properties-common curl git unzip

# Instalar PHP 8.2 e extensões necessárias
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-sqlite3

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar Nginx
sudo apt install -y nginx

# Instalar MySQL (ou PostgreSQL)
sudo apt install -y mysql-server
# OU
# sudo apt install -y postgresql postgresql-contrib

# Instalar Certbot para SSL
sudo apt install -y certbot python3-certbot-nginx
```

### 2. Configurar Banco de Dados

```bash
# Acessar MySQL
sudo mysql -u root -p

# Criar banco de dados e usuário
CREATE DATABASE pesquisa_hmsi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pesquisa_user'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON pesquisa_hmsi.* TO 'pesquisa_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clonar e Configurar a Aplicação

```bash
# Criar diretório da aplicação
sudo mkdir -p /var/www
cd /var/www

# Clonar repositório (ou fazer upload via SCP/SFTP)
sudo git clone https://seu-repositorio.git pesquisa-hmsi-pedro
# OU fazer upload do projeto via SCP/SFTP

# Definir permissões
sudo chown -R www-data:www-data /var/www/pesquisa-hmsi-pedro
cd /var/www/pesquisa-hmsi-pedro
sudo chmod -R 755 storage bootstrap/cache
```

### 4. Configurar Variáveis de Ambiente

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar arquivo .env
nano .env
```

**Configurações importantes no `.env`:**

```env
APP_NAME="Pesquisa HMSI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pesquisa_hmsi
DB_USERNAME=pesquisa_user
DB_PASSWORD=sua_senha_segura

# Session (importante para HTTPS)
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Mail (configurar serviço real)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_senha_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@seudominio.com.br"
MAIL_FROM_NAME="Pesquisa HMSI"
```

### 5. Instalar Dependências e Build

```bash
# Instalar dependências PHP
composer install --no-dev --optimize-autoloader

# Gerar chave da aplicação
php artisan key:generate

# Instalar dependências Node.js
npm install

# Build dos assets para produção
npm run build

# Executar migrações
php artisan migrate --force

# Popular banco com dados iniciais (opcional)
php artisan db:seed --force

# Otimizar aplicação
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 6. Configurar Nginx

```bash
# Copiar configuração
sudo cp nginx.conf /etc/nginx/sites-available/pesquisa-hmsi

# Editar e ajustar domínio e caminhos
sudo nano /etc/nginx/sites-available/pesquisa-hmsi

# Criar link simbólico
sudo ln -s /etc/nginx/sites-available/pesquisa-hmsi /etc/nginx/sites-enabled/

# Remover configuração padrão (opcional)
sudo rm /etc/nginx/sites-enabled/default

# Testar configuração
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

### 7. Configurar SSL com Let's Encrypt

```bash
# Obter certificado SSL
sudo certbot --nginx -d seudominio.com.br -d www.seudominio.com.br

# Renovação automática (já configurado por padrão)
sudo certbot renew --dry-run
```

### 8. Configurar Supervisor (para Queue)

```bash
# Instalar Supervisor
sudo apt install -y supervisor

# Criar arquivo de configuração
sudo nano /etc/supervisor/conf.d/pesquisa-hmsi-queue.conf
```

**Conteúdo do arquivo:**

```ini
[program:pesquisa-hmsi-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pesquisa-hmsi-pedro/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/pesquisa-hmsi-pedro/storage/logs/queue.log
stopwaitsecs=3600
```

```bash
# Recarregar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pesquisa-hmsi-queue:*
```

### 9. Configurar Cron (para tarefas agendadas)

```bash
# Editar crontab
sudo crontab -e -u www-data

# Adicionar linha (ajustar caminho se necessário)
* * * * * cd /var/www/pesquisa-hmsi-pedro && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Configurar Permissões Finais

```bash
cd /var/www/pesquisa-hmsi-pedro

# Permissões de storage e cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Se usar SQLite, garantir permissões
sudo chmod 664 database/database.sqlite
sudo chown www-data:www-data database/database.sqlite
```

## 🔒 Segurança Adicional

### Firewall (UFW)

```bash
# Habilitar firewall
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Otimizações de Segurança

```bash
# Desabilitar informações do servidor no PHP
sudo nano /etc/php/8.2/fpm/php.ini
# Alterar: expose_php = Off

# Reiniciar PHP-FPM
sudo systemctl restart php8.2-fpm
```

## 📊 Monitoramento

### Verificar Status dos Serviços

```bash
# Status Nginx
sudo systemctl status nginx

# Status PHP-FPM
sudo systemctl status php8.2-fpm

# Status MySQL
sudo systemctl status mysql

# Status Supervisor
sudo supervisorctl status
```

### Logs

```bash
# Logs da aplicação
tail -f /var/www/pesquisa-hmsi-pedro/storage/logs/laravel.log

# Logs do Nginx
sudo tail -f /var/log/nginx/pesquisa-hmsi-error.log

# Logs do PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

## 🔄 Atualizações Futuras

### Processo de Atualização

```bash
cd /var/www/pesquisa-hmsi-pedro

# Fazer backup do banco de dados
mysqldump -u pesquisa_user -p pesquisa_hmsi > backup_$(date +%Y%m%d).sql

# Atualizar código
git pull origin main
# OU fazer upload dos novos arquivos

# Instalar/atualizar dependências
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Executar migrações
php artisan migrate --force

# Limpar e recriar cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar serviços
sudo supervisorctl restart pesquisa-hmsi-queue:*
sudo systemctl reload php8.2-fpm
```

## 🐛 Troubleshooting

### Erro 502 Bad Gateway
- Verificar se PHP-FPM está rodando: `sudo systemctl status php8.2-fpm`
- Verificar permissões: `sudo chown -R www-data:www-data /var/www/pesquisa-hmsi-pedro`

### Erro de Permissão
- Ajustar permissões: `sudo chmod -R 775 storage bootstrap/cache`
- Verificar proprietário: `sudo chown -R www-data:www-data storage bootstrap/cache`

### Assets não carregam
- Rebuild: `npm run build`
- Limpar cache: `php artisan view:clear`

### Erro de Conexão com Banco
- Verificar credenciais no `.env`
- Testar conexão: `mysql -u pesquisa_user -p pesquisa_hmsi`

## 📝 Checklist Final

- [ ] Servidor atualizado e dependências instaladas
- [ ] Banco de dados criado e configurado
- [ ] Aplicação clonada/uploadada
- [ ] Arquivo `.env` configurado corretamente
- [ ] Dependências instaladas (Composer e npm)
- [ ] Assets compilados (`npm run build`)
- [ ] Migrações executadas
- [ ] Nginx configurado e testado
- [ ] SSL configurado (HTTPS)
- [ ] Supervisor configurado (Queue)
- [ ] Cron configurado
- [ ] Permissões ajustadas
- [ ] Firewall configurado
- [ ] Testes de acesso realizados
- [ ] Backup inicial criado

## 🆘 Suporte

Em caso de problemas, verifique:
1. Logs da aplicação: `storage/logs/laravel.log`
2. Logs do Nginx: `/var/log/nginx/pesquisa-hmsi-error.log`
3. Status dos serviços: `systemctl status`
4. Permissões de arquivos e diretórios

