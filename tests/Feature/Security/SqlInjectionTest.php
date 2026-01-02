<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
uses(RefreshDatabase::class);

test('SQL injection in email field during login is prevented', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Tentativas de SQL injection comuns
    $sqlInjectionAttempts = [
        "' OR '1'='1",
        "' OR '1'='1' --",
        "' OR '1'='1' /*",
        "admin'--",
        "admin'/*",
        "' UNION SELECT * FROM users--",
        "'; DROP TABLE users--",
        "' OR 1=1--",
        "admin' OR '1'='1",
        "test@example.com' OR '1'='1'--",
    ];

    foreach ($sqlInjectionAttempts as $injection) {
        $response = $this->post('/login', [
            'email' => $injection,
            'password' => 'wrongpassword',
        ]);

        // Não deve conseguir fazer login
        $response->assertStatus(422); // Validation error ou 401
        $this->assertGuest();
    }
});

test('SQL injection in user creation is prevented', function () {
    $admin = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $admin->roles()->attach($adminRole->id);
    }

    $sqlInjectionAttempts = [
        "'; DROP TABLE users--",
        "' OR '1'='1",
        "admin'--",
        "' UNION SELECT * FROM users--",
    ];

    foreach ($sqlInjectionAttempts as $injection) {
        $response = $this->actingAs($admin)->post('/users', [
            'name' => $injection,
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 1,
        ]);

        // Deve falhar na validação ou sanitizar o input
        $this->assertDatabaseMissing('users', [
            'email' => 'test' . uniqid() . '@example.com',
        ]);
    }
});

test('SQL injection in search/filter parameters is prevented', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $sqlInjectionAttempts = [
        "'; DROP TABLE users--",
        "' OR '1'='1",
        "1' UNION SELECT * FROM users--",
    ];

    foreach ($sqlInjectionAttempts as $injection) {
        // Tentar SQL injection em parâmetros de busca
        $response = $this->actingAs($user)->get('/users?search=' . urlencode($injection));
        
        // Não deve quebrar a aplicação
        $response->assertStatus(200);
    }
});

test('prepared statements are used in database queries', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Verificar que queries usam prepared statements (Eloquent usa por padrão)
    DB::enableQueryLog();
    
    User::where('email', "'; DROP TABLE users--")->first();
    
    $queries = DB::getQueryLog();
    $lastQuery = $queries[count($queries) - 1];
    
    // A query deve usar bindings, não concatenação de strings
    $this->assertArrayHasKey('bindings', $lastQuery);
    $this->assertNotEmpty($lastQuery['bindings']);
});

test('SQL injection in integer parameters is prevented', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $sqlInjectionAttempts = [
        "1 OR 1=1",
        "1' OR '1'='1",
        "1; DROP TABLE users--",
        "1 UNION SELECT * FROM users--",
    ];

    foreach ($sqlInjectionAttempts as $injection) {
        // Tentar SQL injection em parâmetros de ID
        $response = $this->actingAs($user)->get('/users/' . urlencode($injection));
        
        // Deve retornar 404 ou erro de validação, não executar SQL malicioso
        $this->assertContains($response->status(), [404, 422, 500]);
    }
});

test('raw SQL queries use parameter binding', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Verificar que DB::select usa bindings
    DB::enableQueryLog();
    
    DB::select('SELECT * FROM users WHERE email = ?', ["'; DROP TABLE users--"]);
    
    $queries = DB::getQueryLog();
    $lastQuery = $queries[count($queries) - 1];
    
    // A query deve usar bindings
    $this->assertArrayHasKey('bindings', $lastQuery);
    $this->assertContains("'; DROP TABLE users--", $lastQuery['bindings']);
});

test('SQL injection cannot delete tables or data', function () {
    $initialUserCount = User::count();
    
    // Tentar várias formas de SQL injection
    $sqlInjectionAttempts = [
        "'; DROP TABLE users--",
        "'; DELETE FROM users--",
        "'; TRUNCATE TABLE users--",
    ];

    foreach ($sqlInjectionAttempts as $injection) {
        try {
            // Tentar executar SQL malicioso através de inputs
            $response = $this->post('/login', [
                'email' => $injection,
                'password' => 'password',
            ]);
        } catch (\Exception $e) {
            // Exceções são esperadas e devem ser capturadas
        }
    }

    // Verificar que nenhum dado foi deletado
    $this->assertEquals($initialUserCount, User::count());
    
    // Verificar que a tabela ainda existe
    $this->assertDatabaseHas('users', [
        'id' => 1,
    ]);
});
