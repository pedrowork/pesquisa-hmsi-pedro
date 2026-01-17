<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidateProductionConfig extends Command
{
    protected $signature = 'config:validate-production {--fix : Corrige automaticamente configurações críticas}';
    protected $description = 'Valida configurações críticas para produção';

    private $errors = [];
    private $warnings = [];

    public function handle()
    {
        $this->info('=== Validação de Configurações de Produção ===');
        $this->newLine();

        // 1. Verificar se .env existe
        if (!File::exists(base_path('.env'))) {
            $this->error('❌ CRÍTICO: Arquivo .env não encontrado!');
            $this->warn('   Crie um arquivo .env baseado em .env.example.production');
            return 1;
        }

        // 2. Verificar variáveis críticas
        $this->validateCriticalEnvVars();
        
        // 3. Verificar banco de dados
        $this->validateDatabaseConfig();
        
        // 4. Verificar segurança
        $this->validateSecurityConfig();

        // 5. Corrigir automaticamente se solicitado
        if ($this->option('fix') && !empty($this->errors)) {
            $this->fixCriticalIssues();
            // Re-validar após correções
            $this->errors = [];
            $this->warnings = [];
            $this->validateCriticalEnvVars();
            $this->validateDatabaseConfig();
            $this->validateSecurityConfig();
        }

        // 6. Exibir resumo
        $this->displaySummary();

        return count($this->errors) > 0 ? 1 : 0;
    }

    private function validateCriticalEnvVars()
    {
        $this->info('1. Verificando variáveis críticas...');

        $criticalVars = [
            'APP_ENV' => ['value' => 'production', 'required' => true],
            'APP_DEBUG' => ['value' => 'false', 'required' => true],
            'APP_URL' => ['pattern' => '/^https:\/\//', 'required' => true],
            'APP_KEY' => ['pattern' => '/^base64:.+/', 'required' => true],
        ];

        foreach ($criticalVars as $var => $rules) {
            $value = env($var);
            $isEmpty = empty($value) && $value !== '0' && $value !== 0 && $value !== false;

            if ($rules['required'] && $isEmpty) {
                $this->errors[] = "CRÍTICO: {$var} não está definido";
                continue;
            }

            if (isset($rules['value']) && !$isEmpty && strtolower($value) !== strtolower($rules['value'])) {
                $this->errors[] = "CRÍTICO: {$var} deve ser '{$rules['value']}', atual: '{$value}'";
            }

            if (isset($rules['pattern']) && !$isEmpty && !preg_match($rules['pattern'], $value)) {
                $this->errors[] = "CRÍTICO: {$var} formato inválido. Atual: '{$value}'";
            }
        }

        $this->checkResult('Variáveis críticas');
    }

    private function validateDatabaseConfig()
    {
        $this->info('2. Verificando configuração de banco de dados...');

        $connection = env('DB_CONNECTION', 'sqlite');

        // SQLite não é permitido em produção
        if ($connection === 'sqlite') {
            $this->errors[] = "CRÍTICO: DB_CONNECTION não pode ser 'sqlite' em produção. Use 'mysql' ou 'pgsql'";
        }

        // Verificar se credenciais MySQL/PostgreSQL estão configuradas
        if (in_array($connection, ['mysql', 'pgsql'])) {
            $requiredVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
            
            foreach ($requiredVars as $var) {
                if (empty(env($var))) {
                    $this->errors[] = "CRÍTICO: {$var} não está definido (necessário para {$connection})";
                }
            }

            // Verificar senhas fracas
            $password = env('DB_PASSWORD', '');
            if (strlen($password) < 12) {
                $this->warnings[] = "AVISO: DB_PASSWORD deve ter pelo menos 12 caracteres";
            }
        }

        $this->checkResult('Configuração de banco');
    }

    private function validateSecurityConfig()
    {
        $this->info('3. Verificando configurações de segurança...');

        // SESSION_SECURE_COOKIE deve ser true com HTTPS
        $appUrl = env('APP_URL', '');
        $secureCookie = env('SESSION_SECURE_COOKIE', 'false');

        if (str_starts_with($appUrl, 'https://') && $secureCookie !== 'true') {
            $this->errors[] = "CRÍTICO: SESSION_SECURE_COOKIE deve ser 'true' quando APP_URL usa HTTPS";
        }

        // Verificar se está usando HTTPS
        if (!str_starts_with($appUrl, 'https://')) {
            $this->warnings[] = "AVISO: APP_URL deve usar HTTPS em produção";
        }

        // Verificar senhas padrão
        $defaultEmails = ['p@h.com', 'm@l.com', 'c@l.com'];
        $adminEmail = env('ADMIN_EMAIL', '');
        if (in_array($adminEmail, $defaultEmails)) {
            $this->warnings[] = "AVISO: Verifique se usuários padrão foram removidos ou tiveram senhas alteradas";
        }

        $this->checkResult('Configurações de segurança');
    }

    private function checkResult($section)
    {
        if (empty($this->errors) && empty($this->warnings)) {
            $this->line("   ✅ {$section} - OK");
        }
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info('=== Resumo da Validação ===');
        $this->newLine();

        if (empty($this->errors) && empty($this->warnings)) {
            $this->info('✅ Todas as configurações críticas estão corretas!');
            return;
        }

        if (!empty($this->errors)) {
            $this->error('❌ ERROS CRÍTICOS ENCONTRADOS:');
            foreach ($this->errors as $error) {
                $this->line("   • {$error}");
            }
            $this->newLine();
        }

        if (!empty($this->warnings)) {
            $this->warn('⚠️  AVISOS:');
            foreach ($this->warnings as $warning) {
                $this->line("   • {$warning}");
            }
            $this->newLine();
        }

        if (!empty($this->errors)) {
            $this->error('Corrija os erros acima antes de fazer deploy em produção!');
            $this->newLine();
            $this->warn('💡 Dica: Execute "php artisan config:validate-production --fix" para corrigir automaticamente algumas configurações.');
        }
    }

    private function fixCriticalIssues()
    {
        $this->newLine();
        $this->info('🔧 Corrigindo configurações críticas...');
        $this->newLine();

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('❌ Arquivo .env não encontrado!');
            return;
        }

        $envContent = File::get($envPath);
        $fixed = false;

        // Fix APP_ENV
        if (!preg_match('/^APP_ENV=/m', $envContent) || preg_match('/^APP_ENV=(local|development)/m', $envContent)) {
            $envContent = preg_replace('/^APP_ENV=.*/m', 'APP_ENV=production', $envContent);
            $this->line('   ✅ Corrigido: APP_ENV=production');
            $fixed = true;
        }

        // Fix APP_DEBUG
        if (!preg_match('/^APP_DEBUG=/m', $envContent) || preg_match('/^APP_DEBUG=(true|1)/m', $envContent)) {
            $envContent = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=false', $envContent);
            $this->line('   ✅ Corrigido: APP_DEBUG=false');
            $fixed = true;
        }

        // Fix SESSION_SECURE_COOKIE
        $appUrl = env('APP_URL', '');
        if (str_starts_with($appUrl, 'https://')) {
            if (!preg_match('/^SESSION_SECURE_COOKIE=/m', $envContent) || preg_match('/^SESSION_SECURE_COOKIE=(false|0)/m', $envContent)) {
                if (preg_match('/^SESSION_SECURE_COOKIE=/m', $envContent)) {
                    $envContent = preg_replace('/^SESSION_SECURE_COOKIE=.*/m', 'SESSION_SECURE_COOKIE=true', $envContent);
                } else {
                    // Adicionar após SESSION_LIFETIME se existir
                    if (preg_match('/^SESSION_LIFETIME=/m', $envContent)) {
                        $envContent = preg_replace('/(^SESSION_LIFETIME=.*)/m', "$1\nSESSION_SECURE_COOKIE=true", $envContent);
                    } else {
                        // Adicionar na seção de sessão
                        $envContent .= "\nSESSION_SECURE_COOKIE=true\n";
                    }
                }
                $this->line('   ✅ Corrigido: SESSION_SECURE_COOKIE=true');
                $fixed = true;
            }
        }

        // Fix DB_CONNECTION se for sqlite
        if (preg_match('/^DB_CONNECTION=sqlite/m', $envContent)) {
            $this->warn('   ⚠️  DB_CONNECTION=sqlite encontrado. Altere manualmente para mysql ou pgsql e configure credenciais.');
        }

        // Fix APP_URL se usar http://
        if (preg_match('/^APP_URL=http:\/\//m', $envContent)) {
            $this->warn('   ⚠️  APP_URL usa HTTP. Para produção, configure HTTPS manualmente.');
        }

        if ($fixed) {
            File::put($envPath, $envContent);
            $this->newLine();
            $this->info('✅ Correções aplicadas! Execute novamente para validar.');
            $this->warn('⚠️  IMPORTANTE: Revise o arquivo .env manualmente para configurações específicas.');
        } else {
            $this->info('   ℹ️  Nenhuma correção automática necessária.');
        }
    }
}
