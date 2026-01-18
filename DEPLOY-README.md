# 🚀 Deploy Rápido - Produção

## ⚡ Início Rápido

### 1️⃣ Preparar Ambiente

```bash
# Clonar projeto
git clone <repositorio> pesquisa-hmsi-pedro
cd pesquisa-hmsi-pedro

# Configurar .env
cp .env.example .env
nano .env  # Configure DB_PASSWORD e outras variáveis
```

### 2️⃣ Deploy Automatizado

```bash
chmod +x deploy-prod.sh
./deploy-prod.sh
```

### 3️⃣ Acessar

```
http://IP-DA-VPS:8080
```

**Login padrão:**
- Email: `admin@admin.com`
- Senha: `password`

---

## 📋 Configurações Importantes no .env

```env
APP_ENV=production
APP_DEBUG=false
DB_PASSWORD=SENHA_FORTE_AQUI
APP_URL=https://seu-dominio.com
```

---

## 🔧 Comandos Úteis

```bash
# Ver logs
docker-compose logs -f --tail=50

# Reiniciar
docker-compose restart

# Executar migrations
docker-compose exec php php artisan migrate --force

# Limpar cache
docker-compose exec php php artisan optimize:clear
```

---

## 📚 Documentação Completa

Ver: [`docs/DEPLOY-PRODUCAO.md`](docs/DEPLOY-PRODUCAO.md)

---

## ⚠️ Problemas Comuns

### "Port already in use"
```bash
# Verificar porta ocupada
sudo lsof -i :8080

# Mudar porta no docker-compose.yml
nano docker-compose.yml
```

### Container em loop de restart
```bash
# Ver erro
docker logs pesquisa-hmsi-<container> --tail=100

# Verificar .env configurado
cat .env | grep DB_PASSWORD
```

---

## 📦 Estrutura de Volumes

- `app_storage` - Uploads e logs
- `app_cache` - Cache Laravel
- `app_public` - Assets compilados
- `postgres_data` - Banco de dados
- `redis_data` - Cache Redis

**IMPORTANTE:** Não use bind-mount (`.:/var/www/html`) em produção!

---

## 🔐 Segurança

✅ Portas internas não expostas  
✅ APP_DEBUG=false  
✅ Variáveis sensíveis em .env  
✅ Containers isolados em rede privada

**Recomendado:**
- Configurar firewall (ufw)
- SSL com Let's Encrypt
- Backups automáticos

---

## 🆘 Suporte

Ver logs: `docker-compose logs`  
Status: `docker-compose ps`  
Documentação: `docs/DEPLOY-PRODUCAO.md`
