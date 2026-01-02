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
        Schema::create('data_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('model_type'); // Ex: 'App\Models\User', 'App\Models\Patient'
            $table->unsignedBigInteger('model_id')->nullable(); // ID do registro acessado
            $table->string('action'); // 'view', 'create', 'update', 'delete', 'export'
            $table->json('accessed_fields')->nullable(); // Campos acessados (mascarados)
            $table->json('changes')->nullable(); // Mudanças realizadas (para update/create)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_access_logs');
    }
};
