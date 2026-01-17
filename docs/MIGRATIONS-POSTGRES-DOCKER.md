# Migrations no PostgreSQL via Docker

## ✅ Status Atual

**Todas as 37 migrations foram executadas com sucesso no PostgreSQL!**

## 📋 Resumo das Migrations Executadas

- ✅ 37 migrations aplicadas
- ✅ Todas as tabelas criadas
- ✅ Banco `pesquisa_hmsi` configurado

## 🚀 Como Executar Migrations no Docker

### Opção 1: Script PowerShell (Recomendado)

```powershell
.\scripts\run-migrations-docker.ps1
```

### Opção 2: Comando Manual

```powershell
# 1. Iniciar containers
docker start pesquisa-hmsi-postgres
docker start pesquisa-hmsi-php

# 2. Aguardar PostgreSQL estar pronto
Start-Sleep -Seconds 5

# 3. Executar migrations
docker exec -e DB_CONNECTION=pgsql `
           -e DB_HOST=postgres `
           -e DB_PORT=5432 `
           -e DB_DATABASE=pesquisa_hmsi `
           -e DB_USERNAME=postgres `
           -e DB_PASSWORD=wug2fvh2CJX@jwq5dck `
           pesquisa-hmsi-php php artisan migrate --force
```

### Opção 3: Via docker-compose

```bash
# Iniciar todos os serviços
docker-compose up -d

# Executar migrations
docker-compose exec php php artisan migrate --force
```

## ⚙️ Configuração do PostgreSQL

### Senha do Banco
- **Usuário:** `postgres`
- **Banco:** `pesquisa_hmsi`
- **Host:** `postgres` (dentro do Docker)
- **Porta:** `5432`

### Variáveis de Ambiente no Container PHP

O container PHP precisa ter estas variáveis configuradas:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=pesquisa_hmsi
DB_USERNAME=postgres
DB_PASSWORD=wug2fvh2CJX@jwq5dck
```

## 📝 Verificar Status das Migrations

```powershell
# Ver migrations executadas
docker exec pesquisa-hmsi-postgres psql -U postgres -d pesquisa_hmsi -c "SELECT migration, batch FROM migrations ORDER BY batch, migration;"

# Ver status via artisan
docker exec -e DB_PASSWORD=wug2fvh2CJX@jwq5dck pesquisa-hmsi-php php artisan migrate:status
```

## 🌱 Executar Seeders

Após as migrations, execute os seeders:

```powershell
docker exec -e DB_PASSWORD=wug2fvh2CJX@jwq5dck pesquisa-hmsi-php php artisan db:seed --force
```

## 🔧 Solução de Problemas

### Erro: "password authentication failed"
- **Solução:** Passe `DB_PASSWORD` como variável de ambiente no comando `docker exec`

### Erro: "no password supplied"
- **Solução:** Verifique se o arquivo `.env` no container PHP tem `DB_PASSWORD` configurado

### Erro: Container não está rodando
- **Solução:** Inicie os containers: `docker start pesquisa-hmsi-postgres pesquisa-hmsi-php`

### Verificar containers rodando
```powershell
docker ps --filter "name=pesquisa-hmsi"
```

## ✅ Checklist

- [x] Container PostgreSQL rodando
- [x] Container PHP rodando
- [x] Todas as 37 migrations executadas
- [x] Banco `pesquisa_hmsi` criado
- [ ] Seeders executados (opcional)
