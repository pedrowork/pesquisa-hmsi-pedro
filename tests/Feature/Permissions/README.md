# Testes de Permissões

Esta pasta contém todos os testes relacionados ao sistema de permissões do aplicativo.

## Estrutura

### Testes Unitários (`tests/Unit/`)
- `HasPermissionsTest.php` - Testa o trait HasPermissions
- `CheckPermissionMiddlewareTest.php` - Testa o middleware de verificação de permissões

### Testes de Feature (`tests/Feature/Permissions/`)
- `DashboardPermissionsTest.php` - Testa permissões do dashboard
- `UsersPermissionsTest.php` - Testa permissões de usuários
- `RolesPermissionsTest.php` - Testa permissões de roles
- `PermissionsPermissionsTest.php` - Testa permissões de permissões
- `QuestionariosPermissionsTest.php` - Testa permissões de questionários
- `LeitosPermissionsTest.php` - Testa permissões granulares de leitos
- `SetoresPermissionsTest.php` - Testa permissões granulares de setores
- `TiposConvenioPermissionsTest.php` - Testa permissões granulares de tipos de convênio
- `SetoresPesquisaPermissionsTest.php` - Testa permissões granulares de setores de pesquisa
- `PerguntasPermissionsTest.php` - Testa permissões granulares de perguntas
- `SatisfacaoPermissionsTest.php` - Testa permissões granulares de satisfação
- `MetricasPermissionsTest.php` - Testa permissões de métricas
- `RoleBasedPermissionsTest.php` - Testa permissões baseadas em roles
- `AdminPermissionsTest.php` - Testa permissões de admin
- `PermissionEdgeCasesTest.php` - Testa casos extremos e edge cases

## Helpers

O arquivo `tests/Helpers/PermissionTestHelpers.php` contém funções auxiliares para facilitar a criação de testes:

- `createPermission(string $slug, ...)` - Cria uma permissão
- `createRole(string $slug, ...)` - Cria uma role
- `assignPermissionToUser(User $user, string $permissionSlug)` - Associa permissão diretamente ao usuário
- `assignRoleToUser(User $user, string $roleSlug)` - Associa role ao usuário
- `assignPermissionToRole(int $roleId, string $permissionSlug)` - Associa permissão ao role
- `createUserWithPermission(string $permissionSlug)` - Cria usuário com permissão
- `createUserWithRole(string $roleSlug, array $permissions)` - Cria usuário com role e permissões
- `createAdminUser()` - Cria usuário admin

## Executando os Testes

```bash
# Todos os testes de permissões
php artisan test tests/Feature/Permissions

# Testes unitários
php artisan test tests/Unit/HasPermissionsTest.php
php artisan test tests/Unit/CheckPermissionMiddlewareTest.php

# Teste específico
php artisan test --filter=DashboardPermissionsTest
```

## Cobertura

Os testes cobrem:
- Todas as 50+ permissões do sistema
- Todas as rotas protegidas
- Permissões diretas e via roles
- Comportamento de admin
- Casos extremos e edge cases
- Independência de permissões granulares

