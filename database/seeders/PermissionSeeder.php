<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard - Geral
            [
                'name' => 'Visualizar Dashboard',
                'slug' => 'dashboard.view',
                'description' => 'Permite visualizar o dashboard do sistema',
            ],
            // Dashboard - Estatísticas de Gerenciamento
            [
                'name' => 'Visualizar Estatísticas de Gerenciamento',
                'slug' => 'dashboard.stats.management',
                'description' => 'Permite visualizar cards de estatísticas (usuários, roles, permissões)',
            ],
            // Dashboard - Ações Rápidas
            [
                'name' => 'Visualizar Ações Rápidas',
                'slug' => 'dashboard.quick-actions',
                'description' => 'Permite visualizar seção de ações rápidas no dashboard',
            ],
            // Dashboard - Links de Gerenciamento
            [
                'name' => 'Visualizar Links de Gerenciamento',
                'slug' => 'dashboard.management-links',
                'description' => 'Permite visualizar cards de links de gerenciamento (usuários, roles, permissões)',
            ],
            // Dashboard - Métricas de Pesquisa (Principais)
            [
                'name' => 'Visualizar Métricas Principais de Pesquisa',
                'slug' => 'dashboard.research.metrics',
                'description' => 'Permite visualizar métricas principais: questionários, pacientes, satisfação',
            ],
            // Dashboard - Métricas de Pesquisa (Secundárias)
            [
                'name' => 'Visualizar Métricas Secundárias de Pesquisa',
                'slug' => 'dashboard.research.secondary',
                'description' => 'Permite visualizar métricas secundárias: respostas, pacientes do mês, análise',
            ],
            // Dashboard - Análises de Pesquisa
            [
                'name' => 'Visualizar Análises de Pesquisa',
                'slug' => 'dashboard.research.analysis',
                'description' => 'Permite visualizar análises: top setores, distribuição por tipo',
            ],

            // Gerenciamento - Usuários
            [
                'name' => 'Visualizar Usuários',
                'slug' => 'users.view',
                'description' => 'Permite visualizar a lista de usuários',
            ],
            [
                'name' => 'Criar Usuário',
                'slug' => 'users.create',
                'description' => 'Permite criar novos usuários',
            ],
            [
                'name' => 'Editar Usuário',
                'slug' => 'users.edit',
                'description' => 'Permite editar informações de usuários',
            ],
            [
                'name' => 'Excluir Usuário',
                'slug' => 'users.delete',
                'description' => 'Permite excluir usuários do sistema',
            ],

            // Gerenciamento - Roles
            [
                'name' => 'Visualizar Roles',
                'slug' => 'roles.view',
                'description' => 'Permite visualizar a lista de roles',
            ],
            [
                'name' => 'Criar Role',
                'slug' => 'roles.create',
                'description' => 'Permite criar novas roles',
            ],
            [
                'name' => 'Editar Role',
                'slug' => 'roles.edit',
                'description' => 'Permite editar roles e suas permissões',
            ],
            [
                'name' => 'Excluir Role',
                'slug' => 'roles.delete',
                'description' => 'Permite excluir roles do sistema',
            ],

            // Gerenciamento - Permissões
            [
                'name' => 'Visualizar Permissões',
                'slug' => 'permissions.view',
                'description' => 'Permite visualizar a lista de permissões',
            ],
            [
                'name' => 'Criar Permissão',
                'slug' => 'permissions.create',
                'description' => 'Permite criar novas permissões',
            ],
            [
                'name' => 'Editar Permissão',
                'slug' => 'permissions.edit',
                'description' => 'Permite editar permissões',
            ],
            [
                'name' => 'Excluir Permissão',
                'slug' => 'permissions.delete',
                'description' => 'Permite excluir permissões do sistema',
            ],

            // Pesquisa - Questionários
            [
                'name' => 'Visualizar Questionários',
                'slug' => 'questionarios.view',
                'description' => 'Permite visualizar a lista de questionários',
            ],
            [
                'name' => 'Criar Questionário',
                'slug' => 'questionarios.create',
                'description' => 'Permite criar novos questionários',
            ],
            [
                'name' => 'Visualizar Detalhes do Questionário',
                'slug' => 'questionarios.show',
                'description' => 'Permite visualizar detalhes de um questionário',
            ],

            // Pesquisa - Módulos (gerenciamento completo)
            [
                'name' => 'Gerenciar Leitos',
                'slug' => 'leitos.manage',
                'description' => 'Permite gerenciar leitos (visualizar, criar, editar, excluir)',
            ],
            [
                'name' => 'Gerenciar Setores',
                'slug' => 'setores.manage',
                'description' => 'Permite gerenciar setores (visualizar, criar, editar, excluir)',
            ],
            [
                'name' => 'Gerenciar Tipos de Convênio',
                'slug' => 'tipos-convenio.manage',
                'description' => 'Permite gerenciar tipos de convênio (visualizar, criar, editar, excluir)',
            ],
            [
                'name' => 'Gerenciar Setores de Pesquisa',
                'slug' => 'setores-pesquisa.manage',
                'description' => 'Permite gerenciar setores de pesquisa (visualizar, criar, editar, excluir)',
            ],
            [
                'name' => 'Gerenciar Perguntas',
                'slug' => 'perguntas.manage',
                'description' => 'Permite gerenciar perguntas (visualizar, criar, editar, excluir)',
            ],
            [
                'name' => 'Gerenciar Satisfação',
                'slug' => 'satisfacao.manage',
                'description' => 'Permite gerenciar opções de satisfação (visualizar, criar, editar, excluir)',
            ],
            [
                'name' => 'Visualizar Métricas',
                'slug' => 'metricas.view',
                'description' => 'Permite visualizar métricas e relatórios',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'slug' => $permission['slug'],
                    'description' => $permission['description'],
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Permissões criadas/atualizadas com sucesso!');
    }
}


