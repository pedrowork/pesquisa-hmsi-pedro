<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:deactivate-inactive {--days=90 : Número de dias de inatividade}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Desativa automaticamente contas de usuários inativos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $thresholdDate = now()->subDays($days);

        $inactiveUsers = User::where('status', 1)
            ->where(function ($query) use ($thresholdDate) {
                $query->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<', $thresholdDate);
            })
            ->where('approval_status', 'approved')
            ->get();

        $count = 0;

        foreach ($inactiveUsers as $user) {
            $user->update([
                'status' => 0,
            ]);

            // Registrar no audit log
            app(\App\Services\AuditService::class)->log(
                'user_deactivated',
                'user_management',
                "Usuário desativado automaticamente por inatividade ({$days} dias)",
                $user,
                ['status' => 1],
                ['status' => 0],
                null,
                'info',
                false
            );

            $count++;
        }

        if ($count > 0) {
            $this->info("{$count} usuário(s) desativado(s) por inatividade.");
            Log::info("DeactivateInactiveUsers: {$count} usuário(s) desativado(s)", [
                'days' => $days,
                'threshold_date' => $thresholdDate->toDateString(),
            ]);
        } else {
            $this->info('Nenhum usuário inativo encontrado.');
        }

        return Command::SUCCESS;
    }
}

