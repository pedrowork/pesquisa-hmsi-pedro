# Estrutura de Arquivos do Projeto - Pesquisa HMSI

Este documento descreve a estrutura importante do projeto e os arquivos principais para referência rápida.

## 📁 Estrutura de Diretórios Principais

### `/app` - Lógica da Aplicação (Backend PHP)
- **`/Http/Controllers/`** - Controladores que gerenciam requisições HTTP
  - `UserController.php` - Gerenciamento de usuários
  - `PerguntaController.php` - Gerenciamento de perguntas
  - `QuestionarioController.php` - Criação e visualização de questionários
  - `MetricaController.php` - Métricas e análises
  - Entre outros...
  
- **`/Http/Middleware/`** - Middlewares de segurança e autenticação
  - `CheckPermission.php` - Verificação de permissões
  - `SingleSession.php` - Controle de sessão única
  - `SecurityHeaders.php` - Headers de segurança HTTP
  - `ForceHttps.php` - Força uso de HTTPS

- **`/Models/`** - Modelos Eloquent (ORM)
  - `User.php` - Modelo de usuário com relacionamentos
  - `Role.php` - Modelo de roles/perfis
  - `AuditLog.php` - Logs de auditoria
  - Entre outros...

- **`/Services/`** - Serviços de negócio
  - `AuditService.php` - Serviços de auditoria
  - `SecurityMonitoringService.php` - Monitoramento de segurança
  - `PasswordPolicyService.php` - Políticas de senha

- **`/Console/Commands/`** - Comandos Artisan personalizados
  - `ValidateProductionConfig.php` - Validação de configuração de produção
  - `BackupDatabase.php` - Backup do banco de dados
  - Entre outros...

### `/resources/js` - Frontend (React/TypeScript)
- **`/pages/`** - Páginas da aplicação (rotas)
  - `dashboard.tsx` - Dashboard principal
  - `users/index.tsx` - Listagem de usuários
  - `perguntas/index.tsx` - Gerenciamento de perguntas
  - `questionarios/create.tsx` - Criação de questionários
  - `auth/login.tsx` - Página de login
  - Entre outras...

- **`/components/`** - Componentes React reutilizáveis
  - `app-sidebar.tsx` - Sidebar principal
  - `app-logo.tsx` - Logo da aplicação
  - `/ui/` - Componentes UI (shadcn/ui)
    - `button.tsx`, `input.tsx`, `dialog.tsx`, etc.

- **`/layouts/`** - Layouts da aplicação
  - `app-layout.tsx` - Layout principal autenticado
  - `auth-layout.tsx` - Layout para páginas de autenticação

- **`/hooks/`** - Custom hooks React
  - `usePermissions.ts` - Hook para verificar permissões
  - `useAppearance.ts` - Hook para tema claro/escuro

- **`/lib/`** - Utilitários e helpers
  - `utils.ts` - Funções utilitárias

- **`/types/`** - Definições TypeScript
  - `index.d.ts` - Tipos globais
  - `permissions.ts` - Tipos relacionados a permissões

### `/database` - Banco de Dados
- **`/migrations/`** - Migrações do banco de dados
  - Estrutura completa das tabelas
  - Última migration: `2026_01_18_001112_fix_perguntas_descricao_sequence.php`

- **`/seeders/`** - Seeders para popular banco
  - `DatabaseSeeder.php` - Seeder principal (chama todos)
  - `AdminSeeder.php` - Cria usuário admin e roles
  - `PermissionSeeder.php` - Cria permissões do sistema
  - Entre outros...

- **`/docs/`** - Documentação do banco
  - `seed.MD` - Documentação dos seeders
  - `mapeamento-permissoes-metricas.md` - Mapeamento de permissões

### `/routes` - Rotas da Aplicação
- **`web.php`** - Rotas web principais (HTTP)
  - Dashboard, CRUDs, métricas, etc.
  
- **`settings.php`** - Rotas de configurações do usuário
  - Perfil, senha, autenticação 2FA

- **`console.php`** - Comandos console (cron, tasks)

### `/config` - Arquivos de Configuração
- **`app.php`** - Configurações gerais da aplicação
- **`database.php`** - Configurações do banco de dados
- **`auth.php`** - Configurações de autenticação
- **`fortify.php`** - Configurações do Laravel Fortify
- **`inertia.php`** - Configurações do Inertia.js
- **`security.php`** - Configurações de segurança
- Entre outros...

### `/public` - Arquivos Públicos (Document Root)
- **`index.php`** - Ponto de entrada da aplicação
- **`.htaccess`** - Configurações Apache (URL rewriting)
- **`logomarca.png`** - Logo da aplicação
- **`favicon.ico`**, `favicon.svg` - Favicons
- **`robots.txt`** - Configurações de SEO/crawlers

### `/storage` - Armazenamento (Não versionado)
- **`/logs/`** - Logs da aplicação (Laravel)
- **`/framework/`** - Cache e arquivos temporários
- **`/app/public/`** - Uploads de arquivos (link simbólico em `/public/storage`)

### `/bootstrap` - Inicialização
- **`app.php`** - Bootstrap da aplicação Laravel
- **`providers.php`** - Service providers
- **`cache/`** - Cache de bootstrap (não versionado)

### `/docs` - Documentação do Projeto
- **`DEPLOY.md`** - Guia completo de deploy em produção
- **`OTIMIZACOES-PERFORMANCE.md`** - Documentação de otimizações
- **`arquivos.md`** - Este arquivo (estrutura do projeto)
- Entre outras documentações...

## 📄 Arquivos Importantes na Raiz

### Configuração e Dependências
- **`.env.example`** - Template de variáveis de ambiente (ÚNICO arquivo .env para exemplo)
- **`composer.json`** - Dependências PHP e scripts
- **`package.json`** - Dependências Node.js e scripts
- **`.gitignore`** - Arquivos ignorados pelo Git (segurança)

### Documentação
- **`README.md`** - Documentação inicial do projeto
- **`DEPLOY.md`** - Guia de deploy em produção
- **`LICENSE`** - Licença do software

### Configuração do Servidor
- **`nginx.conf`** - Configuração do Nginx (VPS)
- **`docker-compose.yml`** - Configuração Docker (desenvolvimento)
- **`vite.config.ts`** - Configuração do Vite (build frontend)

### Scripts
- **`deploy.sh`** - Script de deploy automatizado
- **`artisan`** - Interface de linha de comando Laravel

## 🔐 Arquivos de Segurança (Nunca Commitados)

Os seguintes arquivos estão no `.gitignore` e **NUNCA** devem ser commitados:
- `.env` - Variáveis de ambiente com credenciais
- `.env.*` - Qualquer variação do .env (exceto `.env.example`)
- `env.exemple*` - Arquivos de exemplo duplicados
- `storage/logs/*.log` - Logs que podem conter informações sensíveis
- `/descarte/` - Pasta com arquivos removidos do projeto

## 🗂️ Arquivos Removidos para Limpeza

Arquivos movidos para `/descarte/` durante a limpeza:
- Arquivos `.env` duplicados (env.example04, env.exemple01-05)
- Logomarca duplicada da raiz (mantida apenas em `/public/`)
- Arquivos temporários de documentação (`.txt`, `.sql` de teste)

## 📝 Notas Importantes

### Arquivos .env
- **Único arquivo permitido no Git**: `.env.example`
- Todos os outros arquivos `.env*` ou `env.*` devem estar no `.gitignore`
- Nunca commitar credenciais ou senhas

### Estrutura do Frontend
- Build de produção fica em `/public/build/` (não versionado)
- Assets estáticos ficam em `/public/` (versionados)
- Componentes React em `/resources/js/`

### Banco de Dados
- Migrations em `/database/migrations/` (versionadas)
- Seeders em `/database/seeders/` (versionados)
- Backups em `/database/backups/` (não versionados)

### Documentação
- Documentação principal em `/docs/`
- README.md na raiz para início rápido
- DEPLOY.md para guia de produção

## 🚀 Arquivos Críticos para Deploy

Para deploy em produção, verificar:
1. `.env.example` - Template correto de configuração
2. `composer.json` - Dependências atualizadas
3. `package.json` - Dependências frontend atualizadas
4. `/database/migrations/` - Todas as migrations necessárias
5. `/database/seeders/` - Seeders configurados
6. `nginx.conf` - Configuração do servidor web
7. `DEPLOY.md` - Guia completo de deploy

## 📚 Documentação Adicional

Para mais informações, consulte:
- `DEPLOY.md` - Deploy em produção
- `README.md` - Instalação e desenvolvimento
- `/docs/*.md` - Documentação específica de funcionalidades
