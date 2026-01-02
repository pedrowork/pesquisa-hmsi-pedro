<?php

namespace App\Console\Commands\DataRetention;

use App\Models\AuditLog;
use App\Models\DataAccessLog;
use App\Models\LoginAttempt;
use App\Models\PermissionAuditLog;
use Illuminate\Console\Command;

class PruneOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:prune 
                            {--days=90 : Número de dias para manter os dados}
                            {--type=all : Tipo de dados para limpar (all, logs, audits, audit_logs, login_attempts)}
                            {--force : Executar sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove dados antigos conforme política de retenção';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $type = $this->option('type');
        $force = $this->option('force');

        if (!$force && !$this->confirm("Tem certeza que deseja remover dados mais antigos que {$days} dias?")) {
            $this->info('Operação cancelada.');
            return Command::FAILURE;
        }

        $cutoffDate = now()->subDays($days);
        $deleted = 0;

        $this->info("Removendo dados anteriores a {$cutoffDate->format('Y-m-d H:i:s')}...");

        if ($type === 'all' || $type === 'logs') {
            $count = DataAccessLog::where('created_at', '<', $cutoffDate)->delete();
            $deleted += $count;
            $this->info("✓ Removidos {$count} logs de acesso a dados.");
        }

        if ($type === 'all' || $type === 'audits') {
            $count = PermissionAuditLog::where('created_at', '<', $cutoffDate)->delete();
            $deleted += $count;
            $this->info("✓ Removidos {$count} logs de auditoria de permissões.");
        }

        if ($type === 'all' || $type === 'audit_logs') {
            $count = AuditLog::where('created_at', '<', $cutoffDate)
                ->where('is_security_alert', false) // Não remover alertas de segurança
                ->delete();
            $deleted += $count;
            $this->info("✓ Removidos {$count} logs de auditoria gerais.");
        }

        if ($type === 'all' || $type === 'login_attempts') {
            $count = LoginAttempt::where('created_at', '<', $cutoffDate)->delete();
            $deleted += $count;
            $this->info("✓ Removidos {$count} logs de tentativas de login.");
        }

        $this->info("Total de registros removidos: {$deleted}");

        return Command::SUCCESS;
    }
}
