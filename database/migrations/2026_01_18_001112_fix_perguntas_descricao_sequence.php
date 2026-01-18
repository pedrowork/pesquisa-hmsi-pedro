<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Corrige a sequência do PostgreSQL para evitar erros de chave única duplicada.
     */
    public function up(): void
    {
        // Ajusta a sequência do PostgreSQL para o próximo valor disponível
        DB::statement("SELECT setval('perguntas_descricao_cod_seq', COALESCE((SELECT MAX(cod) FROM perguntas_descricao), 0) + 1, false)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter automaticamente este ajuste
    }
};
