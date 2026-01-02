<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HSTS Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP Strict Transport Security (HSTS) configuração. HSTS força o
    | navegador a usar apenas HTTPS para se conectar ao servidor.
    |
    */

    'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 ano em segundos

    'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),

    /*
    |--------------------------------------------------------------------------
    | X-Frame-Options
    |--------------------------------------------------------------------------
    |
    | Proteção contra clickjacking. Valores possíveis:
    | - DENY: Não permite o site ser carregado em um frame
    | - SAMEORIGIN: Permite apenas frames do mesmo domínio
    |
    */

    'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),

    /*
    |--------------------------------------------------------------------------
    | Referrer Policy
    |--------------------------------------------------------------------------
    |
    | Controla quanto de informação de referrer é enviada com requisições.
    | Valores: no-referrer, no-referrer-when-downgrade, origin, origin-when-cross-origin,
    | same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
    |
    */

    'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),

    /*
    |--------------------------------------------------------------------------
    | Permissions Policy
    |--------------------------------------------------------------------------
    |
    | Controla quais APIs e features do navegador podem ser usadas.
    | Exemplo: geolocation=(), microphone=(), camera=()
    |
    */

    'permissions_policy' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Content Security Policy para prevenir XSS e outros ataques.
    | Pode ser uma string com a política completa ou false para desabilitar.
    | Se null, será usado um CSP padrão.
    |
    */

    'csp' => env('CSP', null), // null = usar padrão, false = desabilitar, string = usar customizado

    /*
    |--------------------------------------------------------------------------
    | Critical Permissions
    |--------------------------------------------------------------------------
    |
    | Lista de permissões consideradas críticas para monitoramento.
    | Mudanças nessas permissões gerarão alertas de segurança.
    |
    */

    'critical_permissions' => [
        'admin.*',
        'users.*',
        'roles.*',
        'permissions.*',
        'security.*',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Approval
    |--------------------------------------------------------------------------
    |
    | Se true, novos usuários precisam ser aprovados por um administrador
    | antes de poderem fazer login.
    |
    */

    'user_approval_required' => env('USER_APPROVAL_REQUIRED', true),

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Configurações de política de senha.
    |
    */

    'password_expires_in_days' => env('PASSWORD_EXPIRES_IN_DAYS', null), // null = sem expiração
    'password_history_limit' => env('PASSWORD_HISTORY_LIMIT', 10), // Quantas senhas anteriores manter

    /*
    |--------------------------------------------------------------------------
    | Inactive Users
    |--------------------------------------------------------------------------
    |
    | Configurações para desativação automática de usuários inativos.
    |
    */

    'auto_deactivate_inactive_users' => env('AUTO_DEACTIVATE_INACTIVE_USERS', true),
    'inactive_days_threshold' => env('INACTIVE_DAYS_THRESHOLD', 90),

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para backup seguro de dados.
    |
    */

    'backup_retention_days' => env('BACKUP_RETENTION_DAYS', 30),
    'backup_encryption_enabled' => env('BACKUP_ENCRYPTION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Key Rotation
    |--------------------------------------------------------------------------
    |
    | Configurações para rotação de chaves e tokens.
    |
    */

    'key_rotation_days' => env('KEY_ROTATION_DAYS', 90), // Rotacionar chaves a cada 90 dias

];

