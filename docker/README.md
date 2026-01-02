# Docker Setup - Pesquisa HMSI

## 🐳 Estrutura Docker

Este projeto usa Docker Compose com os seguintes serviços:

- **Nginx**: Servidor web e reverse proxy
- **PHP-FPM**: Processamento PHP/Laravel
- **PostgreSQL**: Banco de dados
- **Redis**: Cache e sessões (opcional)
- **Queue**: Worker para processar filas

## 🚀 Como Usar

### 1. Configurar Variáveis de Ambiente

```bash
cp .env.example .env
# Edite o .env com suas configurações
```

### 2. Construir e Iniciar Containers

```bash
docker-compose up -d --build
```

### 3. Instalar Dependências e Configurar

```bash
# Executar dentro do container PHP
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate
docker-compose exec php php artisan db:seed
```

### 4. Configurar SSL (Produção)

```bash
# Criar diretório para certificados
mkdir -p nginx/ssl

# Usar Certbot para obter certificados
# (ajustar domínio e email)
docker run -it --rm \
  -v $(pwd)/nginx/ssl:/etc/letsencrypt \
  certbot/certbot certonly \
  --standalone \
  -d seudominio.com.br \
  -d www.seudominio.com.br
```

### 5. Acessar Aplicação

- **HTTP**: http://localhost (redireciona para HTTPS)
- **HTTPS**: https://localhost (após configurar SSL)

## 📝 Comandos Úteis

```bash
# Ver logs
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f postgres

# Executar comandos Artisan
docker-compose exec php php artisan migrate
docker-compose exec php php artisan cache:clear

# Acessar shell do container
docker-compose exec php sh
docker-compose exec postgres psql -U postgres -d pesquisa_hmsi

# Parar containers
docker-compose down

# Parar e remover volumes (CUIDADO: apaga dados!)
docker-compose down -v

# Rebuild após mudanças
docker-compose up -d --build
```

## 🔧 Configuração Nginx

O Nginx está configurado para:
- ✅ Servir arquivos estáticos diretamente (rápido)
- ✅ Comprimir respostas (Gzip)
- ✅ Cache de assets
- ✅ SSL/HTTPS
- ✅ Headers de segurança
- ✅ Proxy para PHP-FPM

## ⚠️ Importante

1. **SSL**: Configure certificados SSL antes de usar em produção
2. **Permissões**: Garanta que `storage` e `bootstrap/cache` tenham permissões corretas
3. **Backup**: Configure backup automático do PostgreSQL
4. **Variáveis**: Nunca commite o arquivo `.env`

