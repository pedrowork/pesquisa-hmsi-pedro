<?php

use App\Http\Controllers\AccountRecoveryController;
use App\Http\Controllers\Admin\SecurityDashboardController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeitoController;
use App\Http\Controllers\MetricaController;
use App\Http\Controllers\PerguntaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\QuestionarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SatisfacaoController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\SetorPesquisaController;
use App\Http\Controllers\TipoConvenioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::middleware('permission:users.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{user}', 'show')->name('show');
        });
        Route::middleware('permission:users.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:users.edit')->group(function () {
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::put('/{user}', 'update')->name('update');
            Route::post('/{user}/toggle-status', 'toggleStatus')->name('toggle-status');
        });
        Route::delete('/{user}', 'destroy')->middleware('permission:users.delete')->name('destroy');
    });

    Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::middleware('permission:roles.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{role}', 'show')->name('show');
        });
        Route::middleware('permission:roles.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:roles.edit')->group(function () {
            Route::get('/{role}/edit', 'edit')->name('edit');
            Route::put('/{role}', 'update')->name('update');
        });
        Route::delete('/{role}', 'destroy')->middleware('permission:roles.delete')->name('destroy');
    });

    Route::controller(PermissionController::class)->prefix('permissions')->name('permissions.')->group(function () {
        Route::middleware('permission:permissions.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permission}', 'show')->name('show')->where('permission', '[0-9]+');
        });
        Route::middleware('permission:permissions.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:permissions.edit')->group(function () {
            Route::get('/{permission}/edit', 'edit')->name('edit');
            Route::put('/{permission}', 'update')->name('update');
            Route::post('/roles/{role}/permissions', 'updateRolePermissions')->name('roles.update');
            Route::post('/users/{user}/permissions', 'updateUserPermissions')->name('users.update');
            Route::post('/roles/{role}/toggle/{permission}', 'toggleRolePermission')->name('roles.toggle');
            Route::post('/users/{user}/toggle/{permission}', 'toggleUserPermission')->name('users.toggle');
        });
        Route::delete('/{permission}', 'destroy')->middleware('permission:permissions.delete')->name('destroy');
    });

    Route::controller(QuestionarioController::class)->prefix('questionarios')->name('questionarios.')->group(function () {
        Route::get('/', 'index')->middleware('permission:questionarios.view')->name('index');
        Route::middleware('permission:questionarios.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::get('/{questionario}', 'show')->middleware('permission:questionarios.show')->name('show');
    });

    Route::controller(LeitoController::class)->prefix('leitos')->name('leitos.')->group(function () {
        Route::middleware('permission:leitos.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{leito}', 'show')->name('show');
        });
        Route::middleware('permission:leitos.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:leitos.edit')->group(function () {
            Route::get('/{leito}/edit', 'edit')->name('edit');
            Route::put('/{leito}', 'update')->name('update');
        });
        Route::delete('/{leito}', 'destroy')->middleware('permission:leitos.delete')->name('destroy');
    });

    Route::controller(SetorController::class)->prefix('setores')->name('setores.')->group(function () {
        Route::middleware('permission:setores.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{setor}', 'show')->name('show');
        });
        Route::middleware('permission:setores.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:setores.edit')->group(function () {
            Route::get('/{setor}/edit', 'edit')->name('edit');
            Route::put('/{setor}', 'update')->name('update');
        });
        Route::delete('/{setor}', 'destroy')->middleware('permission:setores.delete')->name('destroy');
    });

    Route::controller(TipoConvenioController::class)->prefix('tipos-convenio')->name('tipos-convenio.')->group(function () {
        Route::middleware('permission:tipos-convenio.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{tipoConvenio}', 'show')->name('show');
        });
        Route::middleware('permission:tipos-convenio.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:tipos-convenio.edit')->group(function () {
            Route::get('/{tipoConvenio}/edit', 'edit')->name('edit');
            Route::put('/{tipoConvenio}', 'update')->name('update');
        });
        Route::delete('/{tipoConvenio}', 'destroy')->middleware('permission:tipos-convenio.delete')->name('destroy');
    });

    Route::controller(SetorPesquisaController::class)->prefix('setores-pesquisa')->name('setores-pesquisa.')->group(function () {
        Route::middleware('permission:setores-pesquisa.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{setorPesquisa}', 'show')->name('show');
        });
        Route::middleware('permission:setores-pesquisa.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:setores-pesquisa.edit')->group(function () {
            Route::get('/{setorPesquisa}/edit', 'edit')->name('edit');
            Route::put('/{setorPesquisa}', 'update')->name('update');
        });
        Route::delete('/{setorPesquisa}', 'destroy')->middleware('permission:setores-pesquisa.delete')->name('destroy');
    });

    Route::controller(PerguntaController::class)->prefix('perguntas')->name('perguntas.')->group(function () {
        Route::middleware('permission:perguntas.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{pergunta}', 'show')->name('show');
        });
        Route::middleware('permission:perguntas.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:perguntas.edit')->group(function () {
            Route::get('/{pergunta}/edit', 'edit')->name('edit');
            Route::put('/{pergunta}', 'update')->name('update');
        });
        Route::delete('/{pergunta}', 'destroy')->middleware('permission:perguntas.delete')->name('destroy');
        Route::post('/update-order', 'updateOrder')->middleware('permission:perguntas.order')->name('update-order');
    });

    Route::controller(SatisfacaoController::class)->prefix('satisfacao')->name('satisfacao.')->group(function () {
        Route::middleware('permission:satisfacao.view')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{satisfacao}', 'show')->name('show');
        });
        Route::middleware('permission:satisfacao.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
        Route::middleware('permission:satisfacao.edit')->group(function () {
            Route::get('/{satisfacao}/edit', 'edit')->name('edit');
            Route::put('/{satisfacao}', 'update')->name('update');
        });
        Route::delete('/{satisfacao}', 'destroy')->middleware('permission:satisfacao.delete')->name('destroy');
    });

    Route::get('metricas', [MetricaController::class, 'index'])
        ->middleware('permission:metricas.view')
        ->name('metricas.index');

    Route::controller(SecurityDashboardController::class)
        ->prefix('admin/security')
        ->name('admin.security.')
        ->middleware('permission:security.view')
        ->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/alerts', 'alerts')->name('alerts');
            Route::post('/alerts/{alert}/resolve', 'resolve')->name('alerts.resolve');
            Route::get('/export-siem', 'exportSIEM')->name('export-siem');
        });

    Route::controller(UserApprovalController::class)
        ->prefix('admin/users')
        ->name('admin.users.')
        ->middleware('permission:users.approve')
        ->group(function () {
            Route::get('/pending-approval', 'index')->name('pending-approval');
            Route::post('/{user}/approve', 'approve')->name('approve');
            Route::post('/{user}/reject', 'reject')->name('reject');
        });

    Route::controller(ProfilePhotoController::class)->prefix('profile-photo')->name('profile-photo.')->group(function () {
        Route::post('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    Route::controller(AccountRecoveryController::class)->prefix('account-recovery')->name('account-recovery.')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::post('/', 'recover')->name('recover');
    });
});

Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware('signed')
    ->name('verification.verify');

require __DIR__.'/settings.php';
