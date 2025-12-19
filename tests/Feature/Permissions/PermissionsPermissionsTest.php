<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    // Criar permissões de permissões
    createPermission('permissions.view');
    createPermission('permissions.create');
    createPermission('permissions.edit');
    createPermission('permissions.delete');
});

test('user without permissions.view cannot access permissions index', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('permissions.index'))
        ->assertStatus(403);
});

test('user with permissions.view can access permissions index', function () {
    $user = createUserWithPermission('permissions.view');
    
    $this->actingAs($user)
        ->get(route('permissions.index'))
        ->assertOk();
});

test('user without permissions.view cannot access permission show', function () {
    $user = User::factory()->create();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->get(route('permissions.show', $permissionId))
        ->assertStatus(403);
});

test('user with permissions.view can access permission show', function () {
    $user = createUserWithPermission('permissions.view');
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->get(route('permissions.show', $permissionId))
        ->assertOk();
});

test('user without permissions.create cannot access permissions create form', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('permissions.create'))
        ->assertStatus(403);
});

test('user with permissions.create can access permissions create form', function () {
    $user = createUserWithPermission('permissions.create');
    
    $this->actingAs($user)
        ->get(route('permissions.create'))
        ->assertOk();
});

test('user without permissions.create cannot store new permission', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post(route('permissions.store'), [
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'description' => 'Test description',
        ])
        ->assertStatus(403);
});

test('user with permissions.create can store new permission', function () {
    $user = createUserWithPermission('permissions.create');
    
    $this->actingAs($user)
        ->post(route('permissions.store'), [
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'description' => 'Test description',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('permissions', [
        'slug' => 'test.permission',
    ]);
});

test('user without permissions.edit cannot access permissions edit form', function () {
    $user = User::factory()->create();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->get(route('permissions.edit', $permissionId))
        ->assertStatus(403);
});

test('user with permissions.edit can access permissions edit form', function () {
    $user = createUserWithPermission('permissions.edit');
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->get(route('permissions.edit', $permissionId))
        ->assertOk();
});

test('user without permissions.edit cannot update permission', function () {
    $user = User::factory()->create();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->put(route('permissions.update', $permissionId), [
            'name' => 'Updated Permission',
            'slug' => 'test.permission',
            'description' => 'Updated description',
        ])
        ->assertStatus(403);
});

test('user with permissions.edit can update permission', function () {
    $user = createUserWithPermission('permissions.edit');
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->put(route('permissions.update', $permissionId), [
            'name' => 'Updated Permission',
            'slug' => 'test.permission',
            'description' => 'Updated description',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('permissions', [
        'id' => $permissionId,
        'name' => 'Updated Permission',
    ]);
});

test('user without permissions.delete cannot delete permission', function () {
    $user = User::factory()->create();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->delete(route('permissions.destroy', $permissionId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('permissions', [
        'id' => $permissionId,
    ]);
});

test('user with permissions.delete can delete permission', function () {
    $user = createUserWithPermission('permissions.delete');
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->delete(route('permissions.destroy', $permissionId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('permissions', [
        'id' => $permissionId,
    ]);
});

test('admin can access all permissions routes', function () {
    $admin = createAdminUser();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($admin)
        ->get(route('permissions.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('permissions.show', $permissionId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('permissions.create'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('permissions.edit', $permissionId))
        ->assertOk();
});

test('admin can create permissions without permissions.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('permissions.store'), [
            'name' => 'Admin Created Permission',
            'slug' => 'admin.created.permission',
            'description' => 'Created by admin',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('permissions', [
        'slug' => 'admin.created.permission',
        'name' => 'Admin Created Permission',
    ]);
});

test('admin can update permissions without permissions.edit permission', function () {
    $admin = createAdminUser();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($admin)
        ->put(route('permissions.update', $permissionId), [
            'name' => 'Admin Updated Permission',
            'slug' => 'test.permission',
            'description' => 'Updated by admin',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('permissions', [
        'id' => $permissionId,
        'name' => 'Admin Updated Permission',
    ]);
});

test('admin can delete permissions without permissions.delete permission', function () {
    $admin = createAdminUser();
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($admin)
        ->delete(route('permissions.destroy', $permissionId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('permissions', [
        'id' => $permissionId,
    ]);
});

test('admin has all permissions permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'permissions.view',
        'permissions.create',
        'permissions.edit',
        'permissions.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('permissions.view');
    
    $this->actingAs($user)
        ->get(route('permissions.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('permissions.create'))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->post(route('permissions.store'), [
            'name' => 'Test',
            'slug' => 'test',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('permissions.create');
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->get(route('permissions.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('permissions.edit', $permissionId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('permissions.update', $permissionId), [
            'name' => 'Updated',
            'slug' => 'test.permission',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('permissions.edit');
    $permissionId = (int)createPermission('test.permission');
    
    $this->actingAs($user)
        ->get(route('permissions.edit', $permissionId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('permissions.destroy', $permissionId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('permissions', [
        'id' => $permissionId,
    ]);
});

test('permission deletion removes associated role permissions and user permissions', function () {
    $user = createUserWithPermission('permissions.delete');
    $permissionId = (int)createPermission('test.permission');
    $roleId = createRole('test-role');
    $targetUser = User::factory()->create();
    
    // Adicionar permissão à role
    assignPermissionToRole($roleId, 'test.permission');
    
    // Adicionar permissão diretamente ao usuário
    assignPermissionToUser($targetUser, 'test.permission');
    
    // Deletar permissão
    $this->actingAs($user)
        ->delete(route('permissions.destroy', $permissionId))
        ->assertRedirect();
    
    // Verificar que permissão foi deletada
    $this->assertDatabaseMissing('permissions', [
        'id' => $permissionId,
    ]);
    
    // Verificar que role_permissions foram removidos
    $hasRolePermission = DB::table('role_permissions')
        ->where('permission_id', $permissionId)
        ->exists();
    
    expect($hasRolePermission)->toBeFalse();
    
    // Verificar que user_permissions foram removidos
    $hasUserPermission = DB::table('user_permissions')
        ->where('permission_id', $permissionId)
        ->exists();
    
    expect($hasUserPermission)->toBeFalse();
});

