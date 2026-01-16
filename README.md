# Pesquisa HMSI

Sistema de pesquisa de satisfação hospitalar desenvolvido com Laravel 12, Inertia.js, React e TypeScript.

## 📄 Licença

Este software é proprietário e pertence exclusivamente a **Pedro**.

**Uso não comercial apenas** - É estritamente proibido o uso comercial deste software sem autorização expressa do desenvolvedor.

Para mais informações, consulte o arquivo [LICENSE](LICENSE).

## Requisitos

- PHP ^8.2
- Composer
- Node.js 18+ e npm
- Docker e Docker Compose (para ambiente containerizado)
- SQLite (incluído por padrão) ou PostgreSQL (produção)

## Instalação Inicial

BANCO DE DADOS E RESET
```bash
php artisan migrate:fresh --seed
docker-compose exec php php artisan migrate:fresh --seed
```

```bash
# Instalar dependências PHP
composer install

# Instalar dependências Node.js
npm install

# Gerar chave da aplicação (cria .env automaticamente se não existir)
php artisan key:generate

# Criar banco de dados SQLite (se não existir)
touch database/database.sqlite

# Executar migrações
php artisan migrate

# Popular banco com dados iniciais (opcional)
php artisan db:seed
```

## Configuração de Email (Mailpit)

O projeto está configurado para usar o **Mailpit** para capturar emails durante desenvolvimento/testes.

### Configuração no .env

Adicione as seguintes variáveis no seu arquivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@pesquisa-hmsi.local
MAIL_FROM_NAME="Pesquisa HMSI"
```

### Acessar a Interface do Mailpit

Após iniciar os containers Docker:

```bash
docker-compose up -d
```

Acesse a interface web do Mailpit em: **http://localhost:8025**

Todos os emails enviados pela aplicação (verificação de email, recuperação de senha, etc.) serão capturados e exibidos na interface do Mailpit.

Para mais detalhes, consulte [docs/mailpit-configuracao.md](docs/mailpit-configuracao.md).

## Desenvolvimento

```bash
# Iniciar servidor PHP, fila e Vite simultaneamente
composer dev
```

Ou execute separadamente:

```bash
# Servidor PHP (porta 8000)
php artisan serve

# Compilar assets em modo desenvolvimento
npm run dev

# Processar fila (se necessário)
php artisan queue:listen
```

## Build para Produção

```bash
# Compilar assets
npm run build
```

## Comandos Úteis

```bash
# Limpar cache
php artisan config:clear
php artisan cache:clear

# Executar testes
composer test

# Executar migrações
php artisan migrate

# Reverter última migração
php artisan migrate:rollback

# Executar seeders
php artisan db:seed

# Acessar Tinker (console interativo)
php artisan tinker
```

## Estrutura Principal

- `app/` - Código PHP (Controllers, Models, Middleware)
- `resources/js/` - Código React/TypeScript
- `database/migrations/` - Migrações do banco
- `database/seeders/` - Seeders para popular dados
- `routes/web.php` - Rotas da aplicação

## Acesso

Após iniciar o servidor, acesse: http://localhost:8000

