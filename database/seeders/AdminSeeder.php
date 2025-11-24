<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar role admin
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();

        if (!$adminRole) {
            $adminRoleId = DB::table('roles')->insertGetId([
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Perfil com acesso total ao sistema',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $adminRole = DB::table('roles')->where('id', $adminRoleId)->first();
        }

        // Criar usuário admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@hmsi.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'status' => 1,
            ]
        );

        // Associar role admin ao usuário admin
        $userRoleExists = DB::table('user_roles')
            ->where('user_id', $adminUser->id)
            ->where('role_id', $adminRole->id)
            ->exists();

        if (!$userRoleExists) {
            DB::table('user_roles')->insert([
                'user_id' => $adminUser->id,
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Perfil admin criado com sucesso!');
        $this->command->info('Email: admin@hmsi.com');
        $this->command->info('Senha: admin123');
    }
}

