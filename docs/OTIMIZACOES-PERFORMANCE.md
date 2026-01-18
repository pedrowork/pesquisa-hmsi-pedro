# Otimizações de Performance - Pesquisa HMSI

**Data:** 2026-01-17  
**Status:** Análise e Recomendações

## 📊 Problemas Identificados

### 1. Dashboard - Múltiplas Queries Pesadas (6s+ no /setores)
- Múltiplas queries COUNT() complexas executadas a cada request
- Sem cache de resultados
- Queries com JOINs e DISTINCT que podem ser otimizadas

### 2. Queries N+1 Potenciais
- `getUserPermissions()` executado em cada request via middleware
- `getAdminUserId()` e `getFirstMasterUserId()` sem cache
- Roles carregados para cada usuário na listagem

### 3. Frontend - INP Ruim (1,544ms)
- Interações lentas (cliques, inputs)
- Possível falta de code splitting
- Event handlers não otimizados

---

## ✅ OTIMIZAÇÕES PRIORITÁRIAS (Alto Impacto, Baixa Complexidade)

### 🔴 CRÍTICO - Implementar Imediatamente

#### 1. Cache de Queries do Dashboard
**Problema:** Dashboard executa ~10-15 queries pesadas a cada request (6s+)

**Solução:**
```php
// Cache de 5 minutos para estatísticas do dashboard
$stats = Cache::remember('dashboard.stats', 300, function() {
    return [
        'totalUsers' => User::count(),
        'activeUsers' => User::where('status', 1)->count(),
        // ... outras stats
    ];
});
```

**Impacto:** Reduz tempo de resposta de ~2-6s para ~50-200ms  
**Tempo de implementação:** 30 minutos

#### 2. Cache de IDs de Admin e Master
**Problema:** `getAdminUserId()` e `getFirstMasterUserId()` executados múltiplas vezes

**Solução:**
```php
// Cache permanente (limpar apenas quando roles mudarem)
$adminId = Cache::rememberForever('system.admin_user_id', function() {
    return DB::table('user_roles')
        ->join('roles', 'user_roles.role_id', '=', 'roles.id')
        ->where('roles.slug', 'admin')
        ->value('user_roles.user_id');
});
```

**Impacto:** Reduz queries repetidas em múltiplas páginas  
**Tempo de implementação:** 20 minutos

#### 3. Eager Loading de Roles em UserController
**Problema:** Roles carregados individualmente para cada usuário (N+1)

**Solução:**
```php
// Em UserController::index()
$users = User::with('roles')->paginate(10);
```

**Impacto:** Reduz N queries para 1 query  
**Tempo de implementação:** 15 minutos

---

### 🟡 IMPORTANTE - Implementar em Breve

#### 4. Índices de Banco de Dados
**Problema:** Queries com LIKE e WHERE podem ser lentas sem índices

**Solução:**
```php
// Criar migration
Schema::table('users', function (Blueprint $table) {
    $table->index('status');
    $table->index('email');
    $table->index(['status', 'approval_status']);
});

Schema::table('questionario', function (Blueprint $table) {
    $table->index('data_questionario');
    $table->index('cod_paciente');
});
```

**Impacto:** Melhora queries de busca e filtros  
**Tempo de implementação:** 30 minutos

#### 5. Query Builder Otimizado (Dashboard)
**Problema:** Múltiplas queries COUNT() separadas

**Solução:**
```php
// Combinar queries similares usando CASE WHEN
$stats = DB::table('questionario')
    ->selectRaw('
        COUNT(DISTINCT CASE WHEN DATE(data_questionario) = CURDATE() THEN cod_paciente END) as hoje,
        COUNT(DISTINCT CASE WHEN data_questionario >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN cod_paciente END) as semana
    ')
    ->first();
```

**Impacto:** Reduz 6 queries para 1 query  
**Tempo de implementação:** 1 hora

#### 6. Otimizar getUserPermissions (Cache)
**Problema:** Permissões recalculadas a cada request

**Solução:** Já existe cache no `HasPermissions` trait, mas verificar TTL

**Impacto:** Melhora todas as páginas autenticadas  
**Tempo de implementação:** 20 minutos

---

### 🟢 RECOMENDADO - Melhorias Futuras

#### 7. Code Splitting no Frontend
**Problema:** Bundle JavaScript grande carregado de uma vez

**Solução:**
```typescript
// Lazy loading de rotas
const Dashboard = lazy(() => import('./pages/dashboard'));
const Users = lazy(() => import('./pages/users'));
```

**Impacto:** Reduz tempo inicial de carregamento  
**Tempo de implementação:** 2-3 horas

#### 8. Debounce em Inputs de Busca
**Problema:** Busca executa a cada tecla pressionada

**Solução:**
```typescript
const debouncedSearch = useDebounce(search, 300);
useEffect(() => {
    router.get('/users', { search: debouncedSearch });
}, [debouncedSearch]);
```

**Impacto:** Reduz requisições desnecessárias  
**Tempo de implementação:** 1 hora

#### 9. Paginação Otimizada
**Problema:** Listagens carregam todos os registros

**Solução:** Já existe paginação, mas melhorar com cursor-based pagination para grandes datasets

**Impacto:** Melhora performance em listagens grandes  
**Tempo de implementação:** 3-4 horas

---

## 📈 Estimativa de Impacto

| Otimização | Tempo | Redução Esperada | Prioridade |
|------------|-------|------------------|------------|
| 1. Cache Dashboard | 30min | 90% (6s → 200ms) | 🔴 Alta |
| 2. Cache Admin/Master IDs | 20min | 50% queries | 🔴 Alta |
| 3. Eager Loading Roles | 15min | 80% (N queries → 1) | 🔴 Alta |
| 4. Índices DB | 30min | 30-50% queries lentas | 🟡 Média |
| 5. Query Builder Otimizado | 1h | 60% queries dashboard | 🟡 Média |
| 6. Cache Permissões | 20min | 20% todos requests | 🟡 Média |

**Tempo Total para Otimizações Críticas:** ~1h 25min  
**Ganho Esperado:** 70-90% de melhoria no tempo de resposta

---

## 🚀 Implementação Rápida (Top 3)

### 1. Cache Dashboard (Maior Impacto)
Adicionar cache de 5 minutos para todas as estatísticas do dashboard

### 2. Eager Loading Roles
Modificar `UserController::index()` para usar `with('roles')`

### 3. Cache IDs Admin/Master
Cache permanente para `getAdminUserId()` e `getFirstMasterUserId()`

---

## 📝 Próximos Passos

1. ✅ **Imediato:** Implementar Top 3 otimizações (1h 25min)
2. ⚠️ **Esta semana:** Adicionar índices e otimizar queries (2h)
3. 📅 **Futuro:** Code splitting e debounce frontend (4-5h)
