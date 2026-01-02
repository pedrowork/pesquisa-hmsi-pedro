<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--encrypt : Criptografar o backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria backup seguro do banco de dados';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando backup do banco de dados...');

        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $timestamp = Carbon::now()->format('Y-m-d_His');
            $filename = "backup_{$database}_{$timestamp}.sql";
            $backupPath = storage_path("app/backups/{$filename}");

            // Criar diretório se não existir
            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            // Comando mysqldump
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Erro ao executar mysqldump');
            }

            // Criptografar se solicitado
            if ($this->option('encrypt')) {
                $this->info('Criptografando backup...');
                $encryptedPath = $backupPath . '.encrypted';
                $encryptionKey = config('app.key');

                $data = File::get($backupPath);
                $encrypted = encrypt($data);

                File::put($encryptedPath, $encrypted);
                File::delete($backupPath);

                $backupPath = $encryptedPath;
                $filename = basename($backupPath);
            }

            // Calcular tamanho
            $size = File::size($backupPath);
            $sizeFormatted = $this->formatBytes($size);

            $this->info("✓ Backup criado: {$filename}");
            $this->info("  Tamanho: {$sizeFormatted}");
            $this->info("  Localização: {$backupPath}");

            // Limpar backups antigos (manter últimos 30 dias)
            $this->cleanOldBackups();

            // Registrar no audit log
            app(\App\Services\AuditService::class)->log(
                'database_backup_created',
                'security',
                "Backup do banco de dados criado: {$filename}",
                null,
                null,
                null,
                [
                    'filename' => $filename,
                    'size' => $size,
                    'encrypted' => $this->option('encrypt'),
                ],
                'info',
                false
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao criar backup: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Limpa backups antigos.
     */
    protected function cleanOldBackups(): void
    {
        $backupDir = storage_path('app/backups');
        $files = File::files($backupDir);
        $daysToKeep = config('security.backup_retention_days', 30);
        $cutoffDate = Carbon::now()->subDays($daysToKeep);

        $deleted = 0;
        foreach ($files as $file) {
            if (Carbon::createFromTimestamp($file->getMTime())->lt($cutoffDate)) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("  Limpeza: {$deleted} backup(s) antigo(s) removido(s)");
        }
    }

    /**
     * Formata bytes para formato legível.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

