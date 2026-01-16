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
    protected $description = 'Envia um email de teste para verificar a configuração do Mailpit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Enviando email de teste para: ' . $email);
        $this->info('Configuração atual:');
        $this->line('  MAIL_MAILER: ' . config('mail.default'));
        $this->line('  MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('  MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('  MAIL_FROM: ' . config('mail.from.address'));
        
        try {
            Mail::raw('Este é um email de teste do Mailpit. Se você recebeu este email, a configuração está funcionando corretamente!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Teste de Email - Mailpit');
            });
            
            $this->info('✅ Email enviado com sucesso!');
            $this->info('Verifique o Mailpit em: http://localhost:8025');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao enviar email: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
