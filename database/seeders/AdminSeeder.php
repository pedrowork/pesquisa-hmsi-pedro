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

        // Criar role master
        $masterRole = DB::table('roles')->where('slug', 'master')->first();
        if (!$masterRole) {
            $masterRoleId = DB::table('roles')->insertGetId([
                'name' => 'Master',
                'slug' => 'master',
                'description' => 'Perfil Master com permissões de gerenciamento',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $masterRole = DB::table('roles')->where('id', $masterRoleId)->first();
        } else {
            // Atualizar descrição se já existir
            DB::table('roles')->where('id', $masterRole->id)->update([
                'description' => 'Perfil Master com permissões de gerenciamento',
                'updated_at' => now(),
            ]);
            $masterRole = DB::table('roles')->where('id', $masterRole->id)->first();
        }

        // Criar role colaborador
        $colaboradorRole = DB::table('roles')->where('slug', 'colaborador')->first();
        if (!$colaboradorRole) {
            $colaboradorRoleId = DB::table('roles')->insertGetId([
                'name' => 'Colaborador',
                'slug' => 'colaborador',
                'description' => 'Perfil Colaborador com permissões básicas',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $colaboradorRole = DB::table('roles')->where('id', $colaboradorRoleId)->first();
        } else {
            // Atualizar descrição se já existir
            DB::table('roles')->where('id', $colaboradorRole->id)->update([
                'description' => 'Perfil Colaborador com permissões básicas',
                'updated_at' => now(),
            ]);
            $colaboradorRole = DB::table('roles')->where('id', $colaboradorRole->id)->first();
        }

        // Criar usuário admin
        $adminUser = User::firstOrCreate(
            ['email' => 'p@h.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 1,
            ]
        );

        // usuário master
        $masterUser = User::firstOrCreate(
            ['email' => 'm@l.com'],
            [
                'name' => 'Master',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 1,
            ]
        );

        // usuário colaborador
        $userUser = User::firstOrCreate(
            ['email' => 'c@l.com'],
            [
                'name' => 'Colaborador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 1,
            ]
        );

        // Associar role master ao usuário master (remover outras roles primeiro)
        DB::table('user_roles')->where('user_id', $masterUser->id)->delete();
        DB::table('user_roles')->updateOrInsert(
            [
                'user_id' => $masterUser->id,
                'role_id' => $masterRole->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Associar role colaborador ao usuário colaborador (remover outras roles primeiro)
        DB::table('user_roles')->where('user_id', $userUser->id)->delete();
        DB::table('user_roles')->updateOrInsert(
            [
                'user_id' => $userUser->id,
                'role_id' => $colaboradorRole->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
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

        // Associar todas as permissões ao role admin
        $allPermissions = DB::table('permissions')->pluck('id');

        foreach ($allPermissions as $permissionId) {
            $permissionExists = DB::table('role_permissions')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$permissionExists) {
                DB::table('role_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Associar permissões ao role Master
        $masterPermissions = [
            'questionarios.create',
            'users.create',
            'users.edit',
            'leitos.manage',
            'perguntas.manage',
            'satisfacao.manage',
            'setores.manage',
            'setores-pesquisa.manage',
            'tipos-convenio.manage',
            'dashboard.research.analysis',
            'dashboard.view',
            'questionarios.show',
            'metricas.view',
            'dashboard.research.metrics',
            'dashboard.research.secondary',
            'questionarios.view',
        ];

        // Limpar permissões antigas do role master
        DB::table('role_permissions')->where('role_id', $masterRole->id)->delete();

        foreach ($masterPermissions as $permissionSlug) {
            $permission = DB::table('permissions')->where('slug', $permissionSlug)->first();
            if ($permission) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_id' => $masterRole->id,
                        'permission_id' => $permission->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Associar permissões ao role Colaborador
        $colaboradorPermissions = [
            'questionarios.create',
            'questionarios.show',
            'dashboard.view',
            'questionarios.view',
        ];

        // Limpar permissões antigas do role colaborador
        DB::table('role_permissions')->where('role_id', $colaboradorRole->id)->delete();

        foreach ($colaboradorPermissions as $permissionSlug) {
            $permission = DB::table('permissions')->where('slug', $permissionSlug)->first();
            if ($permission) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_id' => $colaboradorRole->id,
                        'permission_id' => $permission->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Perfil admin criado com sucesso!');
        $this->command->info('Email: p@h.com');
        $this->command->info('Senha: password');

        $this->command->info('Perfil master criado com sucesso!');
        $this->command->info('Email: m@l.com');
        $this->command->info('Senha: password');
        $this->command->info('Permissões Master associadas: ' . count($masterPermissions));

        $this->command->info('Perfil colaborador criado com sucesso!');
        $this->command->info('Email: c@l.com');
        $this->command->info('Senha: password');
        $this->command->info('Permissões Colaborador associadas: ' . count($colaboradorPermissions));

        $this->command->info('Todas as permissões foram associadas ao role admin.');
    }
}

