<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    createPermission('perguntas.view');
    createPermission('perguntas.create');
    createPermission('perguntas.edit');
    createPermission('perguntas.delete');
});

test('user without perguntas.view cannot access perguntas index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('perguntas.index'))->assertStatus(403);
});

test('user with perguntas.view can access perguntas index', function () {
    $user = createUserWithPermission('perguntas.view');
    $this->actingAs($user)->get(route('perguntas.index'))->assertOk();
});

test('user without perguntas.create cannot access perguntas create form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('perguntas.create'))->assertStatus(403);
});

test('user with perguntas.create can access perguntas create form', function () {
    $user = createUserWithPermission('perguntas.create');
    
    $this->actingAs($user)
        ->get(route('perguntas.create'))
        ->assertOk();
});

test('user without perguntas.create cannot store new pergunta', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('perguntas.store'), ['descricao' => 'Test Pergunta'])
        ->assertStatus(403);
});

test('user with perguntas.create can store new pergunta', function () {
    $user = createUserWithPermission('perguntas.create');
    $this->actingAs($user)
        ->post(route('perguntas.store'), ['descricao' => 'Test Pergunta'])
        ->assertRedirect();
    
    $this->assertDatabaseHas('perguntas_descricao', ['descricao' => 'Test Pergunta']);
});

test('user without perguntas.edit cannot access perguntas edit form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('perguntas.edit', 1))->assertStatus(403);
});

test('user with perguntas.edit can access perguntas edit form', function () {
    $user = createUserWithPermission('perguntas.edit');
    
    // Criar uma pergunta primeiro
    $perguntaId = DB::table('perguntas_descricao')->insertGetId([
        'descricao' => 'Test Pergunta',
    ]);
    
    $this->actingAs($user)->get(route('perguntas.edit', $perguntaId))->assertOk();
});

test('user without perguntas.delete cannot delete pergunta', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->delete(route('perguntas.destroy', 1))->assertStatus(403);
});

test('user with perguntas.delete can delete pergunta', function () {
    $user = createUserWithPermission('perguntas.delete');
    $perguntaId = DB::table('perguntas_descricao')->insertGetId([
        'descricao' => 'Test Pergunta',
    ]);
    
    $this->actingAs($user)
        ->delete(route('perguntas.destroy', $perguntaId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('perguntas_descricao', ['cod' => $perguntaId]);
});

test('user without perguntas.edit cannot update pergunta', function () {
    $user = User::factory()->create();
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($user)
        ->put(route('perguntas.update', $perguntaId), [
            'descricao' => 'Updated Pergunta',
        ])
        ->assertStatus(403);
});

test('user with perguntas.edit can update pergunta', function () {
    $user = createUserWithPermission('perguntas.edit');
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($user)
        ->put(route('perguntas.update', $perguntaId), [
            'descricao' => 'Updated Pergunta',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('perguntas_descricao', [
        'cod' => $perguntaId,
        'descricao' => 'Updated Pergunta',
    ]);
});

test('permissions are independent - user can have view but not create', function () {
    $user = createUserWithPermission('perguntas.view');
    
    $this->actingAs($user)
        ->get(route('perguntas.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('perguntas.create'))
        ->assertStatus(403);
    
    // Criar uma pergunta para testar show
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test']);
    $this->actingAs($user)
        ->get(route('perguntas.show', (int)$perguntaId))
        ->assertOk();
});

test('permissions are independent - user can have create but not edit', function () {
    $user = createUserWithPermission('perguntas.create');
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($user)
        ->get(route('perguntas.create'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('perguntas.edit', $perguntaId))
        ->assertStatus(403);
    
    $this->actingAs($user)
        ->put(route('perguntas.update', $perguntaId), [
            'descricao' => 'Updated',
        ])
        ->assertStatus(403);
});

test('permissions are independent - user can have edit but not delete', function () {
    $user = createUserWithPermission('perguntas.edit');
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($user)
        ->get(route('perguntas.edit', $perguntaId))
        ->assertOk();
    
    $this->actingAs($user)
        ->delete(route('perguntas.destroy', $perguntaId))
        ->assertStatus(403);
    
    $this->assertDatabaseHas('perguntas_descricao', [
        'cod' => $perguntaId,
    ]);
});

test('admin can access all perguntas routes', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->get(route('perguntas.index'))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('perguntas.create'))
        ->assertOk();
    
    // Criar uma pergunta para testar show e edit
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($admin)
        ->get(route('perguntas.show', (int)$perguntaId))
        ->assertOk();
    
    $this->actingAs($admin)
        ->get(route('perguntas.edit', (int)$perguntaId))
        ->assertOk();
});

test('admin can create perguntas without perguntas.create permission', function () {
    $admin = createAdminUser();
    
    $this->actingAs($admin)
        ->post(route('perguntas.store'), [
            'descricao' => 'Admin Created Pergunta',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('perguntas_descricao', [
        'descricao' => 'Admin Created Pergunta',
    ]);
});

test('admin can update perguntas without perguntas.edit permission', function () {
    $admin = createAdminUser();
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($admin)
        ->put(route('perguntas.update', $perguntaId), [
            'descricao' => 'Admin Updated Pergunta',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('perguntas_descricao', [
        'cod' => $perguntaId,
        'descricao' => 'Admin Updated Pergunta',
    ]);
});

test('admin can delete perguntas without perguntas.delete permission', function () {
    $admin = createAdminUser();
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    
    $this->actingAs($admin)
        ->delete(route('perguntas.destroy', $perguntaId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('perguntas_descricao', [
        'cod' => $perguntaId,
    ]);
});

test('admin has all perguntas permissions implicitly', function () {
    $admin = createAdminUser();
    
    $permissions = [
        'perguntas.view',
        'perguntas.create',
        'perguntas.edit',
        'perguntas.delete',
    ];
    
    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});

// Testes para permissões via roles
test('user with permission via role can access perguntas index', function () {
    $user = User::factory()->create();
    $roleId = createRole('perguntas-viewer');
    assignRoleToUser($user, 'perguntas-viewer');
    assignPermissionToRole($roleId, 'perguntas.view');
    
    // Recarregar usuário para garantir que as permissões via role são carregadas
    $user = User::find($user->id);
    
    expect($user->hasPermission('perguntas.view'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('perguntas.index'))
        ->assertOk();
});

test('user with permission via role can access perguntas create', function () {
    $user = User::factory()->create();
    $roleId = createRole('perguntas-creator');
    assignRoleToUser($user, 'perguntas-creator');
    assignPermissionToRole($roleId, 'perguntas.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('perguntas.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('perguntas.create'))
        ->assertOk();
});

test('user with permission via role can store new pergunta', function () {
    $user = User::factory()->create();
    $roleId = createRole('perguntas-creator');
    assignRoleToUser($user, 'perguntas-creator');
    assignPermissionToRole($roleId, 'perguntas.create');
    
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->post(route('perguntas.store'), [
            'descricao' => 'Pergunta via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('perguntas_descricao', [
        'descricao' => 'Pergunta via Role',
    ]);
});

test('user with permission via role can edit pergunta', function () {
    $user = User::factory()->create();
    $roleId = createRole('perguntas-editor');
    assignRoleToUser($user, 'perguntas-editor');
    assignPermissionToRole($roleId, 'perguntas.edit');
    
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->get(route('perguntas.edit', $perguntaId))
        ->assertOk();
    
    $this->actingAs($user)
        ->put(route('perguntas.update', $perguntaId), [
            'descricao' => 'Updated via Role',
        ])
        ->assertRedirect();
    
    $this->assertDatabaseHas('perguntas_descricao', [
        'cod' => $perguntaId,
        'descricao' => 'Updated via Role',
    ]);
});

test('user with permission via role can delete pergunta', function () {
    $user = User::factory()->create();
    $roleId = createRole('perguntas-deleter');
    assignRoleToUser($user, 'perguntas-deleter');
    assignPermissionToRole($roleId, 'perguntas.delete');
    
    $perguntaId = DB::table('perguntas_descricao')->insertGetId(['descricao' => 'Test Pergunta']);
    $user = User::find($user->id);
    
    $this->actingAs($user)
        ->delete(route('perguntas.destroy', $perguntaId))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('perguntas_descricao', [
        'cod' => $perguntaId,
    ]);
});

test('user with multiple roles gets union of permissions', function () {
    $user = User::factory()->create();
    $role1Id = createRole('perguntas-viewer');
    $role2Id = createRole('perguntas-creator');
    
    assignRoleToUser($user, 'perguntas-viewer');
    assignRoleToUser($user, 'perguntas-creator');
    assignPermissionToRole($role1Id, 'perguntas.view');
    assignPermissionToRole($role2Id, 'perguntas.create');
    
    $user = User::find($user->id);
    
    expect($user->hasPermission('perguntas.view'))->toBeTrue();
    expect($user->hasPermission('perguntas.create'))->toBeTrue();
    
    $this->actingAs($user)
        ->get(route('perguntas.index'))
        ->assertOk();
    
    $this->actingAs($user)
        ->get(route('perguntas.create'))
        ->assertOk();
});

test('perguntas.show returns 404 for non-existent pergunta', function () {
    $user = createUserWithPermission('perguntas.view');
    
    $this->actingAs($user)
        ->get(route('perguntas.show', 99999))
        ->assertStatus(404);
});

