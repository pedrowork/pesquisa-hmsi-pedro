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
        Schema::table('questionario', function (Blueprint $table) {
            $table->foreign('cod_setor_pesquis')
                ->references('cod')
                ->on('setor_pesquis')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionario', function (Blueprint $table) {
            $table->dropForeign(['cod_setor_pesquis']);
        });
    }
};
