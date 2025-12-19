# Pesquisa HMSI

Sistema de pesquisa de satisfação hospitalar desenvolvido com Laravel 12, Inertia.js, React e TypeScript.

## Requisitos

- PHP ^8.2
- Composer
- Node.js 18+ e npm
- SQLite (incluído por padrão)

## Instalação Inicial

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

