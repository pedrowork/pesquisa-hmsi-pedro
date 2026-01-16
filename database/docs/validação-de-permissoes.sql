SELECT
    p.slug AS permissao,
    MAX(CASE WHEN r.slug = 'admin' THEN TRUE ELSE FALSE END) AS admin,
    MAX(CASE WHEN r.slug = 'master' THEN TRUE ELSE FALSE END) AS master,
    MAX(CASE WHEN r.slug = 'colaborador' THEN TRUE ELSE FALSE END) AS colaborador
FROM permissions p
LEFT JOIN role_permissions rp ON p.id = rp.permission_id
LEFT JOIN roles r ON r.id = rp.role_id
GROUP BY p.slug
ORDER BY p.slug DESC;
