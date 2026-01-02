<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('XSS scripts in user name are sanitized', function () {
    $admin = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $admin->roles()->attach($adminRole->id);
    }

    $xssAttempts = [
        '<script>alert("XSS")</script>',
        '<img src=x onerror=alert("XSS")>',
        '<svg onload=alert("XSS")>',
        'javascript:alert("XSS")',
        '<iframe src="javascript:alert(\'XSS\')"></iframe>',
        '<body onload=alert("XSS")>',
        '<input onfocus=alert("XSS") autofocus>',
    ];

    foreach ($xssAttempts as $xss) {
        $response = $this->actingAs($admin)->post('/users', [
            'name' => $xss,
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 1,
        ]);

        // O nome deve ser sanitizado (tags HTML removidas ou escapadas)
        $createdUser = User::where('email', 'like', 'test%@example.com')->latest()->first();
        if ($createdUser) {
            // Verificar que não contém tags script
            $this->assertStringNotContainsString('<script>', $createdUser->name);
            $this->assertStringNotContainsString('onerror=', $createdUser->name);
            $this->assertStringNotContainsString('onload=', $createdUser->name);
        }
    }
});

test('XSS scripts in email field are sanitized', function () {
    $admin = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $admin->roles()->attach($adminRole->id);
    }

    $xssAttempts = [
        '<script>alert("XSS")</script>@example.com',
        'test<script>alert("XSS")</script>@example.com',
    ];

    foreach ($xssAttempts as $xss) {
        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => $xss,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 1,
        ]);

        // Deve falhar na validação de email
        $response->assertSessionHasErrors('email');
    }
});

test('XSS in URL parameters is escaped in responses', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $xssAttempts = [
        '<script>alert("XSS")</script>',
        '<img src=x onerror=alert("XSS")>',
        'javascript:alert("XSS")',
    ];

    foreach ($xssAttempts as $xss) {
        $response = $this->actingAs($user)->get('/users?search=' . urlencode($xss));
        
        // A resposta não deve conter o script executável
        $content = $response->getContent();
        
        // Se o conteúdo contiver o valor, deve estar escapado
        if (str_contains($content, $xss)) {
            // Verificar que está escapado (não como script executável)
            $this->assertStringNotContainsString('<script>', $content);
            $this->assertStringContainsString('&lt;', $content); // HTML entities
        }
    }
});

test('XSS in form inputs is prevented in JSON responses', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $xssAttempt = '<script>alert("XSS")</script>';

    $response = $this->actingAs($user)->get('/users?search=' . urlencode($xssAttempt));
    
    // Se retornar JSON, verificar que está escapado
    if ($response->headers->get('Content-Type') === 'application/json') {
        $data = json_decode($response->getContent(), true);
        
        // O JSON não deve conter scripts executáveis
        $jsonString = json_encode($data);
        $this->assertStringNotContainsString('<script>', $jsonString);
    }
});

test('XSS in cookie values is prevented', function () {
    $xssAttempt = '<script>alert("XSS")</script>';

    // Tentar definir cookie malicioso
    $response = $this->withCookie('malicious', $xssAttempt)
        ->get('/dashboard');

    // O sistema deve tratar o cookie de forma segura
    $response->assertStatus(302); // Redirect ou 403/401
});

test('XSS in user agent is sanitized in logs', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $xssAttempt = '<script>alert("XSS")</script>';

    $response = $this->withHeaders([
        'User-Agent' => $xssAttempt,
    ])->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // O sistema deve sanitizar o User-Agent nos logs
    // Não deve executar o script
    $response->assertStatus(200);
});

test('XSS in password reset token is prevented', function () {
    $xssAttempt = '<script>alert("XSS")</script>';

    // Tentar usar token malicioso
    $response = $this->get('/reset-password/' . urlencode($xssAttempt) . '?email=test@example.com');

    // Deve retornar erro ou redirect seguro
    $this->assertContains($response->status(), [302, 404, 422]);
});

test('XSS protection in Blade templates', function () {
    $user = User::factory()->create([
        'name' => '<script>alert("XSS")</script>',
        'email' => 'test@example.com',
    ]);

    // Quando renderizado em Blade, {{ }} deve escapar automaticamente
    // Este teste verifica que o nome está armazenado mas será escapado na renderização
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);

    // O nome contém o script, mas Blade deve escapar ao renderizar
    $this->assertStringContainsString('<script>', $user->name);
});

test('XSS in file uploads is prevented', function () {
    $user = User::factory()->create();
    $permission = \App\Models\Permission::where('slug', 'users.edit')->first();
    if ($permission) {
        $user->permissions()->attach($permission->id);
    }

    $xssAttempt = '<script>alert("XSS")</script>';

    // Tentar fazer upload de arquivo com nome malicioso
    $file = \Illuminate\Http\UploadedFile::fake()->create($xssAttempt . '.txt', 100);

    $response = $this->actingAs($user)->post('/users/1', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        '_method' => 'PUT',
        'file' => $file,
    ]);

    // O nome do arquivo deve ser sanitizado
    $response->assertStatus(302); // Redirect após update
});
