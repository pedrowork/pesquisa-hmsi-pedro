# Resumo das Alterações - Deploy em Produção

## ✅ Problemas Resolvidos

### 1. **Porta 80 ocupada pelo Nginx do host**
- ❌ Antes: `"80:80"` e `"443:443"`
- ✅ Agora: `"8080:80"` e `"8443:443"`
- **Solução:** O container Nginx roda nas portas 8080/8443. Configure o Nginx do host para fazer proxy reverso.

### 2. **Erro "vendor/autoload.php not found" no container queue**
- ❌ Antes: Bind-mount `.:/var/www/html` sobrescrevia o vendor/ da imagem
- ✅ Agora: Volumes nomeados para storage e cache, código vem da imagem
- **Resultado:** Container queue funciona corretamente

### 3. **Conflito de portas PostgreSQL e Redis**
- ❌ Antes: Portas 5432 e 6379 expostas (conflitavam com outros containers)
- ✅ Agora: Portas não expostas externamente (mais seguro)
- **Resultado:** Containers isolados na rede interna

---

## 📝 Arquivos Modificados

### 1. **docker-compose.yml**

#### Alterações principais:

**Nginx:**
```yaml
ports:
  - "8080:80"   # Evita conflito com Nginx do host
  - "8443:443"
volumes:
  - app_public:/var/www/html/public:ro  # Volume nomeado
  - nginx_logs:/var/log/nginx           # Logs persistentes
```

**PHP:**
```yaml
volumes:
  # Removido: .:/var/www/html (bind-mount problemático)
  # Adicionado: volumes nomeados
  - app_storage:/var/www/html/storage
  - app_cache:/var/www/html/bootstrap/cache
  - app_public:/var/www/html/public
```

**Queue:**
```yaml
volumes:
  # Removido: .:/var/www/html
  # Mantido apenas storage compartilhado
  - app_storage:/var/www/html/storage
```

**PostgreSQL e Redis:**
```yaml
# Portas comentadas/removidas (não expostas externamente)
# Mais seguro - acesso apenas via rede interna do Docker
```

**Volumes adicionados:**
```yaml
volumes:
  postgres_data:
  redis_data:
  app_storage:    # Uploads, logs
  app_cache:      # Bootstrap cache
  app_public:     # Assets compilados
  nginx_logs:     # Logs Nginx
```

---

## 🆕 Arquivos Criados

### 1. **deploy-prod.sh**
Script automatizado de deploy que:
- ✅ Verifica/cria .env
- ✅ Faz build das imagens
- ✅ Sobe containers
- ✅ Gera APP_KEY
- ✅ Copia arquivos públicos
- ✅ Executa migrations
- ✅ Cria usuário admin
- ✅ Otimiza para produção

**Uso:**
```bash
chmod +x deploy-prod.sh
./deploy-prod.sh
```

### 2. **docs/DEPLOY-PRODUCAO.md**
Documentação completa incluindo:
- ✅ Pré-requisitos
- ✅ Passo a passo detalhado
- ✅ Configuração de proxy reverso (Nginx)
- ✅ Configuração de SSL (Let's Encrypt)
- ✅ Troubleshooting
- ✅ Comandos úteis
- ✅ Backup e atualização

### 3. **DEPLOY-README.md**
Guia rápido de referência com:
- ✅ Início rápido (3 passos)
- ✅ Configurações essenciais
- ✅ Comandos mais usados
- ✅ Problemas comuns

---

## 🔄 Fluxo de Deploy Agora

### Antes (problemático):
```
git clone → composer install (no host) → docker-compose up
         ↓
   Faltava extensões PHP no host
   Bind-mount sobrescrevia vendor/
   Portas em conflito
```

### Agora (correto):
```
git clone → ./deploy-prod.sh → Pronto!
         ↓
   Build da imagem (com vendor/)
   Volumes nomeados (dados persistentes)
   Portas ajustadas
   Tudo automatizado
```

---

## 🚀 Próximos Passos na VPS

### 1. **Fazer git pull das alterações**
```bash
cd ~/projetos/pesquisa-hmsi-pedro
git pull origin main
```

### 2. **Criar arquivo .env**
```bash
cp .env.example .env
nano .env
# Configure DB_PASSWORD e outras variáveis
```

### 3. **Executar deploy**
```bash
chmod +x deploy-prod.sh
./deploy-prod.sh
```

### 4. **Configurar proxy reverso (opcional mas recomendado)**
```bash
# Ver guia em docs/DEPLOY-PRODUCAO.md
sudo nano /etc/nginx/sites-available/pesquisa-hmsi
```

### 5. **Acessar aplicação**
```
http://IP-DA-VPS:8080
```

---

## 📊 Comparação

| Aspecto | Antes | Depois |
|---------|-------|--------|
| **Porta Nginx** | 80 (conflito) | 8080 (OK) |
| **Vendor/** | Faltava no queue | ✅ Incluído na imagem |
| **Código fonte** | Bind-mount | ✅ Da imagem (imutável) |
| **Dados persistentes** | Misturado | ✅ Volumes nomeados |
| **Segurança** | Portas expostas | ✅ Isolado |
| **Deploy** | Manual, propenso a erros | ✅ Script automatizado |
| **Documentação** | Dispersa | ✅ Completa e organizada |

---

## ✨ Benefícios

1. **Confiabilidade:** Código vem da imagem buildada, não do host
2. **Segurança:** Portas internas não expostas
3. **Simplicidade:** Script automatizado de deploy
4. **Persistência:** Volumes nomeados para dados importantes
5. **Portabilidade:** Fácil mover entre servidores
6. **Manutenção:** Documentação completa

---

## ⚠️ Importante

- **Não use bind-mount em produção!** O código deve vir da imagem
- **Configure o .env corretamente** antes do primeiro deploy
- **Use proxy reverso** para domínio com SSL
- **Faça backups** do banco de dados regularmente

---

Data: 2026-01-18
Status: ✅ Pronto para deploy em produção
