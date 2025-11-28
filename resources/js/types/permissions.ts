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
    // Pesquisa - Módulos
    | 'leitos.manage'
    | 'setores.manage'
    | 'tipos-convenio.manage'
    | 'setores-pesquisa.manage'
    | 'perguntas.manage'
    | 'satisfacao.manage'
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


