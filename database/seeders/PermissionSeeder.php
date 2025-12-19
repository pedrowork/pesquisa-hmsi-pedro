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

            // Pesquisa - Leitos (permissões granulares)
            [
                'name' => 'Visualizar Leitos',
                'slug' => 'leitos.view',
                'description' => 'Permite visualizar a lista de leitos',
            ],
            [
                'name' => 'Criar Leito',
                'slug' => 'leitos.create',
                'description' => 'Permite criar novos leitos',
            ],
            [
                'name' => 'Editar Leito',
                'slug' => 'leitos.edit',
                'description' => 'Permite editar leitos existentes',
            ],
            [
                'name' => 'Excluir Leito',
                'slug' => 'leitos.delete',
                'description' => 'Permite excluir leitos do sistema',
            ],

            // Pesquisa - Setores (permissões granulares)
            [
                'name' => 'Visualizar Setores',
                'slug' => 'setores.view',
                'description' => 'Permite visualizar a lista de setores',
            ],
            [
                'name' => 'Criar Setor',
                'slug' => 'setores.create',
                'description' => 'Permite criar novos setores',
            ],
            [
                'name' => 'Editar Setor',
                'slug' => 'setores.edit',
                'description' => 'Permite editar setores existentes',
            ],
            [
                'name' => 'Excluir Setor',
                'slug' => 'setores.delete',
                'description' => 'Permite excluir setores do sistema',
            ],

            // Pesquisa - Tipos de Convênio (permissões granulares)
            [
                'name' => 'Visualizar Tipos de Convênio',
                'slug' => 'tipos-convenio.view',
                'description' => 'Permite visualizar a lista de tipos de convênio',
            ],
            [
                'name' => 'Criar Tipo de Convênio',
                'slug' => 'tipos-convenio.create',
                'description' => 'Permite criar novos tipos de convênio',
            ],
            [
                'name' => 'Editar Tipo de Convênio',
                'slug' => 'tipos-convenio.edit',
                'description' => 'Permite editar tipos de convênio existentes',
            ],
            [
                'name' => 'Excluir Tipo de Convênio',
                'slug' => 'tipos-convenio.delete',
                'description' => 'Permite excluir tipos de convênio do sistema',
            ],

            // Pesquisa - Setores de Pesquisa (permissões granulares)
            [
                'name' => 'Visualizar Setores de Pesquisa',
                'slug' => 'setores-pesquisa.view',
                'description' => 'Permite visualizar a lista de setores de pesquisa',
            ],
            [
                'name' => 'Criar Setor de Pesquisa',
                'slug' => 'setores-pesquisa.create',
                'description' => 'Permite criar novos setores de pesquisa',
            ],
            [
                'name' => 'Editar Setor de Pesquisa',
                'slug' => 'setores-pesquisa.edit',
                'description' => 'Permite editar setores de pesquisa existentes',
            ],
            [
                'name' => 'Excluir Setor de Pesquisa',
                'slug' => 'setores-pesquisa.delete',
                'description' => 'Permite excluir setores de pesquisa do sistema',
            ],

            // Pesquisa - Perguntas (permissões granulares)
            [
                'name' => 'Visualizar Perguntas',
                'slug' => 'perguntas.view',
                'description' => 'Permite visualizar a lista de perguntas',
            ],
            [
                'name' => 'Criar Pergunta',
                'slug' => 'perguntas.create',
                'description' => 'Permite criar novas perguntas',
            ],
            [
                'name' => 'Editar Pergunta',
                'slug' => 'perguntas.edit',
                'description' => 'Permite editar perguntas existentes',
            ],
            [
                'name' => 'Excluir Pergunta',
                'slug' => 'perguntas.delete',
                'description' => 'Permite excluir perguntas do sistema',
            ],

            // Pesquisa - Satisfação (permissões granulares)
            [
                'name' => 'Visualizar Satisfação',
                'slug' => 'satisfacao.view',
                'description' => 'Permite visualizar a lista de opções de satisfação',
            ],
            [
                'name' => 'Criar Satisfação',
                'slug' => 'satisfacao.create',
                'description' => 'Permite criar novas opções de satisfação',
            ],
            [
                'name' => 'Editar Satisfação',
                'slug' => 'satisfacao.edit',
                'description' => 'Permite editar opções de satisfação existentes',
            ],
            [
                'name' => 'Excluir Satisfação',
                'slug' => 'satisfacao.delete',
                'description' => 'Permite excluir opções de satisfação do sistema',
            ],
            [
                'name' => 'Visualizar Métricas',
                'slug' => 'metricas.view',
                'description' => 'Permite visualizar métricas e relatórios',
            ],
            // Métricas - Permissões Granulares (admin pode delegar cada seção)
            [
                'name' => 'Métricas - Visão Geral',
                'slug' => 'metricas.overview',
                'description' => 'Permite ver KPIs gerais (totais, taxa de satisfação)',
            ],
            [
                'name' => 'Métricas - Setores',
                'slug' => 'metricas.setores',
                'description' => 'Permite ver médias por setor e ranking',
            ],
            [
                'name' => 'Métricas - NPS',
                'slug' => 'metricas.nps',
                'description' => 'Permite ver o indicador de recomendação (NPS)',
            ],
            [
                'name' => 'Métricas - Dimensões',
                'slug' => 'metricas.dimensoes',
                'description' => 'Permite ver médias por dimensão/pergunta',
            ],
            [
                'name' => 'Métricas - Distribuições',
                'slug' => 'metricas.distribuicoes',
                'description' => 'Permite ver distribuições (tipo, idade, sexo, renda, convênio)',
            ],
            [
                'name' => 'Métricas - Série Temporal',
                'slug' => 'metricas.temporal',
                'description' => 'Permite ver evolução mensal',
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


