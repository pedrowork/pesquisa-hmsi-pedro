<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    createPermission('tipos-convenio.view');
    createPermission('tipos-convenio.create');
    createPermission('tipos-convenio.edit');
    createPermission('tipos-convenio.delete');
});

test('user without tipos-convenio.view cannot access tipos-convenio index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('tipos-convenio.index'))->assertStatus(403);
});

test('user with tipos-convenio.view can access tipos-convenio index', function () {
    $user = createUserWithPermission('tipos-convenio.view');
    $this->actingAs($user)->get(route('tipos-convenio.index'))->assertOk();
});

test('user without tipos-convenio.create cannot access tipos-convenio create form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('tipos-convenio.create'))->assertStatus(403);
});

test('user with tipos-convenio.create can access tipos-convenio create form', function () {
    $user = createUserWithPermission('tipos-convenio.create');
    $this->actingAs($user)->get(route('tipos-convenio.create'))->assertOk();
});

test('user without tipos-convenio.create cannot store new tipo convenio', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('tipos-convenio.store'), ['tipo_descricao' => 'Test Tipo'])
        ->assertStatus(403);
});

test('user with tipos-convenio.create can store new tipo convenio', function () {
    $user = createUserWithPermission('tipos-convenio.create');
    $this->actingAs($user)
        ->post(route('tipos-convenio.store'), ['tipo_descricao' => 'Test Tipo'])
        ->assertRedirect();
    
    $this->assertDatabaseHas('tipoconvenio', ['tipo_descricao' => 'Test Tipo']);
});

test('user without tipos-convenio.view cannot access tipo convenio show', function () {
    $user = User::factory()->create();
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test']);
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.show', $tipoId))
        ->assertStatus(403);
});

test('user with tipos-convenio.view can access tipo convenio show', function () {
    $user = createUserWithPermission('tipos-convenio.view');
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test']);
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.show', $tipoId))
        ->assertOk();
});

test('user without tipos-convenio.edit cannot access tipos-convenio edit form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('tipos-convenio.edit', 1))->assertStatus(403);
});

test('user with tipos-convenio.edit can access tipos-convenio edit form', function () {
    $user = createUserWithPermission('tipos-convenio.edit');
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.edit', (int)$tipoId))
        ->assertOk();
});

test('user without tipos-convenio.delete cannot delete tipo convenio', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->delete(route('tipos-convenio.destroy', 1))->assertStatus(403);
});

test('user with tipos-convenio.delete can delete tipo convenio', function () {
    $user = createUserWithPermission('tipos-convenio.delete');
    $tipoId = DB::table('tipoconvenio')->insertGetId([
        'tipo_descricao' => 'Test Tipo',
    ]);
    
    $this->actingAs($user)
        ->delete(route('tipos-convenio.destroy', $tipoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('tipoconvenio', ['cod' => $tipoId]);
});

test('user without tipos-convenio.edit cannot update tipo convenio', function () {
    $user = User::factory()->create();
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($user)
        ->put(route('tipos-convenio.update', $tipoId), [
            'tipo_descricao' => 'Updated Tipo',
        ])
        ->assertStatus(403);
});

test('user with tipos-convenio.edit can update tipo convenio', function () {
    $user = createUserWithPermission('tipos-convenio.edit');
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($user)
        ->put(route('tipos-convenio.update', $tipoId), [
            'tipo_descricao' => 'Updated Tipo',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('tipoconvenio', [
        'cod' => $tipoId,
        'tipo_descricao' => 'Updated Tipo',
    ]);
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('tipos-convenio.view');
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.create'))
        ->assertStatus(403);
    
    // Criar um tipo para testar show
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test']);
    $this->actingAs($user)
        ->get(route('tipos-convenio.show', (int)$tipoId))
        ->assertOk();
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('tipos-convenio.create');
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.edit', $tipoId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('tipos-convenio.update', $tipoId), [
            'tipo_descricao' => 'Updated',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('tipos-convenio.edit');
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.edit', $tipoId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('tipos-convenio.destroy', $tipoId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('tipoconvenio', [
        'cod' => $tipoId,
    ]);
});

test('admin can access all tipos-convenio routes', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->get(route('tipos-convenio.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('tipos-convenio.create'))
        ->assertOk();
    
    // Criar um tipo para testar show e edit
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($admin)
        ->get(route('tipos-convenio.show', (int)$tipoId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('tipos-convenio.edit', (int)$tipoId))
        ->assertOk();
});

test('admin can create tipos-convenio without tipos-convenio.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('tipos-convenio.store'), [
            'tipo_descricao' => 'Admin Created Tipo',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('tipoconvenio', [
        'tipo_descricao' => 'Admin Created Tipo',
    ]);
});

test('admin can update tipos-convenio without tipos-convenio.edit permission', function () {
    $admin = createAdminUser();
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($admin)
        ->put(route('tipos-convenio.update', $tipoId), [
            'tipo_descricao' => 'Admin Updated Tipo',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('tipoconvenio', [
        'cod' => $tipoId,
        'tipo_descricao' => 'Admin Updated Tipo',
    ]);
});

test('admin can delete tipos-convenio without tipos-convenio.delete permission', function () {
    $admin = createAdminUser();
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    
    $this->actingAs($admin)
        ->delete(route('tipos-convenio.destroy', $tipoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('tipoconvenio', [
        'cod' => $tipoId,
    ]);
});

test('admin has all tipos-convenio permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'tipos-convenio.view',
        'tipos-convenio.create',
        'tipos-convenio.edit',
        'tipos-convenio.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

// Testes para permissões via roles
test('user with permission via role can access tipos-convenio index', function () {
    $user = User::factory()->create();
    $roleId = createRole('tipos-convenio-viewer');
    assignRoleToUser($user, 'tipos-convenio-viewer');
    assignPermissionToRole($roleId, 'tipos-convenio.view');
    
    // Recarregar usuário para garantir que as permissões via role são carregadas
    $user = User::find($user->id);
    
    expect($user->hasPermission('tipos-convenio.view'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.index'))
        ->assertOk();
});

test('user with permission via role can access tipos-convenio create', function () {
    $user = User::factory()->create();
    $roleId = createRole('tipos-convenio-creator');
    assignRoleToUser($user, 'tipos-convenio-creator');
    assignPermissionToRole($roleId, 'tipos-convenio.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('tipos-convenio.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.create'))
        ->assertOk();
});

test('user with permission via role can store new tipo convenio', function () {
    $user = User::factory()->create();
    $roleId = createRole('tipos-convenio-creator');
    assignRoleToUser($user, 'tipos-convenio-creator');
    assignPermissionToRole($roleId, 'tipos-convenio.create');
    
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->post(route('tipos-convenio.store'), [
            'tipo_descricao' => 'Tipo via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('tipoconvenio', [
        'tipo_descricao' => 'Tipo via Role',
    ]);
});

test('user with permission via role can edit tipo convenio', function () {
    $user = User::factory()->create();
    $roleId = createRole('tipos-convenio-editor');
    assignRoleToUser($user, 'tipos-convenio-editor');
    assignPermissionToRole($roleId, 'tipos-convenio.edit');
    
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.edit', $tipoId))
        ->assertOk();
    
    $this->actingAs($user)
        ->put(route('tipos-convenio.update', $tipoId), [
            'tipo_descricao' => 'Updated via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('tipoconvenio', [
        'cod' => $tipoId,
        'tipo_descricao' => 'Updated via Role',
    ]);
});

test('user with permission via role can delete tipo convenio', function () {
    $user = User::factory()->create();
    $roleId = createRole('tipos-convenio-deleter');
    assignRoleToUser($user, 'tipos-convenio-deleter');
    assignPermissionToRole($roleId, 'tipos-convenio.delete');
    
    $tipoId = DB::table('tipoconvenio')->insertGetId(['tipo_descricao' => 'Test Tipo']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->delete(route('tipos-convenio.destroy', $tipoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('tipoconvenio', [
        'cod' => $tipoId,
    ]);
});

test('user with multiple roles gets union of permissions', function () {
    $user = User::factory()->create();
    $role1Id = createRole('tipos-convenio-viewer');
    $role2Id = createRole('tipos-convenio-creator');
    
    assignRoleToUser($user, 'tipos-convenio-viewer');
    assignRoleToUser($user, 'tipos-convenio-creator');
    assignPermissionToRole($role1Id, 'tipos-convenio.view');
    assignPermissionToRole($role2Id, 'tipos-convenio.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('tipos-convenio.view'))->toBeTrue();
    expect($user->hasPermission('tipos-convenio.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.create'))
        ->assertOk();
});

test('tipos-convenio.show returns 404 for non-existent tipo', function () {
    $user = createUserWithPermission('tipos-convenio.view');
    
    $this->actingAs($user)
        ->get(route('tipos-convenio.show', 99999))
        ->assertStatus(404);
});

