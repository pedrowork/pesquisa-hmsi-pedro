<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrantDashboardPermissionToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:grant-dashboard-permission {--force : Forçar atualização mesmo se usuário já tiver a permissão}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Concede a permissão dashboard.view a todos os usuários que não a possuem';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        
        $this->info('Verificando permissão dashboard.view...');
        
        // Buscar a permissão
        $dashboardPermission = DB::table('permissions')
            ->where('slug', 'dashboard.view')
            ->first();
        
        if (!$dashboardPermission) {
            $this->error('Permissão dashboard.view não encontrada. Execute o seeder de permissões primeiro.');
            return Command::FAILURE;
        }
        
        $this->info("Permissão encontrada (ID: {$dashboardPermission->id})");
        
        // Buscar todos os usuários
        $users = User::where('status', 1)->get();
        $totalUsers = $users->count();
        
        $this->info("Total de usuários ativos: {$totalUsers}");
        
        $granted = 0;
        $skipped = 0;
        
        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();
        
        foreach ($users as $user) {
            // Verificar se o usuário já tem a permissão
            $hasPermission = DB::table('user_permissions')
                ->where('user_id', $user->id)
                ->where('permission_id', $dashboardPermission->id)
                ->exists();
            
            if ($hasPermission && !$force) {
                $skipped++;
                $progressBar->advance();
                continue;
            }
            
            // Conceder permissão
            DB::table('user_permissions')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'permission_id' => $dashboardPermission->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            // Limpar cache de permissões do usuário
            $user->clearPermissionsCache();
            
            $granted++;
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Concluído!");
        $this->info("  - Permissões concedidas: {$granted}");
        $this->info("  - Usuários já tinham a permissão: {$skipped}");
        $this->info("  - Total processado: {$totalUsers}");
        
        return Command::SUCCESS;
    }
}
