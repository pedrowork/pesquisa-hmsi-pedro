<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Limpar tabelas relacionadas
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
});

test('hasPermission returns false for user without permissions', function () {
    $user = User::factory()->create();
    
    expect($user->hasPermission('dashboard.view'))->toBeFalse();
});

test('hasPermission returns true for user with direct permission', function () {
    $user = User::factory()->create();
    
    // Criar permissão
    $permissionId = DB::table('permissions')->insertGetId([
        'name' => 'Visualizar Dashboard',
        'slug' => 'dashboard.view',
        'description' => 'Permite visualizar o dashboard',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Associar permissão diretamente ao usuário
    DB::table('user_permissions')->insert([
        'user_id' => $user->id,
        'permission_id' => $permissionId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    expect($user->hasPermission('dashboard.view'))->toBeTrue();
});

test('hasPermission returns true for user with permission via role', function () {
    $user = User::factory()->create();
    
    // Criar role
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Test Role',
        'slug' => 'test-role',
        'description' => 'Role de teste',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Criar permissão
    $permissionId = DB::table('permissions')->insertGetId([
        'name' => 'Visualizar Dashboard',
        'slug' => 'dashboard.view',
        'description' => 'Permite visualizar o dashboard',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Associar role ao usuário
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Associar permissão ao role
    DB::table('role_permissions')->insert([
        'role_id' => $roleId,
        'permission_id' => $permissionId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    expect($user->hasPermission('dashboard.view'))->toBeTrue();
});

test('hasPermission returns true for admin user regardless of permissions', function () {
    $user = User::factory()->create();
    
    // Criar role admin
    $adminRoleId = DB::table('roles')->insertGetId([
        'name' => 'Admin',
        'slug' => 'admin',
        'description' => 'Administrador do sistema',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Associar role admin ao usuário
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $adminRoleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Não criar permissão específica
    expect($user->hasPermission('dashboard.view'))->toBeTrue();
    expect($user->hasPermission('users.create'))->toBeTrue();
    expect($user->hasPermission('any.permission'))->toBeTrue();
});

test('hasRole returns true for user with role by slug', function () {
    $user = User::factory()->create();
    
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Test Role',
        'slug' => 'test-role',
        'description' => 'Role de teste',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    expect($user->hasRole('test-role'))->toBeTrue();
});

test('hasRole returns true for user with role by name', function () {
    $user = User::factory()->create();
    
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Test Role',
        'slug' => 'test-role',
        'description' => 'Role de teste',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    expect($user->hasRole('Test Role'))->toBeTrue();
});

test('isAdmin returns true for user with admin role', function () {
    $user = User::factory()->create();
    
    $adminRoleId = DB::table('roles')->insertGetId([
        'name' => 'Admin',
        'slug' => 'admin',
        'description' => 'Administrador',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $adminRoleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    expect($user->isAdmin())->toBeTrue();
});

test('isAdmin returns false for user without admin role', function () {
    $user = User::factory()->create();
    
    expect($user->isAdmin())->toBeFalse();
});

test('getUserPermissions returns all permissions for admin', function () {
    $user = User::factory()->create();
    
    // Criar role admin
    $adminRoleId = DB::table('roles')->insertGetId([
        'name' => 'Admin',
        'slug' => 'admin',
        'description' => 'Administrador',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $adminRoleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Criar algumas permissões
    DB::table('permissions')->insert([
        ['name' => 'Perm 1', 'slug' => 'perm1', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Perm 2', 'slug' => 'perm2', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Perm 3', 'slug' => 'perm3', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ]);
    
    $permissions = $user->getUserPermissions();
    
    expect($permissions)->toContain('perm1', 'perm2', 'perm3');
    expect(count($permissions))->toBe(3);
});

test('getUserPermissions returns direct and role permissions for non-admin', function () {
    $user = User::factory()->create();
    
    // Criar role
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Test Role',
        'slug' => 'test-role',
        'description' => 'Role de teste',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Criar permissões
    $perm1Id = DB::table('permissions')->insertGetId([
        'name' => 'Perm 1', 'slug' => 'perm1', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
    ]);
    $perm2Id = DB::table('permissions')->insertGetId([
        'name' => 'Perm 2', 'slug' => 'perm2', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
    ]);
    $perm3Id = DB::table('permissions')->insertGetId([
        'name' => 'Perm 3', 'slug' => 'perm3', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
    ]);
    
    // Associar role ao usuário
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Permissão via role
    DB::table('role_permissions')->insert([
        'role_id' => $roleId,
        'permission_id' => $perm1Id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Permissão direta
    DB::table('user_permissions')->insert([
        'user_id' => $user->id,
        'permission_id' => $perm2Id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $permissions = $user->getUserPermissions();
    
    expect($permissions)->toContain('perm1', 'perm2');
    expect($permissions)->not->toContain('perm3');
    expect(count($permissions))->toBe(2);
});

test('getUserPermissions removes duplicate permissions', function () {
    $user = User::factory()->create();
    
    // Criar role
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Test Role',
        'slug' => 'test-role',
        'description' => 'Role de teste',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Criar permissão
    $permId = DB::table('permissions')->insertGetId([
        'name' => 'Perm 1', 'slug' => 'perm1', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
    ]);
    
    // Associar role ao usuário
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Mesma permissão via role E direta
    DB::table('role_permissions')->insert([
        'role_id' => $roleId,
        'permission_id' => $permId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('user_permissions')->insert([
        'user_id' => $user->id,
        'permission_id' => $permId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $permissions = $user->getUserPermissions();
    
    // Deve aparecer apenas uma vez
    expect($permissions)->toContain('perm1');
    expect(count($permissions))->toBe(1);
    expect(count(array_keys($permissions, 'perm1')))->toBe(1);
});

test('getUserRoles returns all roles for user', function () {
    $user = User::factory()->create();
    
    $role1Id = DB::table('roles')->insertGetId([
        'name' => 'Role 1', 'slug' => 'role1', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
    ]);
    $role2Id = DB::table('roles')->insertGetId([
        'name' => 'Role 2', 'slug' => 'role2', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
    ]);
    
    DB::table('user_roles')->insert([
        ['user_id' => $user->id, 'role_id' => $role1Id, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $user->id, 'role_id' => $role2Id, 'created_at' => now(), 'updated_at' => now()],
    ]);
    
    $roles = $user->getUserRoles();
    
    expect($roles)->toContain('role1', 'role2');
    expect(count($roles))->toBe(2);
});

