<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUserSessions extends Command
{
    protected $signature = 'user:sessions {email}';
    protected $description = 'Lista todas as sessões ativas de um usuário';

    public function handle(): int
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuário não encontrado: {$email}");
            return 1;
        }

        $this->info("=== Informações do Usuário ===");
        $this->line("ID: {$user->id}");
        $this->line("Nome: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Sessão Única Habilitada: " . ($user->single_session_enabled ? 'Sim' : 'Não'));
        $this->line("Current Session ID (salvo no banco): " . ($user->current_session_id ?? 'null'));
        $this->line("");

        // Buscar sessões ativas na tabela sessions
        try {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderBy('last_activity', 'desc')
                ->get(['id', 'ip_address', 'user_agent', 'last_activity']);

            $this->info("=== Sessões Ativas na Tabela 'sessions' ===");
            
            if ($sessions->isEmpty()) {
                $this->warn("Nenhuma sessão encontrada na tabela 'sessions' para este usuário.");
            } else {
                $this->line("Total de sessões encontradas: " . $sessions->count());
                $this->line("");

                foreach ($sessions as $index => $session) {
                    $isCurrent = $session->id === $user->current_session_id;
                    $status = $isCurrent ? "✓ ATIVA (Current Session ID)" : "⚠ Outra sessão";
                    
                    $this->line("--- Sessão #" . ($index + 1) . " ({$status}) ---");
                    $this->line("Session ID: {$session->id}");
                    $this->line("IP Address: " . ($session->ip_address ?? 'N/A'));
                    $this->line("User Agent: " . ($session->user_agent ?? 'N/A'));
                    
                    // Converter last_activity (timestamp Unix) para data legível
                    $lastActivity = $session->last_activity ? date('Y-m-d H:i:s', $session->last_activity) : 'N/A';
                    $this->line("Última Atividade: {$lastActivity}");
                    $this->line("");
                }
            }
        } catch (\Exception $e) {
            $this->error("Erro ao buscar sessões: " . $e->getMessage());
            $this->warn("A tabela 'sessions' pode não existir ou ter estrutura diferente.");
        }

        // Verificar se há sessões órfãs (current_session_id que não existe mais)
        if ($user->current_session_id) {
            $sessionExists = DB::table('sessions')
                ->where('id', $user->current_session_id)
                ->exists();
            
            if (!$sessionExists) {
                $this->warn("⚠ ATENÇÃO: O 'current_session_id' salvo ({$user->current_session_id}) não existe mais na tabela 'sessions'.");
                $this->warn("   Isso pode indicar que a sessão expirou ou foi removida.");
            }
        }

        return 0;
    }
}