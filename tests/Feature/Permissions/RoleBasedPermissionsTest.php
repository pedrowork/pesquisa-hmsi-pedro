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

test('user inherits permissions from role', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');
    assignRoleToUser($user, 'test-role');
    
    createPermission('dashboard.view');
    assignPermissionToRole($roleId, 'dashboard.view');
    
    expect($user->hasPermission('dashboard.view'))->toBeTrue();
});

test('user can have permissions from multiple roles', function () {
    $user = User::factory()->create();
    
    $role1Id = createRole('role1');
    $role2Id = createRole('role2');
    
    assignRoleToUser($user, 'role1');
    assignRoleToUser($user, 'role2');
    
    createPermission('perm1');
    createPermission('perm2');
    
    assignPermissionToRole($role1Id, 'perm1');
    assignPermissionToRole($role2Id, 'perm2');
    
    expect($user->hasPermission('perm1'))->toBeTrue();
    expect($user->hasPermission('perm2'))->toBeTrue();
});

test('user can have direct permissions and role permissions', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');
    assignRoleToUser($user, 'test-role');
    
    createPermission('perm1');
    createPermission('perm2');
    
    assignPermissionToRole($roleId, 'perm1');
    assignPermissionToUser($user, 'perm2');
    
    expect($user->hasPermission('perm1'))->toBeTrue();
    expect($user->hasPermission('perm2'))->toBeTrue();
});

test('updating role permissions affects users with that role', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');
    assignRoleToUser($user, 'test-role');
    
    createPermission('perm1');
    assignPermissionToRole($roleId, 'perm1');
    
    expect($user->hasPermission('perm1'))->toBeTrue();
    
    // Remover permissão do role
    $permission = DB::table('permissions')->where('slug', 'perm1')->first();
    DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->where('permission_id', $permission->id)
        ->delete();
    
    // Recarregar usuário para refletir mudanças
    $user->refresh();
    
    expect($user->hasPermission('perm1'))->toBeFalse();
});

test('user loses access when permission is removed from role', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');
    assignRoleToUser($user, 'test-role');
    
    createPermission('dashboard.view');
    assignPermissionToRole($roleId, 'dashboard.view');
    
    expect($user->hasPermission('dashboard.view'))->toBeTrue();
    
    // Remover permissão do role
    $permission = DB::table('permissions')->where('slug', 'dashboard.view')->first();
    DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->where('permission_id', $permission->id)
        ->delete();
    
    $user->refresh();
    expect($user->hasPermission('dashboard.view'))->toBeFalse();
});

test('user with multiple roles gets union of all permissions', function () {
    $user = User::factory()->create();
    
    $role1Id = createRole('role1');
    $role2Id = createRole('role2');
    
    assignRoleToUser($user, 'role1');
    assignRoleToUser($user, 'role2');
    
    createPermission('perm1');
    createPermission('perm2');
    createPermission('perm3');
    
    assignPermissionToRole($role1Id, 'perm1');
    assignPermissionToRole($role1Id, 'perm2');
    assignPermissionToRole($role2Id, 'perm2');
    assignPermissionToRole($role2Id, 'perm3');
    
    $permissions = $user->getUserPermissions();
    
    expect($permissions)->toContain('perm1', 'perm2', 'perm3');
    expect(count($permissions))->toBe(3);
});

test('role without permissions does not grant access', function () {
    $user = User::factory()->create();
    $roleId = createRole('empty-role');
    assignRoleToUser($user, 'empty-role');
    
    createPermission('dashboard.view');
    
    expect($user->hasPermission('dashboard.view'))->toBeFalse();
});

test('user without roles has no permissions', function () {
    $user = User::factory()->create();
    
    createPermission('dashboard.view');
    
    expect($user->hasPermission('dashboard.view'))->toBeFalse();
    expect($user->getUserPermissions())->toBeEmpty();
});

