<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    // Criar permissões granulares de leitos
    createPermission('leitos.view');
    createPermission('leitos.create');
    createPermission('leitos.edit');
    createPermission('leitos.delete');
});

test('user without leitos.view cannot access leitos index', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('leitos.index'))
        ->assertStatus(403);
});

test('user with leitos.view can access leitos index', function () {
    $user = createUserWithPermission('leitos.view');
    
    $this->actingAs($user)
        ->get(route('leitos.index'))
        ->assertOk();
});

test('user without leitos.view cannot access leito show', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('leitos.show', 1))
        ->assertStatus(403);
});

test('user with leitos.view can access leito show', function () {
    $user = createUserWithPermission('leitos.view');
    
    // Criar um leito primeiro
    $leitoId = DB::table('leito')->insertGetId([
        'descricao' => 'Test Leito',
    ]);
    
    $this->actingAs($user)
        ->get(route('leitos.show', $leitoId))
        ->assertOk();
});

test('user without leitos.create cannot access leitos create form', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('leitos.create'))
        ->assertStatus(403);
});

test('user with leitos.create can access leitos create form', function () {
    $user = createUserWithPermission('leitos.create');
    
    $this->actingAs($user)
        ->get(route('leitos.create'))
        ->assertOk();
});

test('user without leitos.create cannot store new leito', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post(route('leitos.store'), [
            'descricao' => 'Test Leito',
        ])
        ->assertStatus(403);
});

test('user with leitos.create can store new leito', function () {
    $user = createUserWithPermission('leitos.create');
    
    $this->actingAs($user)
        ->post(route('leitos.store'), [
            'descricao' => 'Test Leito',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('leito', [
        'descricao' => 'Test Leito',
    ]);
});

test('user without leitos.edit cannot access leitos edit form', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('leitos.edit', 1))
        ->assertStatus(403);
});

test('user with leitos.edit can access leitos edit form', function () {
    $user = createUserWithPermission('leitos.edit');
    
    // Criar um leito primeiro
    $leitoId = DB::table('leito')->insertGetId([
        'descricao' => 'Test Leito',
    ]);
    
    $this->actingAs($user)
        ->get(route('leitos.edit', $leitoId))
        ->assertOk();
});

test('user without leitos.edit cannot update leito', function () {
    $user = User::factory()->create();
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($user)
        ->put(route('leitos.update', $leitoId), [
            'descricao' => 'Updated Leito',
        ])
        ->assertStatus(403);
});

test('user with leitos.edit can update leito', function () {
    $user = createUserWithPermission('leitos.edit');
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($user)
        ->put(route('leitos.update', $leitoId), [
            'descricao' => 'Updated Leito',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('leito', [
        'cod' => $leitoId,
        'descricao' => 'Updated Leito',
    ]);
});

test('user without leitos.delete cannot delete leito', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->delete(route('leitos.destroy', 1))
        ->assertStatus(403);
});

test('user with leitos.delete can delete leito', function () {
    $user = createUserWithPermission('leitos.delete');
    
    // Criar um leito primeiro
    $leitoId = DB::table('leito')->insertGetId([
        'descricao' => 'Test Leito',
    ]);
    
    $this->actingAs($user)
        ->delete(route('leitos.destroy', $leitoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('leito', [
        'cod' => $leitoId,
    ]);
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('leitos.view');
    
    $this->actingAs($user)
        ->get(route('leitos.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('leitos.create'))
        ->assertStatus(403);
    
    // Criar um leito para testar show
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test']);
    $this->actingAs($user)
        ->get(route('leitos.show', (int)$leitoId))
        ->assertOk();
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('leitos.create');
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($user)
        ->get(route('leitos.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('leitos.edit', $leitoId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('leitos.update', $leitoId), [
            'descricao' => 'Updated',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('leitos.edit');
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($user)
        ->get(route('leitos.edit', $leitoId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('leitos.destroy', $leitoId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('leito', [
        'cod' => $leitoId,
    ]);
});

test('admin can access all leitos routes', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->get(route('leitos.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('leitos.create'))
        ->assertOk();
    
    // Criar um leito para testar show e edit
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($admin)
        ->get(route('leitos.show', (int)$leitoId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('leitos.edit', (int)$leitoId))
        ->assertOk();
});

test('admin can create leitos without leitos.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('leitos.store'), [
            'descricao' => 'Admin Created Leito',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('leito', [
        'descricao' => 'Admin Created Leito',
    ]);
});

test('admin can update leitos without leitos.edit permission', function () {
    $admin = createAdminUser();
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($admin)
        ->put(route('leitos.update', $leitoId), [
            'descricao' => 'Admin Updated Leito',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('leito', [
        'cod' => $leitoId,
        'descricao' => 'Admin Updated Leito',
    ]);
});

test('admin can delete leitos without leitos.delete permission', function () {
    $admin = createAdminUser();
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    
    $this->actingAs($admin)
        ->delete(route('leitos.destroy', $leitoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('leito', [
        'cod' => $leitoId,
    ]);
});

test('admin has all leitos permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'leitos.view',
        'leitos.create',
        'leitos.edit',
        'leitos.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

// Testes para permissões via roles
test('user with permission via role can access leitos index', function () {
    $user = User::factory()->create();
    $roleId = createRole('leitos-viewer');
    assignRoleToUser($user, 'leitos-viewer');
    assignPermissionToRole($roleId, 'leitos.view');
    
    // Recarregar usuário para garantir que as permissões via role são carregadas
    $user = User::find($user->id);
    
    expect($user->hasPermission('leitos.view'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('leitos.index'))
        ->assertOk();
});

test('user with permission via role can access leitos create', function () {
    $user = User::factory()->create();
    $roleId = createRole('leitos-creator');
    assignRoleToUser($user, 'leitos-creator');
    assignPermissionToRole($roleId, 'leitos.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('leitos.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('leitos.create'))
        ->assertOk();
});

test('user with permission via role can store new leito', function () {
    $user = User::factory()->create();
    $roleId = createRole('leitos-creator');
    assignRoleToUser($user, 'leitos-creator');
    assignPermissionToRole($roleId, 'leitos.create');
    
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->post(route('leitos.store'), [
            'descricao' => 'Leito via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('leito', [
        'descricao' => 'Leito via Role',
    ]);
});

test('user with permission via role can edit leito', function () {
    $user = User::factory()->create();
    $roleId = createRole('leitos-editor');
    assignRoleToUser($user, 'leitos-editor');
    assignPermissionToRole($roleId, 'leitos.edit');
    
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->get(route('leitos.edit', $leitoId))
        ->assertOk();
    
    $this->actingAs($user)
        ->put(route('leitos.update', $leitoId), [
            'descricao' => 'Updated via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('leito', [
        'cod' => $leitoId,
        'descricao' => 'Updated via Role',
    ]);
});

test('user with permission via role can delete leito', function () {
    $user = User::factory()->create();
    $roleId = createRole('leitos-deleter');
    assignRoleToUser($user, 'leitos-deleter');
    assignPermissionToRole($roleId, 'leitos.delete');
    
    $leitoId = DB::table('leito')->insertGetId(['descricao' => 'Test Leito']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->delete(route('leitos.destroy', $leitoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('leito', [
        'cod' => $leitoId,
    ]);
});

test('user with multiple roles gets union of permissions', function () {
    $user = User::factory()->create();
    $role1Id = createRole('leitos-viewer');
    $role2Id = createRole('leitos-creator');
    
    assignRoleToUser($user, 'leitos-viewer');
    assignRoleToUser($user, 'leitos-creator');
    assignPermissionToRole($role1Id, 'leitos.view');
    assignPermissionToRole($role2Id, 'leitos.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('leitos.view'))->toBeTrue();
    expect($user->hasPermission('leitos.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('leitos.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('leitos.create'))
        ->assertOk();
});

test('leitos.show returns 404 for non-existent leito', function () {
    $user = createUserWithPermission('leitos.view');
    
    $this->actingAs($user)
        ->get(route('leitos.show', 99999))
        ->assertStatus(404);
});

