<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email=teste@example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um email de teste para verificar a configuração de email (Brevo, Mailpit, etc)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('=== Teste de Configuração de Email ===');
        $this->newLine();
        $this->info('Enviando email de teste para: ' . $email);
        $this->info('Configuração atual:');
        $this->line('  MAIL_MAILER: ' . config('mail.default'));
        $this->line('  MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('  MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('  MAIL_ENCRYPTION: ' . (config('mail.mailers.smtp.encryption') ?: 'none'));
        $this->line('  MAIL_FROM: ' . config('mail.from.address') . ' (' . config('mail.from.name') . ')');
        $this->newLine();
        
        try {
            Mail::raw('Este é um email de teste do sistema Pesquisa HMSI. Se você recebeu este email, a configuração de email está funcionando corretamente!

Configuração utilizada:
- Mailer: ' . config('mail.default') . '
- Host: ' . config('mail.mailers.smtp.host') . '
- Port: ' . config('mail.mailers.smtp.port') . '

Enviado em: ' . now()->format('d/m/Y H:i:s') . '
', function ($message) use ($email) {
                $message->to($email)
                        ->subject('✅ Teste de Email - Pesquisa HMSI');
            });
            
            $this->newLine();
            $this->info('✅ Email enviado com sucesso!');
            $this->info('📧 Verifique a caixa de entrada (e spam) de: ' . $email);
            
            // Mensagem específica baseada no host
            $host = config('mail.mailers.smtp.host');
            if (str_contains($host, 'brevo') || str_contains($host, 'sendinblue')) {
                $this->info('💡 Brevo: Verifique também o dashboard em https://app.brevo.com');
            } elseif (str_contains($host, 'mailpit')) {
                $this->info('💡 Mailpit: Verifique a interface em http://localhost:8025');
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Erro ao enviar email: ' . $e->getMessage());
            $this->newLine();
            
            $host = config('mail.mailers.smtp.host');
            $isBrevo = str_contains($host, 'brevo') || str_contains($host, 'sendinblue');
            
            if ($isBrevo && str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication')) {
                $this->warn('⚠️  Erro de Autenticação Brevo - Verificações:');
                $this->newLine();
                $this->line('  1. MAIL_USERNAME deve ser o EMAIL da sua conta Brevo (não um email qualquer)');
                $this->line('  2. MAIL_PASSWORD deve ser a SMTP KEY (começa com "xsmtp-"), NÃO a senha da conta');
                $this->line('  3. Como obter a SMTP Key:');
                $this->line('     → Acesse https://app.brevo.com');
                $this->line('     → Configurações → SMTP & API');
                $this->line('     → Na seção "Chaves SMTP", copie uma chave existente ou gere uma nova');
                $this->newLine();
                $this->info('  📖 Guia completo: docs/CONFIGURACAO-BREVO.md');
            } else {
                $this->warn('Possíveis causas:');
                $this->line('  - Credenciais SMTP incorretas');
                $this->line('  - Host/Porta incorretos');
                $this->line('  - Firewall bloqueando conexão');
                $this->line('  - Domínio não verificado (para Brevo/SendGrid)');
            }
            
            $this->newLine();
            return 1;
        }
        
        return 0;
    }
}
