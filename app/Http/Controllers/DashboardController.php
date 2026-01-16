<!-- app/Http/Controllers/DashboardController.php -->
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal do sistema.
     */
    public function index(): Response
    {
        // Obtém o usuário autenticado
        $user = Auth::user();

        // Obtém permissões do usuário
        $permissions = [];
        $isAdmin = false;

        if ($user) {
            // Método 1: Se usar Spatie Laravel Permission
            // $permissions = $user->getPermissionNames()->toArray();
            // $isAdmin = $user->hasRole('admin');

            // Método 2: Se usar seu próprio sistema (com o trait HasPermissions)
            $permissions = $user->getAllPermissions(); // Verifique se retorna array de strings
            $isAdmin = $user->hasRole('admin') || $user->isAdmin(); // Ajuste conforme sua lógica

            // Método 3: Fallback - carregar via query se necessário
            if (empty($permissions)) {
                $permissions = \Illuminate\Support\Facades\DB::table('user_permissions')
                    ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                    ->where('user_id', $user->id)
                    ->pluck('permissions.slug')
                    ->toArray();
            }
        }

        return Inertia::render('Dashboard', [
            // Dados ESSENCIAIS para o hook usePermissions
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
                'permissions' => $permissions, // DEVE SER ARRAY
                'isAdmin' => $isAdmin,
            ],
            // Adicione outros dados específicos do dashboard aqui
            'stats' => [
                'totalUsers' => \App\Models\User::count(),
                'activeUsers' => \App\Models\User::where('status', 1)->count(),
                // ... outros stats
            ],
        ]);
    }
}
