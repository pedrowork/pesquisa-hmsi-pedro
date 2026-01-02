# Plano de Resposta a Incidentes de Segurança

## 1. Classificação de Incidentes

### Nível Crítico
- Comprometimento de dados pessoais
- Acesso não autorizado a sistemas administrativos
- Vazamento de informações sensíveis
- Ataques de ransomware
- DDoS que impede acesso ao sistema

**Tempo de resposta**: Imediato (< 15 minutos)

### Nível Alto
- Tentativas de acesso não autorizado em massa
- Vulnerabilidades críticas descobertas
- Atividades suspeitas de administradores
- Múltiplas contas comprometidas

**Tempo de resposta**: < 1 hora

### Nível Médio
- Tentativas de força bruta
- Atividades suspeitas de usuários
- Tentativas de SQL injection
- Tentativas de XSS

**Tempo de resposta**: < 4 horas

### Nível Baixo
- Eventos informativos
- Tentativas de acesso falhadas isoladas
- Alertas de segurança rotineiros

**Tempo de resposta**: < 24 horas

## 2. Equipe de Resposta

### Responsabilidades

**Líder de Incidentes**
- Coordenação geral da resposta
- Comunicação com stakeholders
- Decisões estratégicas

**Analista de Segurança**
- Investigação técnica
- Análise de logs
- Identificação de causa raiz

**Desenvolvedor Sênior**
- Correção de vulnerabilidades
- Implementação de patches
- Testes de segurança

**Administrador de Sistema**
- Isolamento de sistemas afetados
- Restauração de serviços
- Monitoramento

## 3. Procedimentos de Resposta

### Fase 1: Detecção e Identificação

1. **Detecção**
   - Monitoramento automatizado (alertas)
   - Relatórios de usuários
   - Análise proativa de logs

2. **Identificação**
   - Classificar o incidente
   - Identificar sistemas afetados
   - Determinar escopo do impacto

3. **Documentação Inicial**
   ```markdown
   - Data/Hora: [timestamp]
   - Tipo: [classificação]
   - Descrição: [detalhes]
   - Sistemas Afetados: [lista]
   - Impacto Estimado: [descrição]
   ```

### Fase 2: Contenção

**Ações Imediatas**

1. **Isolamento**
   - Bloquear IPs suspeitos
   - Desativar contas comprometidas
   - Isolar sistemas afetados

2. **Preservação de Evidências**
   - Capturar logs relevantes
   - Fazer backup de sistemas afetados
   - Documentar estado atual

3. **Notificações**
   - Alertar equipe de resposta
   - Notificar administradores
   - Informar usuários afetados (se necessário)

**Comandos Úteis**

```bash
# Bloquear IP
php artisan security:block-ip {ip}

# Desativar usuário
php artisan user:deactivate {user_id}

# Invalidar sessões
php artisan session:clear-all
```

### Fase 3: Análise e Investigação

1. **Coleta de Dados**
   - Revisar logs de auditoria
   - Analisar tentativas de acesso
   - Examinar alterações recentes

2. **Análise**
   - Identificar vetor de ataque
   - Determinar causa raiz
   - Avaliar extensão do comprometimento

3. **Documentação**
   ```markdown
   - Vetor de Ataque: [descrição]
   - Causa Raiz: [análise]
   - Dados Acessados: [lista]
   - Período de Comprometimento: [início - fim]
   ```

### Fase 4: Remediação

1. **Correção**
   - Aplicar patches de segurança
   - Corrigir vulnerabilidades
   - Implementar controles adicionais

2. **Limpeza**
   - Remover backdoors
   - Limpar dados comprometidos
   - Restaurar sistemas

3. **Validação**
   - Testar correções
   - Verificar integridade
   - Confirmar resolução

### Fase 5: Recuperação

1. **Restauração**
   - Restaurar serviços
   - Validar funcionalidade
   - Monitorar estabilidade

2. **Comunicação**
   - Notificar resolução
   - Fornecer atualizações
   - Documentar lições aprendidas

### Fase 6: Pós-Incidente

1. **Análise Post-Mortem**
   - Revisar resposta
   - Identificar melhorias
   - Atualizar procedimentos

2. **Documentação**
   - Relatório completo do incidente
   - Lições aprendidas
   - Recomendações

3. **Melhorias**
   - Implementar melhorias identificadas
   - Atualizar políticas
   - Treinar equipe

## 4. Procedimentos Específicos

### Comprometimento de Conta

1. **Imediato**
   ```bash
   # Desativar conta
   php artisan user:deactivate {user_id}
   
   # Invalidar sessões
   php artisan session:invalidate-user {user_id}
   
   # Forçar troca de senha
   php artisan user:force-password-change {user_id}
   ```

2. **Investigação**
   - Revisar logs de acesso
   - Verificar alterações feitas
   - Identificar origem do comprometimento

3. **Remediação**
   - Resetar senha
   - Revisar permissões
   - Notificar usuário

### Vazamento de Dados

1. **Imediato**
   - Identificar dados vazados
   - Avaliar impacto
   - Notificar autoridades (se necessário LGPD)

2. **Contenção**
   - Bloquear acesso aos dados
   - Revogar tokens/acessos
   - Isolar sistemas afetados

3. **Notificação**
   - Notificar usuários afetados
   - Informar autoridades competentes
   - Preparar comunicação pública (se necessário)

### Ataque de Força Bruta

1. **Detecção**
   - Monitorar tentativas de login falhadas
   - Identificar padrões suspeitos
   - Alertar quando limite excedido

2. **Resposta**
   ```bash
   # Bloquear IP
   php artisan security:block-ip {ip}
   
   # Bloquear conta
   php artisan user:lock-account {email}
   ```

3. **Prevenção**
   - Implementar CAPTCHA
   - Rate limiting mais rigoroso
   - Monitoramento contínuo

### Vulnerabilidade Crítica

1. **Identificação**
   - Escanear dependências
   - Revisar código
   - Monitorar avisos de segurança

2. **Resposta**
   - Avaliar impacto
   - Desenvolver patch
   - Testar correção
   - Aplicar patch imediatamente

3. **Comunicação**
   - Notificar equipe
   - Documentar vulnerabilidade
   - Atualizar changelog

## 5. Comunicação

### Interna
- **Crítico**: Notificação imediata via Slack/Email
- **Alto**: Notificação em 1 hora
- **Médio/Baixo**: Relatório diário

### Externa
- **Usuários Afetados**: Notificação em 72 horas (LGPD)
- **Autoridades**: Conforme exigido por lei
- **Público**: Se necessário, após aprovação

### Templates de Comunicação

**Notificação Interna (Crítica)**
```
🚨 INCIDENTE CRÍTICO DETECTADO

Tipo: [tipo]
Severidade: Crítica
Data/Hora: [timestamp]
Sistemas Afetados: [lista]
Ações Tomadas: [lista]
Próximos Passos: [lista]

Equipe de resposta acionada.
```

**Notificação a Usuários**
```
Assunto: Notificação de Segurança

Prezado(a) [Nome],

Identificamos uma atividade suspeita em sua conta em [data/hora].
Como medida de segurança, sua conta foi temporariamente bloqueada.

Por favor, redefina sua senha acessando: [link]

Se você não reconhece esta atividade, entre em contato imediatamente.
```

## 6. Ferramentas e Recursos

### Comandos Artisan

```bash
# Segurança
php artisan security:rotate-keys
php artisan security:block-ip {ip}
php artisan security:unblock-ip {ip}

# Usuários
php artisan user:deactivate {id}
php artisan user:lock-account {email}
php artisan user:force-password-change {id}

# Sessões
php artisan session:clear-all
php artisan session:invalidate-user {id}

# Backup
php artisan db:backup --encrypt
```

### Logs Importantes

- `storage/logs/laravel.log` - Logs gerais
- `storage/logs/security.log` - Logs de segurança
- Tabela `audit_logs` - Auditoria de ações
- Tabela `security_alerts` - Alertas de segurança

### Contatos de Emergência

- **Equipe de Segurança**: security@exemplo.com
- **Administradores**: admin@exemplo.com
- **Suporte Técnico**: support@exemplo.com

## 7. Métricas e KPIs

### Tempo de Resposta
- Tempo médio de detecção (MTTD)
- Tempo médio de resposta (MTTR)
- Tempo de resolução

### Efetividade
- Taxa de resolução
- Taxa de recorrência
- Satisfação da equipe

## 8. Revisão e Melhoria

### Revisões Regulares
- Mensal: Revisar incidentes do mês
- Trimestral: Atualizar procedimentos
- Anual: Revisão completa do plano

### Exercícios
- Simulações trimestrais
- Tabletop exercises
- Treinamento da equipe

---

**Versão**: 1.0
**Última atualização**: {{ date('d/m/Y') }}
**Próxima revisão**: {{ date('d/m/Y', strtotime('+3 months')) }}

