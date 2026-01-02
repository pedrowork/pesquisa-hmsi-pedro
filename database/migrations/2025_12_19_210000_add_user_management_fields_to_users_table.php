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
        Schema::table('users', function (Blueprint $table) {
            // Aprovação de usuários
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');

            // Metadados do usuário
            $table->string('department')->nullable()->after('name');
            $table->string('position')->nullable()->after('department');
            $table->string('phone')->nullable()->after('position');
            $table->text('bio')->nullable()->after('phone');
            $table->string('profile_photo_path', 2048)->nullable()->after('bio');

            // Política de senha
            $table->integer('password_expires_in_days')->nullable()->after('password_changed_at');
            $table->timestamp('password_expires_at')->nullable()->after('password_expires_in_days');
            $table->boolean('password_change_required')->default(false)->after('password_expires_at');

            // Recuperação de conta
            $table->string('security_question')->nullable()->after('password_change_required');
            $table->string('security_answer')->nullable()->after('security_question');

            // Contas inativas
            $table->timestamp('last_activity_at')->nullable()->after('last_activity');
            $table->integer('inactive_days_threshold')->default(90)->after('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes',
                'department',
                'position',
                'phone',
                'bio',
                'profile_photo_path',
                'password_expires_in_days',
                'password_expires_at',
                'password_change_required',
                'security_question',
                'security_answer',
                'last_activity_at',
                'inactive_days_threshold',
            ]);
        });
    }
};

