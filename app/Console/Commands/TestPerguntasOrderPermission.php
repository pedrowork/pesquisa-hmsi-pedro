<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TestPerguntasOrderPermission extends Command
{
    protected $signature = 'perguntas:test-order-permission {email?}';
    protected $description = 'Testa a permissão perguntas.order para um usuário específico';

    public function handle()
    {
        $email = $this->argument('email') ?? 'm@l.com';

        $this->info("=== Testando permissão perguntas.order para: {$email} ===");
        $this->newLine();

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ Usuário não encontrado: {$email}");
            return 1;
        }

        $this->info("Usuário: {$user->name} ({$user->email})");
        $this->newLine();

        // Verificar roles do usuário
        $roles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $user->id)
            ->select('roles.name', 'roles.slug')
            ->get();

        $this->info('Roles do usuário:');
        foreach ($roles as $role) {
            $this->line("  - {$role->name} ({$role->slug})");
        }
        $this->newLine();

        // Verificar se Master tem a permissão no banco
        $masterRole = DB::table('roles')->where('slug', 'master')->first();
        $perguntasOrderPermission = DB::table('permissions')->where('slug', 'perguntas.order')->first();

        if ($masterRole && $perguntasOrderPermission) {
            $masterHasPermission = DB::table('role_permissions')
                ->where('role_id', $masterRole->id)
                ->where('permission_id', $perguntasOrderPermission->id)
                ->exists();

            if ($masterHasPermission) {
                $this->info("✅ Role Master TEM a permissão perguntas.order no banco");
            } else {
                $this->error("❌ Role Master NÃO tem a permissão perguntas.order no banco");
            }
        }
        $this->newLine();

        // Limpar cache e testar
        $this->info('1. Testando SEM limpar cache:');
        $hasPermissionBefore = $user->hasPermission('perguntas.order');
        $this->line($hasPermissionBefore ? '   ✅ Tem permissão' : '   ❌ NÃO tem permissão');

        $this->newLine();
        $this->info('2. Limpando cache...');
        $user->clearPermissionsCache();

        $this->newLine();
        $this->info('3. Testando APÓS limpar cache:');
        $hasPermissionAfter = $user->hasPermission('perguntas.order');
        $this->line($hasPermissionAfter ? '   ✅ Tem permissão' : '   ❌ NÃO tem permissão');

        $this->newLine();
        if ($hasPermissionAfter) {
            $this->info('✅ Permissão funcionando corretamente!');
            $this->warn('💡 Se o botão ainda não aparece, faça logout e login novamente para limpar o cache da sessão.');
        } else {
            $this->error('❌ Permissão ainda não está funcionando!');
            $this->warn('💡 Verifique se a permissão está atribuída ao role Master no banco de dados.');
            $this->warn('   Execute: php artisan db:seed --class=AdminSeeder');
        }

        return 0;
    }
}
