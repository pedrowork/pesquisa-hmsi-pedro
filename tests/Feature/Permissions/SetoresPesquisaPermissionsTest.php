<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    createPermission('setores-pesquisa.view');
    createPermission('setores-pesquisa.create');
    createPermission('setores-pesquisa.edit');
    createPermission('setores-pesquisa.delete');
});

test('user without setores-pesquisa.view cannot access setores-pesquisa index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('setores-pesquisa.index'))->assertStatus(403);
});

test('user with setores-pesquisa.view can access setores-pesquisa index', function () {
    $user = createUserWithPermission('setores-pesquisa.view');
    $this->actingAs($user)->get(route('setores-pesquisa.index'))->assertOk();
});

test('user without setores-pesquisa.create cannot access setores-pesquisa create form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('setores-pesquisa.create'))->assertStatus(403);
});

test('user with setores-pesquisa.create can access setores-pesquisa create form', function () {
    $user = createUserWithPermission('setores-pesquisa.create');
    $this->actingAs($user)->get(route('setores-pesquisa.create'))->assertOk();
});

test('user without setores-pesquisa.create cannot store new setor pesquisa', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('setores-pesquisa.store'), ['descricao' => 'Test Setor Pesquisa'])
        ->assertStatus(403);
});

test('user with setores-pesquisa.create can store new setor pesquisa', function () {
    $user = createUserWithPermission('setores-pesquisa.create');
    $this->actingAs($user)
        ->post(route('setores-pesquisa.store'), ['descricao' => 'Test Setor Pesquisa'])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor_pesquis', ['descricao' => 'Test Setor Pesquisa']);
});

test('user without setores-pesquisa.edit cannot access setores-pesquisa edit form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('setores-pesquisa.edit', 1))->assertStatus(403);
});

test('user with setores-pesquisa.edit can access setores-pesquisa edit form', function () {
    $user = createUserWithPermission('setores-pesquisa.edit');
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.edit', (int)$setorId))
        ->assertOk();
});

test('user without setores-pesquisa.delete cannot delete setor pesquisa', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->delete(route('setores-pesquisa.destroy', 1))->assertStatus(403);
});

test('user with setores-pesquisa.delete can delete setor pesquisa', function () {
    $user = createUserWithPermission('setores-pesquisa.delete');
    $setorId = DB::table('setor_pesquis')->insertGetId([
        'descricao' => 'Test Setor Pesquisa',
    ]);
    
    $this->actingAs($user)
        ->delete(route('setores-pesquisa.destroy', $setorId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('setor_pesquis', ['cod' => $setorId]);
});

test('user without setores-pesquisa.edit cannot update setor pesquisa', function () {
    $user = User::factory()->create();
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($user)
        ->put(route('setores-pesquisa.update', $setorId), [
            'descricao' => 'Updated Setor Pesquisa',
        ])
        ->assertStatus(403);
});

test('user with setores-pesquisa.edit can update setor pesquisa', function () {
    $user = createUserWithPermission('setores-pesquisa.edit');
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($user)
        ->put(route('setores-pesquisa.update', $setorId), [
            'descricao' => 'Updated Setor Pesquisa',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor_pesquis', [
        'cod' => $setorId,
        'descricao' => 'Updated Setor Pesquisa',
    ]);
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('setores-pesquisa.view');
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.create'))
        ->assertStatus(403);
    
    // Criar um setor para testar show
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor']);
    $this->actingAs($user)
        ->get(route('setores-pesquisa.show', $setorId))
        ->assertOk();
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('setores-pesquisa.create');
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.edit', $setorId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('setores-pesquisa.update', $setorId), [
            'descricao' => 'Updated',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('setores-pesquisa.edit');
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.edit', $setorId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('setores-pesquisa.destroy', $setorId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('setor_pesquis', [
        'cod' => $setorId,
    ]);
});

test('admin can access all setores-pesquisa routes', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->get(route('setores-pesquisa.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('setores-pesquisa.create'))
        ->assertOk();
    
    // Criar um setor para testar show e edit
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($admin)
        ->get(route('setores-pesquisa.show', (int)$setorId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('setores-pesquisa.edit', (int)$setorId))
        ->assertOk();
});

test('admin can create setores-pesquisa without setores-pesquisa.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('setores-pesquisa.store'), [
            'descricao' => 'Admin Created Setor Pesquisa',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor_pesquis', [
        'descricao' => 'Admin Created Setor Pesquisa',
    ]);
});

test('admin can update setores-pesquisa without setores-pesquisa.edit permission', function () {
    $admin = createAdminUser();
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($admin)
        ->put(route('setores-pesquisa.update', $setorId), [
            'descricao' => 'Admin Updated Setor Pesquisa',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor_pesquis', [
        'cod' => $setorId,
        'descricao' => 'Admin Updated Setor Pesquisa',
    ]);
});

test('admin can delete setores-pesquisa without setores-pesquisa.delete permission', function () {
    $admin = createAdminUser();
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    
    $this->actingAs($admin)
        ->delete(route('setores-pesquisa.destroy', $setorId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('setor_pesquis', [
        'cod' => $setorId,
    ]);
});

test('admin has all setores-pesquisa permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'setores-pesquisa.view',
        'setores-pesquisa.create',
        'setores-pesquisa.edit',
        'setores-pesquisa.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

// Testes para permissões via roles
test('user with permission via role can access setores-pesquisa index', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-pesquisa-viewer');
    assignRoleToUser($user, 'setores-pesquisa-viewer');
    assignPermissionToRole($roleId, 'setores-pesquisa.view');
    
    // Recarregar usuário para garantir que as permissões via role são carregadas
    $user = User::find($user->id);
    
    expect($user->hasPermission('setores-pesquisa.view'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.index'))
        ->assertOk();
});

test('user with permission via role can access setores-pesquisa create', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-pesquisa-creator');
    assignRoleToUser($user, 'setores-pesquisa-creator');
    assignPermissionToRole($roleId, 'setores-pesquisa.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('setores-pesquisa.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.create'))
        ->assertOk();
});

test('user with permission via role can store new setor pesquisa', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-pesquisa-creator');
    assignRoleToUser($user, 'setores-pesquisa-creator');
    assignPermissionToRole($roleId, 'setores-pesquisa.create');
    
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->post(route('setores-pesquisa.store'), [
            'descricao' => 'Setor Pesquisa via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor_pesquis', [
        'descricao' => 'Setor Pesquisa via Role',
    ]);
});

test('user with permission via role can edit setor pesquisa', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-pesquisa-editor');
    assignRoleToUser($user, 'setores-pesquisa-editor');
    assignPermissionToRole($roleId, 'setores-pesquisa.edit');
    
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.edit', $setorId))
        ->assertOk();
    
    $this->actingAs($user)
        ->put(route('setores-pesquisa.update', $setorId), [
            'descricao' => 'Updated via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('setor_pesquis', [
        'cod' => $setorId,
        'descricao' => 'Updated via Role',
    ]);
});

test('user with permission via role can delete setor pesquisa', function () {
    $user = User::factory()->create();
    $roleId = createRole('setores-pesquisa-deleter');
    assignRoleToUser($user, 'setores-pesquisa-deleter');
    assignPermissionToRole($roleId, 'setores-pesquisa.delete');
    
    $setorId = DB::table('setor_pesquis')->insertGetId(['descricao' => 'Test Setor Pesquisa']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->delete(route('setores-pesquisa.destroy', $setorId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('setor_pesquis', [
        'cod' => $setorId,
    ]);
});

test('user with multiple roles gets union of permissions', function () {
    $user = User::factory()->create();
    $role1Id = createRole('setores-pesquisa-viewer');
    $role2Id = createRole('setores-pesquisa-creator');
    
    assignRoleToUser($user, 'setores-pesquisa-viewer');
    assignRoleToUser($user, 'setores-pesquisa-creator');
    assignPermissionToRole($role1Id, 'setores-pesquisa.view');
    assignPermissionToRole($role2Id, 'setores-pesquisa.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('setores-pesquisa.view'))->toBeTrue();
    expect($user->hasPermission('setores-pesquisa.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.create'))
        ->assertOk();
});

test('setores-pesquisa.show returns 404 for non-existent setor', function () {
    $user = createUserWithPermission('setores-pesquisa.view');
    
    $this->actingAs($user)
        ->get(route('setores-pesquisa.show', 99999))
        ->assertStatus(404);
});

