<?php

use App\Models\User;

beforeEach(function () {
    \Illuminate\Support\Facades\DB::table('user_permissions')->delete();
    \Illuminate\Support\Facades\DB::table('role_permissions')->delete();
    \Illuminate\Support\Facades\DB::table('user_roles')->delete();
    \Illuminate\Support\Facades\DB::table('roles')->delete();
    \Illuminate\Support\Facades\DB::table('permissions')->delete();

    // Criar permissões de usuários
    createPermission('users.view');
    createPermission('users.create');
    createPermission('users.edit');
    createPermission('users.delete');
});

test('user without users.view cannot access users index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertStatus(403);
});

test('user with users.view can access users index', function () {
    $user = createUserWithPermission('users.view');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk();
});

test('user without users.view cannot access user show', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.show', $targetUser))
        ->assertStatus(403);
});

test('user with users.view can access user show', function () {
    $user = createUserWithPermission('users.view');
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.show', $targetUser))
        ->assertOk();
});

test('user without users.create cannot access users create form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.create'))
        ->assertStatus(403);
});

test('user with users.create can access users create form', function () {
    $user = createUserWithPermission('users.create');

    $this->actingAs($user)
        ->get(route('users.create'))
        ->assertOk();
});

test('user without users.create cannot store new user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertStatus(403);
});

test('user with users.create can store new user', function () {
    $user = createUserWithPermission('users.create');

    $uniqueEmail = 'test' . time() . '@example.com';

    $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => $uniqueEmail,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 1,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => $uniqueEmail,
    ]);
});

test('user without users.edit cannot access users edit form', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.edit', $targetUser))
        ->assertStatus(403);
});

test('user with users.edit can access users edit form', function () {
    $user = createUserWithPermission('users.edit');
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.edit', $targetUser))
        ->assertOk();
});

test('user without users.edit cannot update user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->put(route('users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
        ])
        ->assertStatus(403);
});

test('user with users.edit can update user', function () {
    $user = createUserWithPermission('users.edit');
    $targetUser = User::factory()->create();

    $originalName = $targetUser->name;
    $targetUserId = $targetUser->id;

    $this->actingAs($user)
        ->put(route('users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'status' => $targetUser->status ?? 1,
            'permissions' => [],
            'roles' => [],
        ])
        ->assertRedirect();

    // Recarregar do banco para garantir que temos os dados atualizados
    $targetUser = User::find($targetUserId);

    // Verificar que o nome foi realmente alterado
    expect($targetUser)->not->toBeNull();
    expect($targetUser->name)->toBe('Updated Name');
    expect($targetUser->name)->not->toBe($originalName);

    // Verificar no banco diretamente
    $this->assertDatabaseHas('users', [
        'id' => $targetUserId,
        'name' => 'Updated Name',
    ]);
});

test('user without users.delete cannot delete user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('users.destroy', $targetUser))
        ->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
    ]);
});

test('user with users.delete can delete user', function () {
    $user = createUserWithPermission('users.delete');
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('users.destroy', $targetUser))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});

test('admin can access all users routes', function () {
    $admin = createAdminUser();
    $targetUser = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('users.show', $targetUser))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('users.create'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('users.edit', $targetUser))
        ->assertOk();
});

test('admin can create users without users.create permission', function () {
    $admin = createAdminUser();
    $uniqueEmail = 'admin-created' . time() . '@example.com';

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Admin Created User',
            'email' => $uniqueEmail,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 1,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => $uniqueEmail,
    ]);
});

test('admin can update users without users.edit permission', function () {
    $admin = createAdminUser();
    $targetUser = User::factory()->create();
    $targetUserId = $targetUser->id;

    $this->actingAs($admin)
        ->put(route('users.update', $targetUser), [
            'name' => 'Admin Updated Name',
            'email' => $targetUser->email,
            'status' => $targetUser->status ?? 1,
            'permissions' => [],
            'roles' => [],
        ])
        ->assertRedirect();

    // Recarregar do banco
    $updatedUser = User::find($targetUserId);
    expect($updatedUser)->not->toBeNull();
    expect($updatedUser->name)->toBe('Admin Updated Name');

    $this->assertDatabaseHas('users', [
        'id' => $targetUserId,
        'name' => 'Admin Updated Name',
    ]);
});

test('admin can delete users without users.delete permission', function () {
    $admin = createAdminUser();
    $targetUser = User::factory()->create();
    $targetUserId = $targetUser->id;

    $this->actingAs($admin)
        ->delete(route('users.destroy', $targetUser))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', [
        'id' => $targetUserId,
    ]);
});

test('admin has all users permissions implicitly', function () {
    $admin = createAdminUser();

    $permissions = [
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
    ];

    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('users.view');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('users.create'))
        ->assertStatus(403);

    $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('users.create');
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.create'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('users.edit', $targetUser))
        ->assertStatus(403);

    $this->actingAs($user)
        ->put(route('users.update', $targetUser), [
            'name' => 'Updated',
            'email' => $targetUser->email,
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('users.edit');
    $targetUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.edit', $targetUser))
        ->assertOk();

    $this->actingAs($user)
        ->delete(route('users.destroy', $targetUser))
        ->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
    ]);
});

test('user with users.create can create user with roles', function () {
    $user = createUserWithPermission('users.create');
    $roleId = createRole('test-role');

    $uniqueEmail = 'test-role' . time() . '@example.com';

    $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'User With Role',
            'email' => $uniqueEmail,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 1,
            'roles' => [$roleId],
        ])
        ->assertRedirect();

    $createdUser = User::where('email', $uniqueEmail)->first();
    expect($createdUser)->not->toBeNull();

    $hasRole = \Illuminate\Support\Facades\DB::table('user_roles')
        ->where('user_id', $createdUser->id)
        ->where('role_id', $roleId)
        ->exists();

    expect($hasRole)->toBeTrue();
});

test('user with users.edit can update user permissions and roles', function () {
    $user = createUserWithPermission('users.edit');
    $targetUser = User::factory()->create();
    $targetUserId = $targetUser->id;
    $permissionId = createPermission('test.permission');
    $roleId = createRole('test-role');

    $this->actingAs($user)
        ->put(route('users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'status' => $targetUser->status ?? 1,
            'permissions' => [$permissionId],
            'roles' => [$roleId],
        ])
        ->assertRedirect();

    // Verificar permissões
    $hasPermission = \Illuminate\Support\Facades\DB::table('user_permissions')
        ->where('user_id', $targetUserId)
        ->where('permission_id', $permissionId)
        ->exists();

    expect($hasPermission)->toBeTrue("Permissão deveria ter sido atribuída ao usuário");

    // Verificar roles
    $hasRole = \Illuminate\Support\Facades\DB::table('user_roles')
        ->where('user_id', $targetUserId)
        ->where('role_id', $roleId)
        ->exists();

    expect($hasRole)->toBeTrue("Role deveria ter sido atribuída ao usuário");
});

