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

            // Calcular taxa de satisfação média (apenas perguntas tipo 3 -> escala 0 a 10)
            // Mapeia os códigos 10..20 para valores 0..10 e ignora NA (cod=7)
            $satisfacaoMedia = \Illuminate\Support\Facades\DB::table('questionario')
                ->join('satisfacao', 'questionario.resposta', '=', 'satisfacao.cod')
                ->join('perguntas_descricao', 'questionario.cod_pergunta', '=', 'perguntas_descricao.cod')
                ->where('perguntas_descricao.cod_tipo_pergunta', 3)
                ->whereBetween('satisfacao.cod', [10, 20]) // somente valores 0..10
                ->selectRaw('AVG(satisfacao.cod - 10) as media') // normaliza para 0..10
                ->value('media');

            $researchStats['satisfacaoMedia'] = $satisfacaoMedia !== null ? round($satisfacaoMedia, 2) : 0;
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
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
    });
    Route::middleware('permission:permissions.create')->group(function () {
        Route::get('permissions/create', [\App\Http\Controllers\PermissionController::class, 'create'])->name('permissions.create');
        Route::post('permissions', [\App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
    });
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('permissions/{permission}', [\App\Http\Controllers\PermissionController::class, 'show'])->name('permissions.show');
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

    // Módulos de Pesquisa (permissões granulares)
    // Leitos
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:leitos.view')->group(function () {
        Route::get('leitos', [\App\Http\Controllers\LeitoController::class, 'index'])->name('leitos.index');
    });
    Route::middleware('permission:leitos.create')->group(function () {
        Route::get('leitos/create', [\App\Http\Controllers\LeitoController::class, 'create'])->name('leitos.create');
        Route::post('leitos', [\App\Http\Controllers\LeitoController::class, 'store'])->name('leitos.store');
    });
    Route::middleware('permission:leitos.view')->group(function () {
        Route::get('leitos/{leito}', [\App\Http\Controllers\LeitoController::class, 'show'])->name('leitos.show');
    });
    Route::middleware('permission:leitos.edit')->group(function () {
        Route::get('leitos/{leito}/edit', [\App\Http\Controllers\LeitoController::class, 'edit'])->name('leitos.edit');
        Route::put('leitos/{leito}', [\App\Http\Controllers\LeitoController::class, 'update'])->name('leitos.update');
    });
    Route::delete('leitos/{leito}', [\App\Http\Controllers\LeitoController::class, 'destroy'])
        ->middleware('permission:leitos.delete')
        ->name('leitos.destroy');

    // Setores
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:setores.view')->group(function () {
        Route::get('setores', [\App\Http\Controllers\SetorController::class, 'index'])->name('setores.index');
    });
    Route::middleware('permission:setores.create')->group(function () {
        Route::get('setores/create', [\App\Http\Controllers\SetorController::class, 'create'])->name('setores.create');
        Route::post('setores', [\App\Http\Controllers\SetorController::class, 'store'])->name('setores.store');
    });
    Route::middleware('permission:setores.view')->group(function () {
        Route::get('setores/{setor}', [\App\Http\Controllers\SetorController::class, 'show'])->name('setores.show');
    });
    Route::middleware('permission:setores.edit')->group(function () {
        Route::get('setores/{setor}/edit', [\App\Http\Controllers\SetorController::class, 'edit'])->name('setores.edit');
        Route::put('setores/{setor}', [\App\Http\Controllers\SetorController::class, 'update'])->name('setores.update');
    });
    Route::delete('setores/{setor}', [\App\Http\Controllers\SetorController::class, 'destroy'])
        ->middleware('permission:setores.delete')
        ->name('setores.destroy');

    // Tipos de Convênio
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:tipos-convenio.view')->group(function () {
        Route::get('tipos-convenio', [\App\Http\Controllers\TipoConvenioController::class, 'index'])->name('tipos-convenio.index');
    });
    Route::middleware('permission:tipos-convenio.create')->group(function () {
        Route::get('tipos-convenio/create', [\App\Http\Controllers\TipoConvenioController::class, 'create'])->name('tipos-convenio.create');
        Route::post('tipos-convenio', [\App\Http\Controllers\TipoConvenioController::class, 'store'])->name('tipos-convenio.store');
    });
    Route::middleware('permission:tipos-convenio.view')->group(function () {
        Route::get('tipos-convenio/{tipoConvenio}', [\App\Http\Controllers\TipoConvenioController::class, 'show'])->name('tipos-convenio.show');
    });
    Route::middleware('permission:tipos-convenio.edit')->group(function () {
        Route::get('tipos-convenio/{tipoConvenio}/edit', [\App\Http\Controllers\TipoConvenioController::class, 'edit'])->name('tipos-convenio.edit');
        Route::put('tipos-convenio/{tipoConvenio}', [\App\Http\Controllers\TipoConvenioController::class, 'update'])->name('tipos-convenio.update');
    });
    Route::delete('tipos-convenio/{tipoConvenio}', [\App\Http\Controllers\TipoConvenioController::class, 'destroy'])
        ->middleware('permission:tipos-convenio.delete')
        ->name('tipos-convenio.destroy');

    // Setores de Pesquisa
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:setores-pesquisa.view')->group(function () {
        Route::get('setores-pesquisa', [\App\Http\Controllers\SetorPesquisaController::class, 'index'])->name('setores-pesquisa.index');
    });
    Route::middleware('permission:setores-pesquisa.create')->group(function () {
        Route::get('setores-pesquisa/create', [\App\Http\Controllers\SetorPesquisaController::class, 'create'])->name('setores-pesquisa.create');
        Route::post('setores-pesquisa', [\App\Http\Controllers\SetorPesquisaController::class, 'store'])->name('setores-pesquisa.store');
    });
    Route::middleware('permission:setores-pesquisa.view')->group(function () {
        Route::get('setores-pesquisa/{setorPesquisa}', [\App\Http\Controllers\SetorPesquisaController::class, 'show'])->name('setores-pesquisa.show');
    });
    Route::middleware('permission:setores-pesquisa.edit')->group(function () {
        Route::get('setores-pesquisa/{setorPesquisa}/edit', [\App\Http\Controllers\SetorPesquisaController::class, 'edit'])->name('setores-pesquisa.edit');
        Route::put('setores-pesquisa/{setorPesquisa}', [\App\Http\Controllers\SetorPesquisaController::class, 'update'])->name('setores-pesquisa.update');
    });
    Route::delete('setores-pesquisa/{setorPesquisa}', [\App\Http\Controllers\SetorPesquisaController::class, 'destroy'])
        ->middleware('permission:setores-pesquisa.delete')
        ->name('setores-pesquisa.destroy');

    // Perguntas
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:perguntas.view')->group(function () {
        Route::get('perguntas', [\App\Http\Controllers\PerguntaController::class, 'index'])->name('perguntas.index');
    });
    Route::middleware('permission:perguntas.create')->group(function () {
        Route::get('perguntas/create', [\App\Http\Controllers\PerguntaController::class, 'create'])->name('perguntas.create');
        Route::post('perguntas', [\App\Http\Controllers\PerguntaController::class, 'store'])->name('perguntas.store');
    });
    Route::middleware('permission:perguntas.view')->group(function () {
        Route::get('perguntas/{pergunta}', [\App\Http\Controllers\PerguntaController::class, 'show'])->name('perguntas.show');
    });
    Route::middleware('permission:perguntas.edit')->group(function () {
        Route::get('perguntas/{pergunta}/edit', [\App\Http\Controllers\PerguntaController::class, 'edit'])->name('perguntas.edit');
        Route::put('perguntas/{pergunta}', [\App\Http\Controllers\PerguntaController::class, 'update'])->name('perguntas.update');
    });
    Route::delete('perguntas/{pergunta}', [\App\Http\Controllers\PerguntaController::class, 'destroy'])
        ->middleware('permission:perguntas.delete')
        ->name('perguntas.destroy');

    // Satisfação
    // Rotas específicas devem vir antes das rotas com parâmetros
    Route::middleware('permission:satisfacao.view')->group(function () {
        Route::get('satisfacao', [\App\Http\Controllers\SatisfacaoController::class, 'index'])->name('satisfacao.index');
    });
    Route::middleware('permission:satisfacao.create')->group(function () {
        Route::get('satisfacao/create', [\App\Http\Controllers\SatisfacaoController::class, 'create'])->name('satisfacao.create');
        Route::post('satisfacao', [\App\Http\Controllers\SatisfacaoController::class, 'store'])->name('satisfacao.store');
    });
    Route::middleware('permission:satisfacao.view')->group(function () {
        Route::get('satisfacao/{satisfacao}', [\App\Http\Controllers\SatisfacaoController::class, 'show'])->name('satisfacao.show');
    });
    Route::middleware('permission:satisfacao.edit')->group(function () {
        Route::get('satisfacao/{satisfacao}/edit', [\App\Http\Controllers\SatisfacaoController::class, 'edit'])->name('satisfacao.edit');
        Route::put('satisfacao/{satisfacao}', [\App\Http\Controllers\SatisfacaoController::class, 'update'])->name('satisfacao.update');
    });
    Route::delete('satisfacao/{satisfacao}', [\App\Http\Controllers\SatisfacaoController::class, 'destroy'])
        ->middleware('permission:satisfacao.delete')
        ->name('satisfacao.destroy');

    Route::get('metricas', [\App\Http\Controllers\MetricaController::class, 'index'])
        ->middleware('permission:metricas.view')
        ->name('metricas.index');
});

require __DIR__.'/settings.php';
