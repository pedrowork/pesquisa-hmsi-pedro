<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('perguntas_descricao', function (Blueprint $table) {
            $table->boolean('obrigatoria')->default(false)->after('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perguntas_descricao', function (Blueprint $table) {
            $table->dropColumn('obrigatoria');
        });
    }
};
