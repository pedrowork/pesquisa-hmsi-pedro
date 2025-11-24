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
        Schema::create('dados_do_paciente', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->string('telefone', 20);
            $table->string('email', 255);
            $table->string('sexo', 10);
            $table->string('tipo_paciente', 50)->nullable();
            $table->integer('idade');
            $table->string('leito', 20)->nullable();
            $table->string('setor', 50)->nullable();
            $table->string('renda', 100)->nullable();
            $table->unsignedBigInteger('tp_cod_convenio')->nullable();
            $table->foreign('tp_cod_convenio')->references('cod')->on('tipoconvenio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dados_do_paciente');
    }
};

