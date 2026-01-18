-- Verificar se a permissão perguntas.order existe na tabela permissions
SELECT id, name, slug, description 
FROM permissions 
WHERE slug = 'perguntas.order';

-- Verificar se o role Admin tem essa permissão (deve ter TODAS as permissões)
SELECT 
    r.name as role_name,
    r.slug as role_slug,
    p.name as permission_name,
    p.slug as permission_slug
FROM role_permissions rp
JOIN roles r ON rp.role_id = r.id
JOIN permissions p ON rp.permission_id = p.id
WHERE p.slug = 'perguntas.order'
AND r.slug = 'admin';

-- Verificar se o role Master tem essa permissão
SELECT 
    r.name as role_name,
    r.slug as role_slug,
    p.name as permission_name,
    p.slug as permission_slug
FROM role_permissions rp
JOIN roles r ON rp.role_id = r.id
JOIN permissions p ON rp.permission_id = p.id
WHERE p.slug = 'perguntas.order'
AND r.slug = 'master';

-- Verificar quais usuários têm a permissão perguntas.order (via roles)
SELECT DISTINCT
    u.id,
    u.name,
    u.email,
    r.name as role_name,
    r.slug as role_slug,
    p.slug as permission_slug
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE p.slug = 'perguntas.order';

-- Contar quantas permissões cada role tem (para validar que admin tem todas)
SELECT 
    r.name as role_name,
    COUNT(DISTINCT rp.permission_id) as total_permissions,
    (SELECT COUNT(*) FROM permissions) as total_permissions_system
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
WHERE r.slug IN ('admin', 'master')
GROUP BY r.id, r.name;
