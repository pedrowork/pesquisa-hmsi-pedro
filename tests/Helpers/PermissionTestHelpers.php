<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Helper para criar permissão no banco
 */
function createPermission(string $slug, string $name = null, string $description = null): int
{
    return DB::table('permissions')->insertGetId([
        'name' => $name ?? ucfirst(str_replace('.', ' ', $slug)),
        'slug' => $slug,
        'description' => $description ?? "Permissão para {$slug}",
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Helper para criar role no banco
 */
function createRole(string $slug, string $name = null, string $description = null): int
{
    return DB::table('roles')->insertGetId([
        'name' => $name ?? ucfirst($slug),
        'slug' => $slug,
        'description' => $description ?? "Role {$slug}",
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Helper para associar permissão diretamente ao usuário
 */
function assignPermissionToUser(User $user, string $permissionSlug): void
{
    $permission = DB::table('permissions')->where('slug', $permissionSlug)->first();
    
    if (!$permission) {
        $permissionId = createPermission($permissionSlug);
    } else {
        $permissionId = $permission->id;
    }
    
    // Verificar se já existe para evitar duplicatas
    $exists = DB::table('user_permissions')
        ->where('user_id', $user->id)
        ->where('permission_id', $permissionId)
        ->exists();
    
    if (!$exists) {
    DB::table('user_permissions')->insert([
        'user_id' => $user->id,
        'permission_id' => $permissionId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Limpar cache de permissões do usuário
    $user->clearPermissionsCache();
}
    
    // Limpar cache de permissões do usuário
    $user->clearPermissionsCache();
}

/**
 * Helper para associar role ao usuário
 */
function assignRoleToUser(User $user, string $roleSlug): int
{
    $role = DB::table('roles')->where('slug', $roleSlug)->first();
    
    if (!$role) {
        $roleId = createRole($roleSlug);
    } else {
        $roleId = $role->id;
    }
    
    DB::table('user_roles')->updateOrInsert(
        ['user_id' => $user->id, 'role_id' => $roleId],
        ['created_at' => now(), 'updated_at' => now()]
    );
    
    // Limpar cache de permissões do usuário
    $user->clearPermissionsCache();
    
    return $roleId;
}

/**
 * Helper para associar permissão ao role
 */
function assignPermissionToRole(int $roleId, string $permissionSlug): void
{
    $permission = DB::table('permissions')->where('slug', $permissionSlug)->first();
    
    if (!$permission) {
        $permissionId = createPermission($permissionSlug);
    } else {
        $permissionId = $permission->id;
    }
    
    DB::table('role_permissions')->updateOrInsert(
        ['role_id' => $roleId, 'permission_id' => $permissionId],
        ['created_at' => now(), 'updated_at' => now()]
    );
    
    // Limpar cache de todos os usuários que têm esta role
    clearCacheForUsersWithRole($roleId);
}

/**
 * Limpa o cache de permissões de todos os usuários que têm uma role específica
 */
function clearCacheForUsersWithRole(int $roleId): void
{
    $userIds = DB::table('user_roles')
        ->where('role_id', $roleId)
        ->pluck('user_id')
        ->toArray();
    
    foreach ($userIds as $userId) {
        $user = User::find($userId);
        if ($user) {
            $user->clearPermissionsCache();
        }
    }
}

/**
 * Helper para criar usuário com permissão específica
 */
function createUserWithPermission(string $permissionSlug): User
{
    $user = User::factory()->create();
    assignPermissionToUser($user, $permissionSlug);
    
    // Recarregar do banco para garantir que as permissões estão carregadas
    $user->refresh();
    
    return $user;
}

/**
 * Helper para criar usuário com role e permissões
 */
function createUserWithRole(string $roleSlug, array $permissions = []): User
{
    $user = User::factory()->create();
    $roleId = assignRoleToUser($user, $roleSlug);
    
    foreach ($permissions as $permission) {
        assignPermissionToRole($roleId, $permission);
    }
    
    return $user;
}

/**
 * Helper para criar usuário admin
 */
function createAdminUser(): User
{
    $user = User::factory()->create();
    assignRoleToUser($user, 'admin');
    return $user;
}

/**
 * Helper para verificar se usuário tem permissão
 */
function assertUserHasPermission(User $user, string $permission): void
{
    expect($user->hasPermission($permission))->toBeTrue("Usuário deveria ter permissão {$permission}");
}

/**
 * Helper para verificar se usuário não tem permissão
 */
function assertUserDoesNotHavePermission(User $user, string $permission): void
{
    expect($user->hasPermission($permission))->toBeFalse("Usuário não deveria ter permissão {$permission}");
}

