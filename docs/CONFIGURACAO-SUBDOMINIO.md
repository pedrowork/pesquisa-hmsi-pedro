# Configuração de Subdomínio no Nginx do Host

Este guia explica como configurar um subdomínio no Nginx do host para fazer proxy reverso para o container Docker da aplicação.

## 📋 Pré-requisitos

- Nginx instalado no host (já instalado)
- Aplicação rodando no Docker (porta 8080)
- Acesso root/sudo na VPS

## 🚀 Passo a Passo

### 1. Criar arquivo de configuração do Nginx

```bash
# Copiar o arquivo de exemplo para o Nginx
sudo cp nginx-host-example.conf /etc/nginx/sites-available/pesquisa-hmsi

# OU criar diretamente
sudo nano /etc/nginx/sites-available/pesquisa-hmsi
```

### 2. Editar o arquivo de configuração

Edite o arquivo `/etc/nginx/sites-available/pesquisa-hmsi` e ajuste:

- **Substitua `pesquisa.hmsi.local`** pelo seu domínio/subdomínio real
  - Exemplo: `pesquisa.exemplo.com.br`
  - Exemplo: `hmsi.exemplo.com.br`

### 3. Criar symlink no sites-enabled

```bash
# Criar link simbólico
sudo ln -s /etc/nginx/sites-available/pesquisa-hmsi /etc/nginx/sites-enabled/

# Verificar se foi criado
ls -la /etc/nginx/sites-enabled/ | grep pesquisa-hmsi
```

### 4. Testar configuração do Nginx

```bash
# Verificar sintaxe do Nginx
sudo nginx -t

# Se houver erros, corrija e teste novamente
# Se estiver OK, você verá:
# nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
# nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### 5. Recarregar Nginx

```bash
# Recarregar Nginx (sem parar o serviço)
sudo systemctl reload nginx

# OU reiniciar (se necessário)
sudo systemctl restart nginx
```

### 6. Verificar se está funcionando

```bash
# Testar acesso local
curl -I http://localhost

# OU testar pelo domínio (se DNS já estiver configurado)
curl -I http://pesquisa.hmsi.local
```

### 7. Configurar DNS (se necessário)

Se estiver usando um domínio real, configure os registros DNS:

**Tipo A (IPv4):**
```
Nome: pesquisa (ou @)
Valor: IP_DA_VPS
TTL: 3600
```

**OU Tipo CNAME:**
```
Nome: pesquisa
Valor: dominio-principal.com.br
TTL: 3600
```

## 🔒 Configuração SSL/HTTPS (Opcional - Recomendado)

### 1. Instalar Certbot

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Gerar certificado SSL

```bash
# Gerar certificado automaticamente (Certbot configura o Nginx)
sudo certbot --nginx -d pesquisa.hmsi.local

# OU gerar apenas certificado (sem configurar Nginx)
sudo certbot certonly --nginx -d pesquisa.hmsi.local
```

### 3. Configurar HTTPS no Nginx

Se usar `certbot --nginx`, o Certbot já configura automaticamente.

Se não, descomente o bloco `server` HTTPS no arquivo `/etc/nginx/sites-available/pesquisa-hmsi` e ajuste os caminhos dos certificados.

### 4. Recarregar Nginx

```bash
sudo systemctl reload nginx
```

### 5. Renovação automática

O Certbot já configura renovação automática. Para testar:

```bash
# Testar renovação
sudo certbot renew --dry-run
```

## 📝 Exemplo de Configuração Completa

```nginx
server {
    listen 80;
    server_name pesquisa.exemplo.com.br;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 443 ssl http2;
    server_name pesquisa.exemplo.com.br;
    
    ssl_certificate /etc/letsencrypt/live/pesquisa.exemplo.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pesquisa.exemplo.com.br/privkey.pem;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 🔍 Troubleshooting

### Erro: "502 Bad Gateway"

**Causa:** Container Docker não está rodando ou porta incorreta.

**Solução:**
```bash
# Verificar se container está rodando
docker-compose ps

# Verificar se está acessível na porta 8080
curl -I http://127.0.0.1:8080
```

### Erro: "Connection refused"

**Causa:** Container Docker não está acessível em `127.0.0.1:8080`.

**Solução:**
```bash
# Verificar se o container Nginx está saudável
docker-compose ps nginx

# Verificar logs do container
docker-compose logs nginx --tail=50
```

### Nginx não recarrega

**Causa:** Erro de sintaxe na configuração.

**Solução:**
```bash
# Verificar sintaxe
sudo nginx -t

# Ver arquivo de erro
sudo tail -50 /var/log/nginx/error.log
```

## 📚 Referências

- [Nginx Proxy Pass](http://nginx.org/en/docs/http/ngx_http_proxy_module.html#proxy_pass)
- [Certbot Documentation](https://certbot.eff.org/docs/)
- [Let's Encrypt](https://letsencrypt.org/)
