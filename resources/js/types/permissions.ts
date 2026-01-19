/**
 * Tipos de permissões do sistema
 */

export type PermissionName =
    // Dashboard
    | 'dashboard.view'
    | 'dashboard.stats.management'
    | 'dashboard.quick-actions'
    | 'dashboard.management-links'
    | 'dashboard.research.metrics'
    | 'dashboard.research.secondary'
    | 'dashboard.research.analysis'
    // Gerenciamento - Usuários
    | 'users.view'
    | 'users.create'
    | 'users.edit'
    | 'users.delete'
    | 'users.approve'
    // Gerenciamento - Roles
    | 'roles.view'
    | 'roles.create'
    | 'roles.edit'
    | 'roles.delete'
    // Gerenciamento - Permissões
    | 'permissions.view'
    | 'permissions.create'
    | 'permissions.edit'
    | 'permissions.delete'
    // Pesquisa - Questionários
    | 'questionarios.view'
    | 'questionarios.create'
    | 'questionarios.show'
    // Pesquisa - Leitos
    | 'leitos.view'
    | 'leitos.create'
    | 'leitos.edit'
    | 'leitos.delete'
    | 'leitos.manage'
    // Pesquisa - Setores
    | 'setores.view'
    | 'setores.create'
    | 'setores.edit'
    | 'setores.delete'
    | 'setores.manage'
    // Pesquisa - Tipos de Convênio
    | 'tipos-convenio.view'
    | 'tipos-convenio.create'
    | 'tipos-convenio.edit'
    | 'tipos-convenio.delete'
    | 'tipos-convenio.manage'
    // Pesquisa - Setores de Pesquisa
    | 'setores-pesquisa.view'
    | 'setores-pesquisa.create'
    | 'setores-pesquisa.edit'
    | 'setores-pesquisa.delete'
    | 'setores-pesquisa.manage'
    // Pesquisa - Perguntas
    | 'perguntas.view'
    | 'perguntas.create'
    | 'perguntas.edit'
    | 'perguntas.delete'
    | 'perguntas.manage'
    // Pesquisa - Satisfação
    | 'satisfacao.view'
    | 'satisfacao.create'
    | 'satisfacao.edit'
    | 'satisfacao.delete'
    | 'satisfacao.manage'
    // Métricas
    | 'metricas.view'
    | 'metricas.overview'
    | 'metricas.setores'
    | 'metricas.nps'
    | 'metricas.dimensoes'
    | 'metricas.distribuicoes'
    | 'metricas.temporal';

export interface Permission {
    id: number;
    name: string;
    slug: PermissionName;
    description: string | null;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    permissions?: PermissionName[];
    isAdmin?: boolean;
}

export interface SharedAuthData {
    user: AuthUser | null;
    permissions: PermissionName[];
    isAdmin: boolean;
}
