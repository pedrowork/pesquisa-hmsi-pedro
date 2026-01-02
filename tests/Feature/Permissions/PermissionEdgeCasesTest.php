<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
});

test('hasPermission returns false for nonexistent permission', function () {
    $user = User::factory()->create();
    
    expect($user->hasPermission('nonexistent.permission'))->toBeFalse();
});

test('hasPermission handles permission slug with special characters', function () {
    $user = User::factory()->create();
    
    // Criar permissão com caracteres especiais no slug
    $permissionId = createPermission('test-permission_123');
    assignPermissionToUser($user, 'test-permission_123');
    
    expect($user->hasPermission('test-permission_123'))->toBeTrue();
});

test('user with multiple roles gets correct permissions', function () {
    $user = User::factory()->create();
    
    $role1Id = createRole('role1');
    $role2Id = createRole('role2');
    $role3Id = createRole('role3');
    
    assignRoleToUser($user, 'role1');
    assignRoleToUser($user, 'role2');
    assignRoleToUser($user, 'role3');
    
    createPermission('perm1');
    createPermission('perm2');
    createPermission('perm3');
    createPermission('perm4');
    
    assignPermissionToRole($role1Id, 'perm1');
    assignPermissionToRole($role2Id, 'perm2');
    assignPermissionToRole($role3Id, 'perm3');
    assignPermissionToRole($role3Id, 'perm4');
    
    $permissions = $user->getUserPermissions();
    
    expect($permissions)->toContain('perm1', 'perm2', 'perm3', 'perm4');
    expect(count($permissions))->toBe(4);
});

test('role without permissions grants no access', function () {
    $user = User::factory()->create();
    $roleId = createRole('empty-role');
    assignRoleToUser($user, 'empty-role');
    
    createPermission('dashboard.view');
    
    expect($user->hasPermission('dashboard.view'))->toBeFalse();
    expect($user->getUserPermissions())->toBeEmpty();
});

test('user without roles has no permissions', function () {
    $user = User::factory()->create();
    
    createPermission('dashboard.view');
    createPermission('users.view');
    
    expect($user->hasPermission('dashboard.view'))->toBeFalse();
    expect($user->hasPermission('users.view'))->toBeFalse();
    expect($user->getUserPermissions())->toBeEmpty();
});

test('removed permission from database does not cause error', function () {
    $user = User::factory()->create();
    $permissionId = createPermission('test.permission');
    assignPermissionToUser($user, 'test.permission');
    
    expect($user->hasPermission('test.permission'))->toBeTrue();
    
    // Remover permissão do banco
    DB::table('permissions')->where('id', $permissionId)->delete();
    
    // Limpar cache do usuário para refletir a mudança
    $user->clearPermissionsCache();
    
    // Deve retornar false sem causar erro
    expect($user->hasPermission('test.permission'))->toBeFalse();
});

test('duplicate permissions are handled correctly', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');
    assignRoleToUser($user, 'test-role');
    
    $permissionId = createPermission('test.permission');
    
    // Mesma permissão via role E direta
    assignPermissionToRole($roleId, 'test.permission');
    assignPermissionToUser($user, 'test.permission');
    
    $permissions = $user->getUserPermissions();
    
    // Deve aparecer apenas uma vez
    expect($permissions)->toContain('test.permission');
    expect(count(array_keys($permissions, 'test.permission')))->toBe(1);
});

test('hasRole returns false for nonexistent role', function () {
    $user = User::factory()->create();
    
    expect($user->hasRole('nonexistent-role'))->toBeFalse();
});

test('hasRole works with role name and slug', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role', 'Test Role Name');
    assignRoleToUser($user, 'test-role');
    
    expect($user->hasRole('test-role'))->toBeTrue();
    expect($user->hasRole('Test Role Name'))->toBeTrue();
});

test('getUserRoles returns empty array for user without roles', function () {
    $user = User::factory()->create();
    
    expect($user->getUserRoles())->toBeEmpty();
});

test('getUserRoles returns all roles for user', function () {
    $user = User::factory()->create();
    
    assignRoleToUser($user, 'role1');
    assignRoleToUser($user, 'role2');
    assignRoleToUser($user, 'role3');
    
    $roles = $user->getUserRoles();
    
    expect($roles)->toContain('role1', 'role2', 'role3');
    expect(count($roles))->toBe(3);
});

