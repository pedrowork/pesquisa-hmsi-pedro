<?php

use App\Http\Middleware\CheckPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    DB::table('user_permissions')->delete();
    DB::table('role_permissions')->delete();
    DB::table('user_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
});

test('middleware redirects unauthenticated user to login', function () {
    $middleware = new CheckPermission();
    $request = Request::create('/test', 'GET');
    
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'dashboard.view');
    
    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toContain('login');
});

test('middleware allows access for user with permission', function () {
    $user = User::factory()->create();
    
    // Criar permissão
    $permissionId = DB::table('permissions')->insertGetId([
        'name' => 'Visualizar Dashboard',
        'slug' => 'dashboard.view',
        'description' => 'Permite visualizar o dashboard',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Associar permissão diretamente ao usuário
    DB::table('user_permissions')->insert([
        'user_id' => $user->id,
        'permission_id' => $permissionId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $middleware = new CheckPermission();
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'dashboard.view');
    
    expect($response->getContent())->toBe('OK');
    expect($response->getStatusCode())->toBe(200);
});

test('middleware denies access for user without permission', function () {
    $user = User::factory()->create();
    
    $middleware = new CheckPermission();
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    try {
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'dashboard.view');
        
        // Se não lançou exceção, verificar status code
        expect($response->getStatusCode())->toBe(403);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
    }
});

test('middleware allows access for admin user', function () {
    $user = User::factory()->create();
    
    // Criar role admin
    $adminRoleId = DB::table('roles')->insertGetId([
        'name' => 'Admin',
        'slug' => 'admin',
        'description' => 'Administrador',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('user_roles')->insert([
        'user_id' => $user->id,
        'role_id' => $adminRoleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $middleware = new CheckPermission();
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'dashboard.view');
    
    expect($response->getContent())->toBe('OK');
    expect($response->getStatusCode())->toBe(200);
});

test('middleware returns JSON response for Inertia requests when permission denied', function () {
    $user = User::factory()->create();
    
    $middleware = new CheckPermission();
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Inertia', 'true');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'dashboard.view');
    
    expect($response->getStatusCode())->toBe(403);
    expect($response->headers->get('Content-Type'))->toContain('application/json');
    
    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('message');
    expect($content)->toHaveKey('permission');
    expect($content['permission'])->toBe('dashboard.view');
});

test('middleware returns abort 403 for non-Inertia requests when permission denied', function () {
    $user = User::factory()->create();
    
    $middleware = new CheckPermission();
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    try {
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'dashboard.view');
        
        // Se não lançou exceção, verificar status code
        expect($response->getStatusCode())->toBe(403);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
        expect($e->getMessage())->toContain('permissão');
    }
});

