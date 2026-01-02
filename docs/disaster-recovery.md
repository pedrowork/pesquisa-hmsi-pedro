# Plano de Recuperação de Desastre

## 1. Objetivos de Recuperação

### RTO (Recovery Time Objective)
- **Crítico**: 4 horas
- **Importante**: 24 horas
- **Normal**: 72 horas

### RPO (Recovery Point Objective)
- **Crítico**: 1 hora (máximo de perda de dados)
- **Importante**: 6 horas
- **Normal**: 24 horas

## 2. Cenários de Desastre

### Perda de Dados do Banco de Dados
- Corrupção de dados
- Exclusão acidental
- Falha de hardware
- Ataque de ransomware

### Perda de Arquivos
- Exclusão acidental
- Corrupção de storage
- Ataque malicioso

### Falha de Infraestrutura
- Falha de servidor
- Falha de rede
- Falha de storage

### Comprometimento de Segurança
- Acesso não autorizado
- Vazamento de dados
- Comprometimento completo

## 3. Procedimentos de Backup

### Backup do Banco de Dados

**Frequência**
- Completo: Diário às 02:00
- Incremental: A cada 6 horas
- Logs de transação: Contínuo

**Comando**
```bash
php artisan db:backup --encrypt
```

**Localização**
- Primário: `storage/app/backups/`
- Secundário: Servidor remoto (S3, etc.)
- Retenção: 30 dias

**Validação**
- Verificar integridade após backup
- Testar restauração mensalmente
- Documentar resultados

### Backup de Arquivos

**Frequência**
- Completo: Semanal
- Incremental: Diário

**Arquivos Críticos**
- `storage/app/public/` (uploads de usuários)
- `storage/app/profile-photos/` (fotos de perfil)
- Configurações customizadas

**Comando**
```bash
# Backup manual
tar -czf backup_files_$(date +%Y%m%d).tar.gz storage/app/public/
```

### Backup de Configuração

**Itens**
- Arquivo `.env` (sem senhas)
- Configurações customizadas
- Chaves de criptografia (em cofre seguro)

**Frequência**
- Sempre que houver mudanças
- Backup antes de atualizações

## 4. Procedimentos de Restauração

### Restauração do Banco de Dados

**Pré-requisitos**
1. Identificar backup mais recente antes do incidente
2. Verificar integridade do backup
3. Preparar ambiente de teste (se possível)

**Procedimento**

```bash
# 1. Parar aplicação
php artisan down

# 2. Fazer backup do estado atual (se possível)
php artisan db:backup

# 3. Restaurar backup
mysql -u [user] -p [database] < backup_[timestamp].sql

# 4. Verificar integridade
php artisan db:check-integrity

# 5. Limpar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Reativar aplicação
php artisan up
```

**Validação**
- Verificar dados críticos
- Testar funcionalidades principais
- Verificar integridade referencial

### Restauração de Arquivos

```bash
# 1. Parar aplicação
php artisan down

# 2. Restaurar arquivos
tar -xzf backup_files_[timestamp].tar.gz -C /

# 3. Ajustar permissões
chmod -R 755 storage/
chown -R www-data:www-data storage/

# 4. Reativar aplicação
php artisan up
```

### Restauração Completa

**Ordem de Restauração**
1. Infraestrutura (servidor, rede)
2. Sistema operacional e dependências
3. Aplicação Laravel
4. Banco de dados
5. Arquivos de usuários
6. Configurações
7. Validação completa

## 5. Procedimentos por Cenário

### Cenário 1: Perda Total do Servidor

**Passos**
1. Provisionar novo servidor
2. Instalar dependências (PHP, MySQL, etc.)
3. Clonar repositório de código
4. Restaurar configurações
5. Restaurar banco de dados
6. Restaurar arquivos
7. Configurar DNS/networking
8. Validar funcionamento

**Tempo Estimado**: 4-6 horas

### Cenário 2: Corrupção de Banco de Dados

**Passos**
1. Identificar último backup válido
2. Parar aplicação
3. Fazer backup do estado atual (para análise)
4. Restaurar backup válido
5. Aplicar logs de transação (se disponível)
6. Validar integridade
7. Reativar aplicação

**Tempo Estimado**: 1-2 horas

### Cenário 3: Comprometimento de Segurança

**Passos**
1. Isolar sistemas comprometidos
2. Avaliar extensão do comprometimento
3. Limpar backdoors/vírus
4. Rotacionar todas as chaves
5. Invalidar todas as sessões
6. Forçar troca de senhas
7. Restaurar de backup limpo (se necessário)
8. Implementar patches de segurança
9. Validar segurança
10. Reativar serviços

**Tempo Estimado**: 4-8 horas

### Cenário 4: Perda de Arquivos

**Passos**
1. Identificar arquivos perdidos
2. Restaurar do backup
3. Verificar integridade
4. Ajustar permissões
5. Validar funcionamento

**Tempo Estimado**: 1-2 horas

## 6. Validação Pós-Restauração

### Checklist de Validação

**Funcionalidades Críticas**
- [ ] Login de usuários funciona
- [ ] Autenticação 2FA funciona
- [ ] CRUD de dados principais funciona
- [ ] Upload de arquivos funciona
- [ ] Relatórios geram corretamente

**Integridade de Dados**
- [ ] Dados críticos presentes
- [ ] Relacionamentos intactos
- [ ] Índices funcionando
- [ ] Constraints respeitadas

**Performance**
- [ ] Tempo de resposta aceitável
- [ ] Queries otimizadas
- [ ] Cache funcionando

**Segurança**
- [ ] SSL/TLS funcionando
- [ ] Headers de segurança presentes
- [ ] Autenticação funcionando
- [ ] Autorização funcionando

## 7. Comunicação Durante DR

### Stakeholders

**Interno**
- Equipe técnica: Imediato
- Gestão: Dentro de 1 hora
- Usuários internos: Dentro de 2 horas

**Externo**
- Usuários: Conforme impacto
- Clientes: Se aplicável
- Autoridades: Se exigido por lei

### Templates de Comunicação

**Notificação Interna**
```
🚨 PROCEDIMENTO DE RECUPERAÇÃO DE DESASTRE ATIVADO

Tipo: [tipo de desastre]
Início: [timestamp]
Status: [em andamento/concluído]
ETA: [tempo estimado]
Impacto: [descrição]

Equipe técnica trabalhando na resolução.
```

**Notificação a Usuários**
```
Assunto: Manutenção de Sistema

Prezados usuários,

Estamos realizando manutenção de emergência no sistema.
Serviços podem estar temporariamente indisponíveis.

Tempo estimado: [X horas]
Atualizações: [link para status]

Agradecemos sua compreensão.
```

## 8. Testes de Recuperação

### Frequência
- **Teste Completo**: Trimestral
- **Teste Parcial**: Mensal
- **Validação de Backup**: Semanal

### Tipos de Teste

**Teste Completo**
- Simular perda total
- Executar procedimento completo
- Validar todos os sistemas
- Documentar resultados

**Teste Parcial**
- Restaurar apenas banco de dados
- Restaurar apenas arquivos
- Validar funcionalidade específica

**Validação de Backup**
- Verificar integridade
- Testar restauração rápida
- Validar dados críticos

## 9. Documentação e Manutenção

### Documentação Necessária

1. **Inventário de Sistemas**
   - Servidores e IPs
   - Bancos de dados
   - Storage
   - Dependências

2. **Credenciais**
   - Armazenadas em cofre seguro
   - Acesso controlado
   - Rotação regular

3. **Contatos**
   - Equipe técnica
   - Fornecedores
   - Suporte

4. **Procedimentos**
   - Passo a passo detalhado
   - Comandos exatos
   - Validações

### Manutenção

- Revisar plano trimestralmente
- Atualizar após mudanças
- Treinar equipe regularmente
- Testar procedimentos

## 10. Recursos e Ferramentas

### Ferramentas de Backup

- **Banco de Dados**: mysqldump, Laravel Backup
- **Arquivos**: tar, rsync, S3
- **Configuração**: Git, versionamento

### Ambientes

- **Produção**: Servidor principal
- **Staging**: Ambiente de teste
- **DR Site**: Localização alternativa (se disponível)

### Monitoramento

- Status de backups
- Integridade de dados
- Disponibilidade de serviços
- Performance pós-restauração

## 11. Lições Aprendidas

### Após Cada Incidente

1. **Documentar**
   - O que aconteceu
   - Como foi resolvido
   - Tempo de recuperação
   - Impacto

2. **Analisar**
   - O que funcionou bem
   - O que pode melhorar
   - Lições aprendidas

3. **Melhorar**
   - Atualizar procedimentos
   - Implementar melhorias
   - Treinar equipe

---

**Versão**: 1.0
**Última atualização**: {{ date('d/m/Y') }}
**Próxima revisão**: {{ date('d/m/Y', strtotime('+3 months')) }}
**RTO**: 4 horas (crítico)
**RPO**: 1 hora (crítico)

