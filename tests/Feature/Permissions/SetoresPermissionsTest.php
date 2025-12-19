<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    createPermission('setores.view');
    createPermission('setores.create');
    createPermission('setores.edit');
    createPermission('setores.delete');
});

test('user without setores.view cannot access setores index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('setores.index'))->assertStatus(403);
});

test('user with setores.view can access setores index', function () {
    $user = createUserWithPermission('setores.view');
    $this->actingAs($user)->get(route('setores.index'))->assertOk();
});

test('user without setores.create cannot access setores create form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('setores.create'))->assertStatus(403);
});

test('user with setores.create can access setores create form', function () {
    $user = createUserWithPermission('setores.create');
    
    $this->actingAs($user)
        ->get(route('setores.create'))
        ->assertOk();
});

test('user without setores.create cannot store new setor', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('setores.store'), ['descricao' => 'Test Setor'])
        ->assertStatus(403);
});

test('user with setores.create can store new setor', function () {
    $user = createUserWithPermission('setores.create');
    $this->actingAs($user)
        ->post(route('setores.store'), ['descricao' => 'Test Setor'])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor', ['descricao' => 'Test Setor']);
});

test('user without setores.edit cannot access setores edit form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('setores.edit', 1))->assertStatus(403);
});

test('user with setores.edit can access setores edit form', function () {
    $user = createUserWithPermission('setores.edit');
    
    // Criar um setor primeiro
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($user)->get(route('setores.edit', (int)$setorId))->assertOk();
});

test('user without setores.delete cannot delete setor', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->delete(route('setores.destroy', 1))->assertStatus(403);
});

test('user with setores.delete can delete setor', function () {
    $user = createUserWithPermission('setores.delete');
    $setorId = DB::table('setor')->insertGetId([
        'descricao' => 'Test Setor',
    ]);
    
    $this->actingAs($user)
        ->delete(route('setores.destroy', $setorId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('setor', ['cod' => $setorId]);
});

test('user without setores.edit cannot update setor', function () {
    $user = User::factory()->create();
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($user)
        ->put(route('setores.update', $setorId), [
            'descricao' => 'Updated Setor',
        ])
        ->assertStatus(403);
});

test('user with setores.edit can update setor', function () {
    $user = createUserWithPermission('setores.edit');
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($user)
        ->put(route('setores.update', $setorId), [
            'descricao' => 'Updated Setor',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor', [
        'cod' => $setorId,
        'descricao' => 'Updated Setor',
    ]);
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('setores.view');
    
    $this->actingAs($user)
        ->get(route('setores.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('setores.create'))
        ->assertStatus(403);
    
    // Criar um setor para testar show
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test']);
    $this->actingAs($user)
        ->get(route('setores.show', (int)$setorId))
        ->assertOk();
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('setores.create');
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($user)
        ->get(route('setores.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('setores.edit', $setorId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('setores.update', $setorId), [
            'descricao' => 'Updated',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('setores.edit');
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($user)
        ->get(route('setores.edit', $setorId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('setores.destroy', $setorId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('setor', [
        'cod' => $setorId,
    ]);
});

test('admin can access all setores routes', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->get(route('setores.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('setores.create'))
        ->assertOk();
    
    // Criar um setor para testar show e edit
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($admin)
        ->get(route('setores.show', (int)$setorId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('setores.edit', (int)$setorId))
        ->assertOk();
});

test('admin can create setores without setores.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('setores.store'), [
            'descricao' => 'Admin Created Setor',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor', [
        'descricao' => 'Admin Created Setor',
    ]);
});

test('admin can update setores without setores.edit permission', function () {
    $admin = createAdminUser();
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($admin)
        ->put(route('setores.update', $setorId), [
            'descricao' => 'Admin Updated Setor',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor', [
        'cod' => $setorId,
        'descricao' => 'Admin Updated Setor',
    ]);
});

test('admin can delete setores without setores.delete permission', function () {
    $admin = createAdminUser();
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    
    $this->actingAs($admin)
        ->delete(route('setores.destroy', $setorId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('setor', [
        'cod' => $setorId,
    ]);
});

test('admin has all setores permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'setores.view',
        'setores.create',
        'setores.edit',
        'setores.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

// Testes para permissões via roles
test('user with permission via role can access setores index', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-viewer');
    assignRoleToUser($user, 'setores-viewer');
    assignPermissionToRole($roleId, 'setores.view');
    
    // Recarregar usuário para garantir que as permissões via role são carregadas
    $user = User::find($user->id);
    
    expect($user->hasPermission('setores.view'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('setores.index'))
        ->assertOk();
});

test('user with permission via role can access setores create', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-creator');
    assignRoleToUser($user, 'setores-creator');
    assignPermissionToRole($roleId, 'setores.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('setores.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('setores.create'))
        ->assertOk();
});

test('user with permission via role can store new setor', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-creator');
    assignRoleToUser($user, 'setores-creator');
    assignPermissionToRole($roleId, 'setores.create');
    
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->post(route('setores.store'), [
            'descricao' => 'Setor via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor', [
        'descricao' => 'Setor via Role',
    ]);
});

test('user with permission via role can edit setor', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-editor');
    assignRoleToUser($user, 'setores-editor');
    assignPermissionToRole($roleId, 'setores.edit');
    
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->get(route('setores.edit', $setorId))
        ->assertOk();
    
    $this->actingAs($user)
        ->put(route('setores.update', $setorId), [
            'descricao' => 'Updated via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor', [
        'cod' => $setorId,
        'descricao' => 'Updated via Role',
    ]);
});

test('user with permission via role can delete setor', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-deleter');
    assignRoleToUser($user, 'setores-deleter');
    assignPermissionToRole($roleId, 'setores.delete');
    
    $setorId = DB::table('setor')->insertGetId(['descricao' => 'Test Setor']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->delete(route('setores.destroy', $setorId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('setor', [
        'cod' => $setorId,
    ]);
});

test('user with multiple roles gets union of permissions', function () {
    $user = User::factory()->create();
    $role1Id = createRole('setores-viewer');
    $role2Id = createRole('setores-creator');
    
    assignRoleToUser($user, 'setores-viewer');
    assignRoleToUser($user, 'setores-creator');
    assignPermissionToRole($role1Id, 'setores.view');
    assignPermissionToRole($role2Id, 'setores.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('setores.view'))->toBeTrue();
    expect($user->hasPermission('setores.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('setores.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('setores.create'))
        ->assertOk();
});

test('setores.show returns 404 for non-existent setor', function () {
    $user = createUserWithPermission('setores.view');
    
    $this->actingAs($user)
        ->get(route('setores.show', 99999))
        ->assertStatus(404);
});

