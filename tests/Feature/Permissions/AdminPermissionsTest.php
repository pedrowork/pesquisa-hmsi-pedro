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

test('admin user has access to all routes', function () {
    $admin = createAdminUser();
    
    // Criar algumas permissões
    createPermission('dashboard.view');
    createPermission('users.view');
    createPermission('roles.view');
    createPermission('permissions.view');
    
    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('roles.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('permissions.index'))
        ->assertOk();
});

test('admin user has all permissions', function () {
    $admin = createAdminUser();
    
    // Criar várias permissões
    createPermission('dashboard.view');
    createPermission('users.create');
    createPermission('roles.edit');
    createPermission('permissions.delete');
    createPermission('leitos.view');
    createPermission('metricas.nps');
    
    expect($admin->hasPermission('dashboard.view'))->toBeTrue();
    expect($admin->hasPermission('users.create'))->toBeTrue();
    expect($admin->hasPermission('roles.edit'))->toBeTrue();
    expect($admin->hasPermission('permissions.delete'))->toBeTrue();
    expect($admin->hasPermission('leitos.view'))->toBeTrue();
    expect($admin->hasPermission('metricas.nps'))->toBeTrue();
    expect($admin->hasPermission('any.nonexistent.permission'))->toBeTrue();
});

test('admin user isAdmin returns true', function () {
    $admin = createAdminUser();
    
    expect($admin->isAdmin())->toBeTrue();
});

test('non-admin user isAdmin returns false', function () {
    $user = User::factory()->create();
    
    expect($user->isAdmin())->toBeFalse();
});

test('admin user getUserPermissions returns all permissions', function () {
    $admin = createAdminUser();
    
    // Criar várias permissões
    createPermission('perm1');
    createPermission('perm2');
    createPermission('perm3');
    
    $permissions = $admin->getUserPermissions();
    
    expect($permissions)->toContain('perm1', 'perm2', 'perm3');
    expect(count($permissions))->toBeGreaterThanOrEqual(3);
});

test('admin can manage roles and permissions', function () {
    $admin = createAdminUser();
    
    // Admin pode criar role
    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test',
        ])
        ->assertRedirect();
    
    $roleId = DB::table('roles')->where('slug', 'test-role')->value('id');
    
    // Admin pode editar role
    $this->actingAs($admin)
        ->put(route('roles.update', $roleId), [
            'name' => 'Updated Role',
            'slug' => 'test-role',
            'description' => 'Updated',
        ])
        ->assertRedirect();
    
    // Admin pode deletar role
    $this->actingAs($admin)
        ->delete(route('roles.destroy', $roleId))
        ->assertRedirect();
});

test('admin can access dashboard with all data', function () {
    $admin = createAdminUser();
    
    $response = $this->actingAs($admin)->get(route('dashboard'));
    
    $response->assertOk();
    $props = $response->viewData('page')['props'];
    
    // Admin deve ver todos os dados
    expect($props)->toHaveKey('stats');
    expect($props)->toHaveKey('researchStats');
});

test('admin can access metricas with all data', function () {
    $admin = createAdminUser();
    
    $response = $this->actingAs($admin)->get(route('metricas.index'));
    
    $response->assertOk();
    $props = $response->viewData('page')['props'];
    
    // Admin deve ver todos os dados de métricas
    expect($props)->toHaveKey('overview');
    expect($props)->toHaveKey('nps');
});

