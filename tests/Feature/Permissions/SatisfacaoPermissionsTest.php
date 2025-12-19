<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    createPermission('satisfacao.view');
    createPermission('satisfacao.create');
    createPermission('satisfacao.edit');
    createPermission('satisfacao.delete');
});

test('user without satisfacao.view cannot access satisfacao index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('satisfacao.index'))->assertStatus(403);
});

test('user with satisfacao.view can access satisfacao index', function () {
    $user = createUserWithPermission('satisfacao.view');
    $this->actingAs($user)->get(route('satisfacao.index'))->assertOk();
});

test('user without satisfacao.create cannot access satisfacao create form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('satisfacao.create'))->assertStatus(403);
});

test('user with satisfacao.create can access satisfacao create form', function () {
    $user = createUserWithPermission('satisfacao.create');
    
    $this->actingAs($user)
        ->get(route('satisfacao.create'))
        ->assertOk();
});

test('user without satisfacao.create cannot store new satisfacao', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('satisfacao.store'), ['descricao' => 'Test Satisfacao'])
        ->assertStatus(403);
});

test('user with satisfacao.create can store new satisfacao', function () {
    $user = createUserWithPermission('satisfacao.create');
    $this->actingAs($user)
        ->post(route('satisfacao.store'), [
            'descricao' => 'Test Satisfacao',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('satisfacao', ['descricao' => 'Test Satisfacao']);
});

test('user without satisfacao.edit cannot access satisfacao edit form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('satisfacao.edit', 1))->assertStatus(403);
});

test('user with satisfacao.edit can access satisfacao edit form', function () {
    $user = createUserWithPermission('satisfacao.edit');
    
    // Criar uma satisfacao primeiro
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($user)->get(route('satisfacao.edit', (int)$satisfacaoId))->assertOk();
});

test('user without satisfacao.delete cannot delete satisfacao', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->delete(route('satisfacao.destroy', 1))->assertStatus(403);
});

test('user with satisfacao.delete can delete satisfacao', function () {
    $user = createUserWithPermission('satisfacao.delete');
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($user)
        ->delete(route('satisfacao.destroy', $satisfacaoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('satisfacao', ['cod' => $satisfacaoId]);
});

test('user without satisfacao.edit cannot update satisfacao', function () {
    $user = User::factory()->create();
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($user)
        ->put(route('satisfacao.update', $satisfacaoId), [
            'descricao' => 'Updated Satisfacao',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertStatus(403);
});

test('user with satisfacao.edit can update satisfacao', function () {
    $user = createUserWithPermission('satisfacao.edit');
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($user)
        ->put(route('satisfacao.update', $satisfacaoId), [
            'descricao' => 'Updated Satisfacao',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('satisfacao', [
        'cod' => $satisfacaoId,
        'descricao' => 'Updated Satisfacao',
    ]);
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('satisfacao.view');
    
    $this->actingAs($user)
        ->get(route('satisfacao.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('satisfacao.create'))
        ->assertStatus(403);
    
    // Criar uma satisfacao para testar show
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    $this->actingAs($user)
        ->get(route('satisfacao.show', (int)$satisfacaoId))
        ->assertOk();
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('satisfacao.create');
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($user)
        ->get(route('satisfacao.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('satisfacao.edit', $satisfacaoId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('satisfacao.update', $satisfacaoId), [
            'descricao' => 'Updated',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('satisfacao.edit');
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($user)
        ->get(route('satisfacao.edit', $satisfacaoId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('satisfacao.destroy', $satisfacaoId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('satisfacao', [
        'cod' => $satisfacaoId,
    ]);
});

test('admin can access all satisfacao routes', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->get(route('satisfacao.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('satisfacao.create'))
        ->assertOk();
    
    // Criar uma satisfacao para testar show e edit
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($admin)
        ->get(route('satisfacao.show', (int)$satisfacaoId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('satisfacao.edit', (int)$satisfacaoId))
        ->assertOk();
});

test('admin can create satisfacao without satisfacao.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('satisfacao.store'), [
            'descricao' => 'Admin Created Satisfacao',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('satisfacao', [
        'descricao' => 'Admin Created Satisfacao',
    ]);
});

test('admin can update satisfacao without satisfacao.edit permission', function () {
    $admin = createAdminUser();
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($admin)
        ->put(route('satisfacao.update', $satisfacaoId), [
            'descricao' => 'Admin Updated Satisfacao',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('satisfacao', [
        'cod' => $satisfacaoId,
        'descricao' => 'Admin Updated Satisfacao',
    ]);
});

test('admin can delete satisfacao without satisfacao.delete permission', function () {
    $admin = createAdminUser();
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    
    $this->actingAs($admin)
        ->delete(route('satisfacao.destroy', $satisfacaoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('satisfacao', [
        'cod' => $satisfacaoId,
    ]);
});

test('admin has all satisfacao permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'satisfacao.view',
        'satisfacao.create',
        'satisfacao.edit',
        'satisfacao.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

// Testes para permissões via roles
test('user with permission via role can access satisfacao index', function () {
    $user = User::factory()->create();
    $roleId = createRole('satisfacao-viewer');
    assignRoleToUser($user, 'satisfacao-viewer');
    assignPermissionToRole($roleId, 'satisfacao.view');
    
    // Recarregar usuário para garantir que as permissões via role são carregadas
    $user = User::find($user->id);
    
    expect($user->hasPermission('satisfacao.view'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('satisfacao.index'))
        ->assertOk();
});

test('user with permission via role can access satisfacao create', function () {
    $user = User::factory()->create();
    $roleId = createRole('satisfacao-creator');
    assignRoleToUser($user, 'satisfacao-creator');
    assignPermissionToRole($roleId, 'satisfacao.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('satisfacao.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('satisfacao.create'))
        ->assertOk();
});

test('user with permission via role can store new satisfacao', function () {
    $user = User::factory()->create();
    $roleId = createRole('satisfacao-creator');
    assignRoleToUser($user, 'satisfacao-creator');
    assignPermissionToRole($roleId, 'satisfacao.create');
    
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->post(route('satisfacao.store'), [
            'descricao' => 'Satisfacao via Role',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('satisfacao', [
        'descricao' => 'Satisfacao via Role',
    ]);
});

test('user with permission via role can edit satisfacao', function () {
    $user = User::factory()->create();
    $roleId = createRole('satisfacao-editor');
    assignRoleToUser($user, 'satisfacao-editor');
    assignPermissionToRole($roleId, 'satisfacao.edit');
    
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->get(route('satisfacao.edit', $satisfacaoId))
        ->assertOk();
    
    $this->actingAs($user)
        ->put(route('satisfacao.update', $satisfacaoId), [
            'descricao' => 'Updated via Role',
            'cod_tipo_pergunta' => 1,
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('satisfacao', [
        'cod' => $satisfacaoId,
        'descricao' => 'Updated via Role',
    ]);
});

test('user with permission via role can delete satisfacao', function () {
    $user = User::factory()->create();
    $roleId = createRole('satisfacao-deleter');
    assignRoleToUser($user, 'satisfacao-deleter');
    assignPermissionToRole($roleId, 'satisfacao.delete');
    
    $satisfacaoId = DB::table('satisfacao')->insertGetId([
        'descricao' => 'Test Satisfacao',
        'cod_tipo_pergunta' => 1,
    ]);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->delete(route('satisfacao.destroy', $satisfacaoId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('satisfacao', [
        'cod' => $satisfacaoId,
    ]);
});

test('user with multiple roles gets union of permissions', function () {
    $user = User::factory()->create();
    $role1Id = createRole('satisfacao-viewer');
    $role2Id = createRole('satisfacao-creator');
    
    assignRoleToUser($user, 'satisfacao-viewer');
    assignRoleToUser($user, 'satisfacao-creator');
    assignPermissionToRole($role1Id, 'satisfacao.view');
    assignPermissionToRole($role2Id, 'satisfacao.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('satisfacao.view'))->toBeTrue();
    expect($user->hasPermission('satisfacao.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('satisfacao.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('satisfacao.create'))
        ->assertOk();
});

test('satisfacao.show returns 404 for non-existent satisfacao', function () {
    $user = createUserWithPermission('satisfacao.view');
    
    $this->actingAs($user)
        ->get(route('satisfacao.show', 99999))
        ->assertStatus(404);
});

