<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckSessionSecurity;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SingleSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Middleware de segurança HTTP/HTTPS (global, executa primeiro)
        $middleware->web(prepend: [
            ForceHttps::class,
            SecurityHeaders::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            CheckSessionSecurity::class, // Verificação de segurança de sessão
            // SingleSession::class, // Sessão única por usuário - DESABILITADO para permitir múltiplos navegadores
            \App\Http\Middleware\RequireApproval::class, // Verificação de aprovação
        ]);

        // Registrar middleware de permissões com alias
        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
