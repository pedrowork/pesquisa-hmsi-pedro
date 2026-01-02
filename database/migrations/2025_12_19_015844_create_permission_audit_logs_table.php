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
        Schema::create('permission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // 'granted', 'revoked', 'updated', 'created', 'deleted'
            $table->string('target_type'); // 'user_permission', 'role_permission', 'role', 'permission'
            $table->foreignId('target_id')->nullable(); // ID do alvo (user_id, role_id, permission_id)
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->onDelete('set null');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->json('changes')->nullable(); // Mudanças realizadas (old values, new values)
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
            $table->index('permission_id');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_logs');
    }
};
