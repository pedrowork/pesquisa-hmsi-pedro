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
    // Dashboard
    Route::get('dashboard', function () {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isAdmin = $user->isAdmin();
        $stats = [];
        $researchStats = [];

        // Calcular estatísticas de gerenciamento apenas se tiver permissão
        if ($isAdmin || $user->hasPermission('dashboard.stats.management')) {
            $stats = [
                'totalUsers' => \App\Models\User::count(),
                'activeUsers' => \App\Models\User::where('status', 1)->count(),
                'totalRoles' => \Illuminate\Support\Facades\DB::table('roles')->count(),
                'totalPermissions' => \Illuminate\Support\Facades\DB::table('permissions')->count(),
            ];
        }

        // Calcular métricas principais de pesquisa apenas se tiver permissão
        if ($isAdmin || $user->hasPermission('dashboard.research.metrics')) {
            $researchStats['totalQuestionarios'] = \Illuminate\Support\Facades\DB::table('questionario')
                ->select('cod_paciente')
                ->distinct()
                ->count('cod_paciente');
            $researchStats['totalPacientes'] = \Illuminate\Support\Facades\DB::table('dados_do_paciente')->count();
            $researchStats['questionariosMes'] = \Illuminate\Support\Facades\DB::table('questionario')
                ->whereMonth('data_questionario', now()->month)
                ->whereYear('data_questionario', now()->year)
                ->select('cod_paciente')
                ->distinct()
                ->count('cod_paciente');

            // Calcular taxa de satisfação média
            $satisfacaoMedia = \Illuminate\Support\Facades\DB::table('questionario')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->selectRaw('AVG(satisfacao.cod) as media')
                ->value('media');

            $researchStats['satisfacaoMedia'] = $satisfacaoMedia ? round($satisfacaoMedia, 2) : 0;
        }

        // Calcular métricas secundárias apenas se tiver permissão
        if ($isAdmin || $user->hasPermission('dashboard.research.secondary')) {
            $researchStats['totalRespostas'] = \Illuminate\Support\Facades\DB::table('questionario')->count();
            $researchStats['pacientesMes'] = \Illuminate\Support\Facades\DB::table('questionario')
                ->join('dados_do_paciente', 'questionario.cod_paciente', '=', 'dados_do_paciente.id')
                ->whereMonth('questionario.data_questionario', now()->month)
                ->whereYear('questionario.data_questionario', now()->year)
                ->select('questionario.cod_paciente')
                ->distinct()
                ->count('questionario.cod_paciente');
        }

        // Calcular análises apenas se tiver permissão
        if ($isAdmin || $user->hasPermission('dashboard.research.analysis')) {
            // Top setores pesquisados
            $topSetores = \Illuminate\Support\Facades\DB::table('dados_do_paciente')
                ->select('setor', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                ->whereNotNull('setor')
                ->groupBy('setor')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->toArray();

            $researchStats['topSetores'] = $topSetores;

            // Distribuição por tipo de paciente
            $tipoPaciente = \Illuminate\Support\Facades\DB::table('dados_do_paciente')
                ->select('tipo_paciente', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                ->whereNotNull('tipo_paciente')
                ->groupBy('tipo_paciente')
                ->get()
                ->toArray();

            $researchStats['tipoPaciente'] = $tipoPaciente;
        }

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'researchStats' => $researchStats,
        ]);
    })->middleware('permission:dashboard.view')->name('dashboard');

    // Gerenciamento de Usuários
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:users.create')->group(function () {
        Route::get('users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:users.edit')->group(function () {
        Route::get('users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    });
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    });
    Route::delete('users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    // Gerenciamento de Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware('permission:roles.create')->group(function () {
        Route::get('roles/create', [\App\Http\Controllers\RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [\App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:roles.edit')->group(function () {
        Route::get('roles/{role}/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
    });
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('roles/{role}', [\App\Http\Controllers\RoleController::class, 'show'])->name('roles.show');
    });
    Route::delete('roles/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');

    // Gerenciamento de Permissões
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/{permission}', [\App\Http\Controllers\PermissionController::class, 'show'])->name('permissions.show');
    });
    Route::middleware('permission:permissions.create')->group(function () {
        Route::get('permissions/create', [\App\Http\Controllers\PermissionController::class, 'create'])->name('permissions.create');
        Route::post('permissions', [\App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
    });
    Route::middleware('permission:permissions.edit')->group(function () {
        Route::get('permissions/{permission}/edit', [\App\Http\Controllers\PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{permission}', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
    });
    Route::delete('permissions/{permission}', [\App\Http\Controllers\PermissionController::class, 'destroy'])
        ->middleware('permission:permissions.delete')
        ->name('permissions.destroy');

    // Pesquisa de Satisfação
    Route::middleware('permission:questionarios.view')->group(function () {
        Route::get('questionarios', [\App\Http\Controllers\QuestionarioController::class, 'index'])->name('questionarios.index');
    });
    Route::middleware('permission:questionarios.create')->group(function () {
        Route::get('questionarios/create', [\App\Http\Controllers\QuestionarioController::class, 'create'])->name('questionarios.create');
        Route::post('questionarios', [\App\Http\Controllers\QuestionarioController::class, 'store'])->name('questionarios.store');
    });
    Route::get('questionarios/{questionario}', [\App\Http\Controllers\QuestionarioController::class, 'show'])
        ->middleware('permission:questionarios.show')
        ->name('questionarios.show');

    // Módulos de Pesquisa (gerenciamento completo)
    Route::middleware('permission:leitos.manage')->group(function () {
        Route::resource('leitos', \App\Http\Controllers\LeitoController::class);
    });

    Route::middleware('permission:setores.manage')->group(function () {
        Route::resource('setores', \App\Http\Controllers\SetorController::class);
    });

    Route::middleware('permission:tipos-convenio.manage')->group(function () {
        Route::resource('tipos-convenio', \App\Http\Controllers\TipoConvenioController::class);
    });

    Route::middleware('permission:setores-pesquisa.manage')->group(function () {
        Route::resource('setores-pesquisa', \App\Http\Controllers\SetorPesquisaController::class);
    });

    Route::middleware('permission:perguntas.manage')->group(function () {
        Route::resource('perguntas', \App\Http\Controllers\PerguntaController::class);
    });

    Route::middleware('permission:satisfacao.manage')->group(function () {
        Route::resource('satisfacao', \App\Http\Controllers\SatisfacaoController::class);
    });

    Route::get('metricas', [\App\Http\Controllers\MetricaController::class, 'index'])
        ->middleware('permission:metricas.view')
        ->name('metricas.index');
});

require __DIR__.'/settings.php';
