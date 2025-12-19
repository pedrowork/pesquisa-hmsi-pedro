<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();

    // Criar permissões de roles
    createPermission('roles.view');
    createPermission('roles.create');
    createPermission('roles.edit');
    createPermission('roles.delete');
});

test('user without roles.view cannot access roles index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertStatus(403);
});

test('user with roles.view can access roles index', function () {
    $user = createUserWithPermission('roles.view');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk();
});

test('user without roles.view cannot access role show', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->get(route('roles.show', $roleId))
        ->assertStatus(403);
});

test('user with roles.view can access role show', function () {
    $user = createUserWithPermission('roles.view');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->get(route('roles.show', $roleId))
        ->assertOk();
});

test('user without roles.create cannot access roles create form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertStatus(403);
});

test('user with roles.create can access roles create form', function () {
    $user = createUserWithPermission('roles.create');

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk();
});

test('user without roles.create cannot store new role', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test description',
        ])
        ->assertStatus(403);
});

test('user with roles.create can store new role', function () {
    $user = createUserWithPermission('roles.create');

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test description',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'slug' => 'test-role',
    ]);
});

test('user without roles.edit cannot access roles edit form', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->get(route('roles.edit', $roleId))
        ->assertStatus(403);
});

test('user with roles.edit can access roles edit form', function () {
    $user = createUserWithPermission('roles.edit');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->get(route('roles.edit', $roleId))
        ->assertOk();
});

test('user without roles.edit cannot update role', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->put(route('roles.update', $roleId), [
            'name' => 'Updated Role',
            'slug' => 'test-role',
            'description' => 'Updated description',
        ])
        ->assertStatus(403);
});

test('user with roles.edit can update role', function () {
    $user = createUserWithPermission('roles.edit');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->put(route('roles.update', $roleId), [
            'name' => 'Updated Role',
            'slug' => 'test-role',
            'description' => 'Updated description',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'id' => $roleId,
        'name' => 'Updated Role',
    ]);
});

test('user without roles.delete cannot delete role', function () {
    $user = User::factory()->create();
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->delete(route('roles.destroy', $roleId))
        ->assertStatus(403);

    $this->assertDatabaseHas('roles', [
        'id' => $roleId,
    ]);
});

test('user with roles.delete can delete role', function () {
    $user = createUserWithPermission('roles.delete');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->delete(route('roles.destroy', $roleId))
        ->assertRedirect();

    $this->assertDatabaseMissing('roles', [
        'id' => $roleId,
    ]);
});

test('admin can access all roles routes', function () {
    $admin = createAdminUser();
    $roleId = createRole('test-role');

    $this->actingAs($admin)
        ->get(route('roles.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('roles.show', $roleId))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('roles.create'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('roles.edit', $roleId))
        ->assertOk();
});

test('admin can create roles without roles.create permission', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'Admin Created Role',
            'slug' => 'admin-created-role',
            'description' => 'Created by admin',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'slug' => 'admin-created-role',
        'name' => 'Admin Created Role',
    ]);
});

test('admin can update roles without roles.edit permission', function () {
    $admin = createAdminUser();
    $roleId = createRole('test-role');

    $this->actingAs($admin)
        ->put(route('roles.update', $roleId), [
            'name' => 'Admin Updated Role',
            'slug' => 'test-role',
            'description' => 'Updated by admin',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'id' => $roleId,
        'name' => 'Admin Updated Role',
    ]);
});

test('admin can delete roles without roles.delete permission', function () {
    $admin = createAdminUser();
    $roleId = createRole('test-role');

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $roleId))
        ->assertRedirect();

    $this->assertDatabaseMissing('roles', [
        'id' => $roleId,
    ]);
});

test('admin has all roles permissions implicitly', function () {
    $admin = createAdminUser();

    $permissions = [
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
    ];

    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('roles.view');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertStatus(403);

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'Test',
            'slug' => 'test',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('roles.create');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('roles.edit', $roleId))
        ->assertStatus(403);

    $this->actingAs($user)
        ->put(route('roles.update', $roleId), [
            'name' => 'Updated',
            'slug' => 'test-role',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('roles.edit');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->get(route('roles.edit', $roleId))
        ->assertOk();

    $this->actingAs($user)
        ->delete(route('roles.destroy', $roleId))
        ->assertStatus(403);

    $this->assertDatabaseHas('roles', [
        'id' => $roleId,
    ]);
});

test('user with roles.create can create role with permissions', function () {
    $user = createUserWithPermission('roles.create');
    $permissionId = createPermission('test.permission');

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'Role With Permission',
            'slug' => 'role-with-permission',
            'description' => 'Test role',
            'permissions' => [$permissionId],
        ])
        ->assertRedirect();

    $createdRole = DB::table('roles')->where('slug', 'role-with-permission')->first();
    expect($createdRole)->not->toBeNull();

    $hasPermission = DB::table('role_permissions')
        ->where('role_id', $createdRole->id)
        ->where('permission_id', $permissionId)
        ->exists();

    expect($hasPermission)->toBeTrue();
});

test('user with roles.edit can update role permissions', function () {
    $user = createUserWithPermission('roles.edit');
    $roleId = createRole('test-role');
    $permissionId1 = createPermission('test.permission.1');
    $permissionId2 = createPermission('test.permission.2');

    // Adicionar primeira permissão
    assignPermissionToRole($roleId, 'test.permission.1');

    // Atualizar para ter ambas as permissões
    $this->actingAs($user)
        ->put(route('roles.update', $roleId), [
            'name' => 'Updated Role',
            'slug' => 'test-role',
            'description' => 'Updated',
            'permissions' => [$permissionId1, $permissionId2],
        ])
        ->assertRedirect();

    $hasPermission1 = DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->where('permission_id', $permissionId1)
        ->exists();

    $hasPermission2 = DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->where('permission_id', $permissionId2)
        ->exists();

    expect($hasPermission1)->toBeTrue();
    expect($hasPermission2)->toBeTrue();

    // Verificar que tem exatamente 2 permissões
    $permissionsCount = DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->count();

    expect($permissionsCount)->toBe(2);
});

test('user with roles.edit can remove all permissions from role', function () {
    $user = createUserWithPermission('roles.edit');
    $roleId = createRole('test-role');
    $permissionId = createPermission('test.permission');

    // Adicionar permissão inicial
    assignPermissionToRole($roleId, 'test.permission');

    // Remover todas as permissões
    $this->actingAs($user)
        ->put(route('roles.update', $roleId), [
            'name' => 'Role Without Permissions',
            'slug' => 'test-role',
            'description' => 'No permissions',
            'permissions' => [],
        ])
        ->assertRedirect();

    $permissionsCount = DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->count();

    expect($permissionsCount)->toBe(0);
});

test('role deletion removes associated permissions and user roles', function () {
    $user = createUserWithPermission('roles.delete');
    $roleId = createRole('test-role');
    $permissionId = createPermission('test.permission');
    $targetUser = User::factory()->create();

    // Adicionar permissão à role
    assignPermissionToRole($roleId, 'test.permission');

    // Adicionar role ao usuário
    assignRoleToUser($targetUser, 'test-role');

    // Deletar role
    $this->actingAs($user)
        ->delete(route('roles.destroy', $roleId))
        ->assertRedirect();

    // Verificar que role foi deletada
    $this->assertDatabaseMissing('roles', [
        'id' => $roleId,
    ]);

    // Verificar que permissões da role foram removidas
    $hasPermission = DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->exists();

    expect($hasPermission)->toBeFalse();

    // Verificar que user_roles foram removidos
    $hasUserRole = DB::table('user_roles')
        ->where('role_id', $roleId)
        ->exists();

    expect($hasUserRole)->toBeFalse();
});

