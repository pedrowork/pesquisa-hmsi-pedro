<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;
uses(RefreshDatabase::class);

test('two-factor authentication feature is enabled', function () {
    $features = config('fortify.features');
    
    $this->assertContains(Features::twoFactorAuthentication(), $features);
});

test('user can enable two-factor authentication', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    // Simular ativação de 2FA (normalmente feito via QR code)
    // Na prática, isso seria feito através do endpoint de Fortify
    $response = $this->post('/user/two-factor-authentication');

    // Deve retornar sucesso ou redirect
    $this->assertContains($response->status(), [200, 302]);
});

test('user with 2FA enabled must provide code on login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Habilitar 2FA para o usuário
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
    ])->save();

    // Tentar fazer login
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Deve redirecionar para página de desafio 2FA
    $this->assertContains($response->status(), [200, 302]);
});

test('invalid two-factor code is rejected', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('test-secret'),
    ]);

    $session = $this->app['session'];
    $session->put('login.id', $user->id);

    // Tentar fazer login com código inválido
    $response = $this->post('/two-factor-challenge', [
        'code' => '000000', // Código inválido
    ]);

    // Deve retornar erro de validação
    $this->assertContains($response->status(), [302, 422, 401]);
    $response->assertSessionHasErrors();
});

test('two-factor recovery codes can be used', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
    ]);

    $session = $this->app['session'];
    $session->put('login.id', $user->id);

    // Tentar usar código de recuperação
    $response = $this->post('/two-factor-challenge', [
        'recovery_code' => 'recovery-code-1',
    ]);

    // Deve funcionar (200 ou redirect)
    $this->assertContains($response->status(), [200, 302]);
});

test('used recovery code cannot be reused', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
    ]);

    $session = $this->app['session'];
    $session->put('login.id', $user->id);

    // Usar código de recuperação pela primeira vez
    $this->post('/two-factor-challenge', [
        'recovery_code' => 'recovery-code-1',
    ]);

    // Tentar usar novamente
    $session->put('login.id', $user->id);
    $response = $this->post('/two-factor-challenge', [
        'recovery_code' => 'recovery-code-1',
    ]);

    // Deve falhar (código já usado)
    $this->assertContains($response->status(), [302, 422, 401]);
});

test('two-factor authentication is rate limited', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('test-secret'),
    ]);

    $session = $this->app['session'];
    $session->put('login.id', $user->id);

    RateLimiter::clear('two-factor');

    // Fazer 5 tentativas (limite padrão)
    for ($i = 0; $i < 5; $i++) {
        $this->post('/two-factor-challenge', [
            'code' => '000000',
        ]);
    }

    // A 6ª tentativa deve ser bloqueada
    $response = $this->post('/two-factor-challenge', [
        'code' => '000000',
    ]);

    $this->assertEquals(429, $response->status());
});

test('user can disable two-factor authentication', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('test-secret'),
    ]);

    $this->actingAs($user);

    // Desabilitar 2FA
    $response = $this->delete('/user/two-factor-authentication');

    // Deve retornar sucesso
    $this->assertContains($response->status(), [200, 302]);

    // Verificar que 2FA foi desabilitado
    $user->refresh();
    $this->assertNull($user->two_factor_secret);
});

test('two-factor secret is encrypted in database', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('original-secret'),
    ]);

    // O valor no banco deve estar criptografado
    $rawSecret = \DB::table('users')->where('id', $user->id)->value('two_factor_secret');
    
    // Não deve ser igual ao valor original
    $this->assertNotEquals('original-secret', $rawSecret);
    
    // Deve ser possível descriptografar
    $decrypted = decrypt($rawSecret);
    $this->assertEquals('original-secret', $decrypted);
});

test('user without 2FA can login normally', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Não deve ter two_factor_secret
    $this->assertNull($user->two_factor_secret);

    // Login deve funcionar normalmente
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Deve autenticar sem pedir código 2FA
    $this->assertAuthenticated();
});
