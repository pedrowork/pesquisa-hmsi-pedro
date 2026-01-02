<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class RotateSecurityKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:rotate-keys {--force : Forçar rotação mesmo se não estiver no período}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotaciona chaves e tokens de segurança da aplicação';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando rotação de chaves de segurança...');

        // Rotacionar APP_KEY
        $this->info('Rotacionando APP_KEY...');
        Artisan::call('key:generate', ['--force' => true]);
        $this->info('✓ APP_KEY rotacionada');

        // Rotacionar tokens de sessão (invalidar todas as sessões)
        $this->info('Invalidando sessões antigas...');
        \Illuminate\Support\Facades\DB::table('sessions')->truncate();
        $this->info('✓ Sessões invalidadas');

        // Rotacionar tokens de reset de senha antigos
        $this->info('Limpando tokens de reset de senha expirados...');
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours(1))
            ->delete();
        $this->info('✓ Tokens de reset limpos');

        // Limpar cache de configuração
        $this->info('Limpando cache...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('✓ Cache limpo');

        // Registrar última rotação
        cache()->put('last_key_rotation', now(), now()->addDays(365));

        // Registrar no audit log
        app(\App\Services\AuditService::class)->log(
            'security_keys_rotated',
            'security',
            'Chaves de segurança rotacionadas',
            null,
            null,
            null,
            ['rotated_at' => now()->toIso8601String()],
            'info',
            false
        );

        $this->info('');
        $this->info('✓ Rotação de chaves concluída com sucesso!');
        $this->warn('⚠️  Todos os usuários precisarão fazer login novamente.');

        return Command::SUCCESS;
    }
}

