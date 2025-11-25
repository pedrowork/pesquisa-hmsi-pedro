<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $stats = [
            'totalUsers' => \App\Models\User::count(),
            'activeUsers' => \App\Models\User::where('status', 1)->count(),
            'totalRoles' => \Illuminate\Support\Facades\DB::table('roles')->count(),
            'totalPermissions' => \Illuminate\Support\Facades\DB::table('permissions')->count(),
        ];

        return Inertia::render('dashboard', [
            'stats' => $stats,
        ]);
    })->name('dashboard');

    // Gerenciamento de Usuários
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // Gerenciamento de Roles
    Route::resource('roles', \App\Http\Controllers\RoleController::class);

    // Gerenciamento de Permissões
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);

    // Pesquisa de Satisfação
    Route::resource('leitos', \App\Http\Controllers\LeitoController::class);
    Route::resource('setores', \App\Http\Controllers\SetorController::class);
    Route::resource('tipos-convenio', \App\Http\Controllers\TipoConvenioController::class);
    Route::resource('setores-pesquisa', \App\Http\Controllers\SetorPesquisaController::class);
    Route::resource('perguntas', \App\Http\Controllers\PerguntaController::class);
    Route::resource('satisfacao', \App\Http\Controllers\SatisfacaoController::class);
    Route::resource('questionarios', \App\Http\Controllers\QuestionarioController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('metricas', [\App\Http\Controllers\MetricaController::class, 'index'])->name('metricas.index');
});

require __DIR__.'/settings.php';
