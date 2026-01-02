# Configuração de Auto-Restart - Pesquisa HMSI

## ✅ Configurações Aplicadas

### 1. Docker Compose - Restart Policy

Todos os serviços estão configurados com `restart: always`:
- ✅ **Nginx**: Reinicia sempre
- ✅ **PHP-FPM**: Reinicia sempre
- ✅ **PostgreSQL**: Reinicia sempre
- ✅ **Redis**: Reinicia sempre
- ✅ **Queue Worker**: Reinicia sempre

### 2. Health Checks

Health checks configurados para garantir que os serviços estejam funcionando:
- **Nginx**: Verifica endpoint `/up`
- **PHP-FPM**: Verifica processo PHP-FPM
- **PostgreSQL**: Verifica conexão com `pg_isready`
- **Redis**: Verifica com `redis-cli ping`
- **Queue**: Verifica processo `queue:work`

### 3. Dependências com Condições

Os serviços aguardam dependências ficarem saudáveis antes de iniciar:
- Nginx aguarda PHP-FPM estar saudável
- PHP-FPM aguarda PostgreSQL estar saudável
- Queue aguarda PostgreSQL e Redis estarem prontos

## 🚀 Como Configurar Auto-Restart no Sistema

### Opção 1: Systemd Service (Recomendado para Linux)

```bash
# Copiar arquivo de serviço
sudo cp docker/pesquisa-hmsi.service /etc/systemd/system/

# Recarregar systemd
sudo systemctl daemon-reload

# Habilitar serviço (inicia automaticamente no boot)
sudo systemctl enable pesquisa-hmsi.service

# Iniciar serviço agora
sudo systemctl start pesquisa-hmsi.service

# Verificar status
sudo systemctl status pesquisa-hmsi.service
```

**Ajustar caminho no arquivo de serviço:**
Edite `/etc/systemd/system/pesquisa-hmsi.service` e altere:
```
WorkingDirectory=/var/www/pesquisa-hmsi-pedro
```
Para o caminho real do seu projeto.

### Opção 2: Script de Inicialização

```bash
# Tornar script executável
chmod +x docker/start.sh

# Adicionar ao crontab para iniciar no boot
crontab -e
# Adicionar linha:
@reboot /caminho/para/projeto/docker/start.sh
```

### Opção 3: Docker Restart Policy (Já Configurado)

O `restart: always` no docker-compose.yml já garante que:
- Containers reiniciem automaticamente se pararem
- Containers iniciem automaticamente quando o Docker iniciar

**Para habilitar Docker no boot:**
```bash
sudo systemctl enable docker
```

## 🔄 Comandos Úteis

### Verificar Status
```bash
docker-compose ps
docker-compose logs -f
```

### Reiniciar Serviços
```bash
# Reiniciar todos
docker-compose restart

# Reiniciar serviço específico
docker-compose restart nginx
docker-compose restart php
```

### Ver Logs
```bash
# Todos os serviços
docker-compose logs -f

# Serviço específico
docker-compose logs -f nginx
docker-compose logs -f php
```

### Parar e Iniciar
```bash
# Parar
docker-compose down

# Iniciar
docker-compose up -d
```

## 🛡️ Garantias de Auto-Restart

### 1. Reinicialização do Servidor
- ✅ Docker inicia automaticamente (se habilitado)
- ✅ Systemd service inicia containers (se configurado)
- ✅ Containers com `restart: always` iniciam automaticamente

### 2. Falha de Container
- ✅ Container reinicia automaticamente
- ✅ Health checks verificam saúde
- ✅ Dependências aguardam serviços ficarem prontos

### 3. Falha de Serviço
- ✅ Nginx reinicia se crashar
- ✅ PHP-FPM reinicia se crashar
- ✅ PostgreSQL reinicia se crashar
- ✅ Redis reinicia se crashar
- ✅ Queue worker reinicia se crashar

## 📊 Monitoramento

### Verificar Health Checks
```bash
docker inspect pesquisa-hmsi-nginx | grep -A 10 Health
docker inspect pesquisa-hmsi-php | grep -A 10 Health
docker inspect pesquisa-hmsi-postgres | grep -A 10 Health
```

### Verificar Restart Count
```bash
docker-compose ps
# A coluna "Restart" mostra quantas vezes reiniciou
```

## ⚠️ Troubleshooting

### Container não reinicia
1. Verificar logs: `docker-compose logs nome-do-servico`
2. Verificar restart policy: `docker inspect nome-container | grep RestartPolicy`
3. Verificar Docker: `sudo systemctl status docker`

### Serviço não inicia no boot
1. Verificar systemd: `sudo systemctl status pesquisa-hmsi.service`
2. Verificar Docker: `sudo systemctl status docker`
3. Verificar logs: `sudo journalctl -u pesquisa-hmsi.service`

### Health check falhando
1. Verificar logs do serviço
2. Verificar dependências (PostgreSQL, Redis)
3. Ajustar interval/timeout no docker-compose.yml se necessário

## ✅ Checklist de Configuração

- [x] Docker Compose com `restart: always`
- [x] Health checks configurados
- [x] Dependências com condições
- [ ] Systemd service configurado (opcional)
- [ ] Docker habilitado no boot: `sudo systemctl enable docker`
- [ ] Testar reinicialização do servidor

## 🎯 Resultado Final

Com essas configurações:
- ✅ Aplicação reinicia automaticamente após reinicialização do servidor
- ✅ Containers reiniciam automaticamente se crasharem
- ✅ Serviços aguardam dependências ficarem prontas
- ✅ Health checks garantem que serviços estejam funcionando

**Sua aplicação está configurada para alta disponibilidade!** 🚀

