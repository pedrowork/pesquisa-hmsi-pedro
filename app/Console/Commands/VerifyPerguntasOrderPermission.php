<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class VerifyPerguntasOrderPermission extends Command
{
    protected $signature = 'perguntas:verify-order-permission';
    protected $description = 'Verifica se a permissão perguntas.order existe e está atribuída corretamente';

    public function handle()
    {
        $this->info('=== Verificando permissão perguntas.order ===');
        $this->newLine();

        // 1. Verificar se a permissão existe
        $permission = DB::table('permissions')
            ->where('slug', 'perguntas.order')
            ->first();

        if (!$permission) {
            $this->error('❌ Permissão perguntas.order NÃO existe na tabela permissions!');
            $this->warn('Execute: php artisan db:seed --class=PermissionSeeder');
            return 1;
        }

        $this->info('✅ Permissão encontrada:');
        $this->line("   ID: {$permission->id}");
        $this->line("   Nome: {$permission->name}");
        $this->line("   Slug: {$permission->slug}");
        $this->line("   Descrição: {$permission->description}");
        $this->newLine();

        // 2. Verificar se Admin tem a permissão (deve ter TODAS)
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            $adminHasPermission = DB::table('role_permissions')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permission->id)
                ->exists();

            $totalAdminPermissions = DB::table('role_permissions')
                ->where('role_id', $adminRole->id)
                ->count();

            $totalSystemPermissions = DB::table('permissions')->count();

            if ($adminHasPermission) {
                $this->info("✅ Role Admin tem a permissão ({$totalAdminPermissions}/{$totalSystemPermissions} permissões)");
            } else {
                $this->error("❌ Role Admin NÃO tem a permissão perguntas.order");
                $this->warn("   Admin deveria ter TODAS as permissões!");
            }
        }
        $this->newLine();

        // 3. Verificar se Master tem a permissão
        $masterRole = DB::table('roles')->where('slug', 'master')->first();
        if ($masterRole) {
            $masterHasPermission = DB::table('role_permissions')
                ->where('role_id', $masterRole->id)
                ->where('permission_id', $permission->id)
                ->exists();

            if ($masterHasPermission) {
                $this->info('✅ Role Master tem a permissão');
            } else {
                $this->error('❌ Role Master NÃO tem a permissão perguntas.order');
                $this->warn('Execute: php artisan db:seed --class=AdminSeeder');
            }
        }
        $this->newLine();

        // 4. Verificar quais usuários têm a permissão
        $usersWithPermission = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
            ->where('role_permissions.permission_id', $permission->id)
            ->select('users.id', 'users.name', 'users.email', 'roles.slug as role_slug')
            ->distinct()
            ->get();

        if ($usersWithPermission->isEmpty()) {
            $this->warn('⚠️  Nenhum usuário tem essa permissão via roles');
        } else {
            $this->info('Usuários com a permissão:');
            foreach ($usersWithPermission as $user) {
                $this->line("   - {$user->name} ({$user->email}) - Role: {$user->role_slug}");
            }
        }
        $this->newLine();

        // 5. Testar hasPermission em usuários Admin e Master
        $adminUser = User::where('email', 'p@h.com')->first();
        $masterUser = User::where('email', 'm@l.com')->first();

        if ($adminUser) {
            $adminCanOrder = $adminUser->hasPermission('perguntas.order');
            if ($adminCanOrder) {
                $this->info("✅ Usuário Admin ({$adminUser->email}) tem permissão via hasPermission()");
            } else {
                $this->error("❌ Usuário Admin ({$adminUser->email}) NÃO tem permissão via hasPermission()");
            }
        }

        if ($masterUser) {
            // Limpar cache antes de verificar
            $masterUser->clearPermissionsCache();

            $masterCanOrder = $masterUser->hasPermission('perguntas.order');
            if ($masterCanOrder) {
                $this->info("✅ Usuário Master ({$masterUser->email}) tem permissão via hasPermission()");
            } else {
                $this->error("❌ Usuário Master ({$masterUser->email}) NÃO tem permissão via hasPermission()");
                $this->warn('Tentando limpar cache e verificar novamente...');

                // Limpar cache novamente e verificar
                $masterUser->clearPermissionsCache();
                $masterCanOrder = $masterUser->hasPermission('perguntas.order');

                if ($masterCanOrder) {
                    $this->info("✅ Após limpar cache: Usuário Master ({$masterUser->email}) tem permissão");
                } else {
                    $this->error("❌ Após limpar cache: Usuário Master ainda NÃO tem permissão");
                }
            }
        }

        $this->newLine();
        $this->info('=== Verificação concluída ===');
        $this->info('💡 Dica: Se a permissão não estiver funcionando, limpe o cache do usuário Master fazendo logout e login novamente.');
        return 0;
    }
}
