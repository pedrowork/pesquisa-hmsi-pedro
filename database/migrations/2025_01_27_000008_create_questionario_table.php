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
        Schema::create('questionario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cod_pergunta');
            $table->unsignedBigInteger('resposta');
            $table->unsignedBigInteger('cod_paciente');
            $table->unsignedBigInteger('cod_usuario');
            $table->date('data_questionario')->nullable();
            $table->boolean('data_isretroativa')->nullable();
            $table->date('data_retroativa')->nullable();
            $table->string('desc_metrica_indicacao', 255)->nullable();
            $table->integer('vl_metrica_indicacao')->nullable();
            $table->unsignedBigInteger('cod_setor_pesquis')->nullable();
            $table->string('observacao', 1000)->nullable();
            $table->string('column13', 50)->nullable();

            $table->foreign('cod_pergunta')->references('cod')->on('perguntas_descricao');
            $table->foreign('resposta')->references('cod')->on('satisfacao');
            $table->foreign('cod_paciente')->references('id')->on('dados_do_paciente');
            $table->foreign('cod_usuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionario');
    }
};

