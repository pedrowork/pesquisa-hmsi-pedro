<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Limpar dados antigos diariamente (90 dias por padrão)
        $schedule->command('data:prune --days=90 --type=all')
            ->daily()
            ->at('02:00');

        // Desativar usuários inativos semanalmente
        if (config('security.auto_deactivate_inactive_users', true)) {
            $days = config('security.inactive_days_threshold', 90);
            $schedule->command("users:deactivate-inactive --days={$days}")
                ->weekly()
                ->sundays()
                ->at('03:00');
        }

        // Backup do banco de dados diariamente
        $schedule->command('db:backup --encrypt')
            ->daily()
            ->at('02:00')
            ->onOneServer();

        // Rotação de chaves (a cada 90 dias)
        $schedule->command('security:rotate-keys')
            ->monthly()
            ->when(function () {
                $lastRotation = cache()->get('last_key_rotation');
                if (!$lastRotation) {
                    return true;
                }
                $daysSinceRotation = now()->diffInDays($lastRotation);
                return $daysSinceRotation >= config('security.key_rotation_days', 90);
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

