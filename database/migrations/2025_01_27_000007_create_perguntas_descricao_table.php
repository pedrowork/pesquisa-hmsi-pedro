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
        Schema::create('perguntas_descricao', function (Blueprint $table) {
            $table->id('cod');
            $table->string('descricao', 255);
            $table->unsignedBigInteger('cod_setor_pesquis')->nullable();
            $table->unsignedBigInteger('cod_tipo_pergunta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perguntas_descricao');
    }
};

