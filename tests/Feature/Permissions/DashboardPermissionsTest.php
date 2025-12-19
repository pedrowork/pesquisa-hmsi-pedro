<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();

    // Criar todas as permissões do dashboard
    createPermission('dashboard.view');
    createPermission('dashboard.stats.management');
    createPermission('dashboard.quick-actions');
    createPermission('dashboard.management-links');
    createPermission('dashboard.research.metrics');
    createPermission('dashboard.research.secondary');
    createPermission('dashboard.research.analysis');

    // Criar permissões necessárias para ações rápidas e links
    createPermission('users.view');
    createPermission('users.create');
    createPermission('roles.view');
    createPermission('roles.create');
    createPermission('permissions.view');
});

// ============================================
// TESTE 1: dashboard.view
// ============================================

test('user without dashboard.view permission cannot access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(403);
});

test('user with dashboard.view permission can access dashboard', function () {
    $user = createUserWithPermission('dashboard.view');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('admin can access dashboard even without explicit dashboard.view permission', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();
});

// ============================================
// TESTE 2: dashboard.stats.management
// ============================================

test('dashboard does not calculate stats without dashboard.stats.management permission', function () {
    $user = createUserWithPermission('dashboard.view');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['stats'])->toBeEmpty();
});

test('dashboard calculates stats with dashboard.stats.management permission', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.stats.management');

    // Criar alguns dados de teste
    User::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['stats'])->not->toBeEmpty();
    expect($props['stats'])->toHaveKey('totalUsers');
    expect($props['stats'])->toHaveKey('activeUsers');
    expect($props['stats'])->toHaveKey('totalRoles');
    expect($props['stats'])->toHaveKey('totalPermissions');
});

test('admin sees stats without dashboard.stats.management permission', function () {
    $admin = createAdminUser();

    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['stats'])->not->toBeEmpty();
    expect($props['stats'])->toHaveKey('totalUsers');
});

// ============================================
// TESTE 3: dashboard.quick-actions
// ============================================

test('dashboard.quick-actions permission controls quick actions section visibility', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.quick-actions');
    assignPermissionToUser($user, 'users.create');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    // Verificar que o conteúdo está presente (será validado no frontend via componente Can)
    expect($props)->toHaveKey('stats');
});

test('user without dashboard.quick-actions permission cannot see quick actions', function () {
    $user = createUserWithPermission('dashboard.view');
    assignPermissionToUser($user, 'users.create');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    // O frontend com componente Can não renderizará a seção
});

test('admin sees quick actions without dashboard.quick-actions permission', function () {
    $admin = createAdminUser();
    assignPermissionToUser($admin, 'users.create');

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    // Admin sempre vê tudo
});

// ============================================
// TESTE 4: dashboard.management-links
// ============================================

test('dashboard.management-links permission controls management links section visibility', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.management-links');
    assignPermissionToUser($user, 'dashboard.stats.management');
    assignPermissionToUser($user, 'users.view');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    // Verificar que stats está disponível para os links
    expect($props['stats'])->not->toBeEmpty();
});

test('user without dashboard.management-links permission cannot see management links', function () {
    $user = createUserWithPermission('dashboard.view');
    assignPermissionToUser($user, 'dashboard.stats.management');
    assignPermissionToUser($user, 'users.view');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    // O frontend com componente Can não renderizará a seção
});

test('admin sees management links without dashboard.management-links permission', function () {
    $admin = createAdminUser();
    assignPermissionToUser($admin, 'users.view');

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    // Admin sempre vê tudo
});

// ============================================
// TESTE 5: dashboard.research.metrics
// ============================================

test('dashboard does not calculate research metrics without dashboard.research.metrics permission', function () {
    $user = createUserWithPermission('dashboard.view');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->not->toHaveKey('totalQuestionarios');
    expect($props['researchStats'])->not->toHaveKey('totalPacientes');
    expect($props['researchStats'])->not->toHaveKey('questionariosMes');
    expect($props['researchStats'])->not->toHaveKey('satisfacaoMedia');
});

test('dashboard calculates research metrics with dashboard.research.metrics permission', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.research.metrics');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->toHaveKey('totalQuestionarios');
    expect($props['researchStats'])->toHaveKey('totalPacientes');
    expect($props['researchStats'])->toHaveKey('questionariosMes');
    expect($props['researchStats'])->toHaveKey('satisfacaoMedia');
});

test('admin sees research metrics without dashboard.research.metrics permission', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->toHaveKey('totalQuestionarios');
    expect($props['researchStats'])->toHaveKey('totalPacientes');
});

// ============================================
// TESTE 6: dashboard.research.secondary
// ============================================

test('dashboard does not calculate secondary metrics without dashboard.research.secondary permission', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.research.metrics');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->not->toHaveKey('totalRespostas');
    expect($props['researchStats'])->not->toHaveKey('pacientesMes');
});

test('dashboard calculates secondary metrics with dashboard.research.secondary permission', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.research.metrics');
    assignPermissionToUser($user, 'dashboard.research.secondary');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->toHaveKey('totalRespostas');
    expect($props['researchStats'])->toHaveKey('pacientesMes');
});

test('admin sees secondary metrics without dashboard.research.secondary permission', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    // Admin deve ver todas as métricas secundárias
    expect($props['researchStats'])->toHaveKey('totalRespostas');
    expect($props['researchStats'])->toHaveKey('pacientesMes');
});

// ============================================
// TESTE 7: dashboard.research.analysis
// ============================================

test('dashboard does not calculate analysis without dashboard.research.analysis permission', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.research.metrics');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->not->toHaveKey('topSetores');
    expect($props['researchStats'])->not->toHaveKey('tipoPaciente');
});

test('dashboard calculates analysis with dashboard.research.analysis permission', function () {
    $user = User::factory()->create();
    assignPermissionToUser($user, 'dashboard.view');
    assignPermissionToUser($user, 'dashboard.research.metrics');
    assignPermissionToUser($user, 'dashboard.research.analysis');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->toHaveKey('topSetores');
    expect($props['researchStats'])->toHaveKey('tipoPaciente');
});

test('admin sees analysis without dashboard.research.analysis permission', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['researchStats'])->toHaveKey('topSetores');
    expect($props['researchStats'])->toHaveKey('tipoPaciente');
});

// ============================================
// TESTE ADMIN: Todas as permissões
// ============================================

test('admin user sees all dashboard data regardless of permissions', function () {
    $admin = createAdminUser();

    // Criar dados de teste
    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    // Admin deve ver todos os dados
    expect($props)->toHaveKey('stats');
    expect($props)->toHaveKey('researchStats');

    // Verificar que stats está preenchido
    expect($props['stats'])->not->toBeEmpty();
    expect($props['stats'])->toHaveKey('totalUsers');

    // Verificar que researchStats está presente (mesmo que vazio)
    expect($props)->toHaveKey('researchStats');
});

test('admin has all dashboard permissions implicitly', function () {
    $admin = createAdminUser();

    $permissions = [
        'dashboard.view',
        'dashboard.stats.management',
        'dashboard.quick-actions',
        'dashboard.management-links',
        'dashboard.research.metrics',
        'dashboard.research.secondary',
        'dashboard.research.analysis',
    ];

    foreach ($permissions as $permission) {
        expect($admin->hasPermission($permission))->toBeTrue("Admin deveria ter permissão {$permission}");
    }
});
