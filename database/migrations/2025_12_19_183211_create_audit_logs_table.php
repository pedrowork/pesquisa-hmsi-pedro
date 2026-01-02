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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type'); // 'user.created', 'user.updated', 'user.deleted', 'permission.granted', 'role.created', 'password.changed', 'admin.action', etc.
            $table->string('category'); // 'user_management', 'permission_management', 'security', 'system', 'data_access'
            $table->string('severity')->default('info'); // 'info', 'warning', 'error', 'critical'
            $table->string('model_type')->nullable(); // Modelo relacionado
            $table->unsignedBigInteger('model_id')->nullable(); // ID do modelo relacionado
            $table->json('old_values')->nullable(); // Valores antigos (para updates)
            $table->json('new_values')->nullable(); // Valores novos
            $table->json('metadata')->nullable(); // Dados adicionais
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_security_alert')->default(false); // Se deve gerar alerta de segurança
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index(['is_security_alert', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
