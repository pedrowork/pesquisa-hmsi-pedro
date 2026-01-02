<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
uses(RefreshDatabase::class);

test('password must be at least 8 characters', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Short1!', // 7 caracteres
        'password_confirmation' => 'Short1!',
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
    $response->assertSessionHasErrors(['password' => ['The password field must be at least 8 characters.']]);
});

test('password must contain uppercase letters', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123!', // Sem maiúscula
        'password_confirmation' => 'password123!',
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('password must contain lowercase letters', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'PASSWORD123!', // Sem minúscula
        'password_confirmation' => 'PASSWORD123!',
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('password must contain numbers', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password!', // Sem número
        'password_confirmation' => 'Password!',
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('password must contain symbols', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123', // Sem símbolo
        'password_confirmation' => 'Password123',
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('valid password is accepted', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'status' => 1,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('password is hashed before storing', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $password = 'Password123!';

    $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $password,
        'password_confirmation' => $password,
        'status' => 1,
    ]);

    $createdUser = User::where('email', 'test@example.com')->first();

    // A senha não deve ser igual ao texto plano
    $this->assertNotEquals($password, $createdUser->password);

    // Deve ser possível verificar com Hash::check
    $this->assertTrue(Hash::check($password, $createdUser->password));
});

test('password confirmation must match', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Different123!', // Diferente
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('password cannot be empty', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $response = $this->actingAs($user)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => '',
        'status' => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('common passwords are rejected', function () {
    $user = User::factory()->create();
    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    $commonPasswords = [
        'password',
        '12345678',
        'qwerty123',
        'admin123',
    ];

    foreach ($commonPasswords as $commonPassword) {
        $response = $this->actingAs($user)->post('/users', [
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => $commonPassword,
            'password_confirmation' => $commonPassword,
            'status' => 1,
        ]);

        // Deve falhar na validação (uncompromised rule)
        $response->assertSessionHasErrors('password');
    }
});

test('password reset requires valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('OldPassword123!'),
    ]);

    // Tentar resetar senha com token inválido
    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    // Deve falhar
    $response->assertSessionHasErrors();
});

test('password change requires current password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('CurrentPassword123!'),
    ]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'WrongPassword123!', // Senha atual incorreta
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    // Deve falhar
    $response->assertSessionHasErrors('current_password');
});

test('password change with correct current password succeeds', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('CurrentPassword123!'),
    ]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    // Deve funcionar
    $response->assertSessionHasNoErrors();

    // Verificar que a senha foi alterada
    $user->refresh();
    $this->assertTrue(Hash::check('NewPassword123!', $user->password));
});
