# Mapeamento de Permissões - Métricas

Este documento mapeia todas as permissões relacionadas à página de métricas, permitindo que o administrador controle granularmente o que cada usuário/role pode visualizar.

## Tabela Resumo de Permissões

| Slug | Nome | Descrição | Elementos Visíveis |
|------|------|-----------|-------------------|
| `metricas.view` | Visualizar Métricas | Permissão base para acessar a página | Página `/metricas` |
| `metricas.overview` | Métricas - Visão Geral | KPIs gerais (totais, taxa de satisfação) | 3 cards: Total Questionários, Total Respostas, Taxa Satisfação |
| `metricas.nps` | Métricas - NPS | Indicador de recomendação (NPS) | Card NPS, Média (0-10), Detalhes (Promotores/Neutros/Detratores), Gráfico NPS por Setor |
| `metricas.setores` | Métricas - Setores | Médias por setor e ranking | 2 cards: Média por Setor (gráfico), Ranking por Volume (Top 10) |
| `metricas.dimensoes` | Métricas - Dimensões | Médias por dimensão/pergunta | 1 card: Média por Dimensão (gráfico de barras) |
| `metricas.distribuicoes` | Métricas - Distribuições | Distribuições demográficas | 5 cards: Tipo Paciente, Sexo, Renda, Faixa Etária, Convênio |
| `metricas.temporal` | Métricas - Série Temporal | Evolução mensal | 1 card: Avaliação Mensal (gráfico de linha) |

## Estrutura de Permissões

### Permissão Base
- **`metricas.view`**: Permissão base para acessar a página de métricas
  - **Descrição**: Permite visualizar métricas e relatórios
  - **Obrigatória**: Sim (sem ela, o usuário não pode acessar `/metricas`)

### Permissões Granulares por Seção

#### 1. Visão Geral (`metricas.overview`)
- **Slug**: `metricas.overview`
- **Descrição**: Permite ver KPIs gerais (totais, taxa de satisfação)
- **Elementos Controlados**:
  - Card: **Total de Questionários** (contagem de pacientes distintos)
  - Card: **Total de Respostas** (contagem de respostas coletadas)
  - Card: **Taxa de Satisfação (0–10)** (média das notas tipo 3)

#### 2. NPS (`metricas.nps`)
- **Slug**: `metricas.nps`
- **Descrição**: Permite ver o indicador de recomendação (NPS)
- **Elementos Controlados**:
  - Card principal: **Score NPS** (cálculo: 100*(Promotores/Total) − 100*(Detratores/Total))
  - Card: **Média (0–10)** da pergunta de NPS
  - Seção expandível: **Ver Métricas Detalhadas** contendo:
    - Estatísticas: Total, Promotores (%), Neutros, Detratores (%)
    - Gráfico: **NPS por Setor** (bar chart)

#### 3. Setores (`metricas.setores`)
- **Slug**: `metricas.setores`
- **Descrição**: Permite ver médias por setor e ranking
- **Elementos Controlados**:
  - Card: **Média por Setor (0–10)** (gráfico de barras)
  - Card: **Ranking por Volume (Top 10)** (gráfico de barras com total de respostas)

#### 4. Dimensões (`metricas.dimensoes`)
- **Slug**: `metricas.dimensoes`
- **Descrição**: Permite ver médias por dimensão/pergunta
- **Elementos Controlados**:
  - Card: **Média por Dimensão (pergunta tipo 3)** (gráfico de barras horizontal)
  - Mostra a média de cada pergunta do tipo 3 (escala 0–10)

#### 5. Distribuições (`metricas.distribuicoes`)
- **Slug**: `metricas.distribuicoes`
- **Descrição**: Permite ver distribuições (tipo, idade, sexo, renda, convênio)
- **Elementos Controlados**:
  - Card: **Tipo de Paciente** (barras horizontais)
  - Card: **Sexo** (barras horizontais)
  - Card: **Renda** (barras horizontais)
  - Card: **Faixa Etária** (barras horizontais)
  - Card: **Convênio** (barras horizontais)

#### 6. Série Temporal (`metricas.temporal`)
- **Slug**: `metricas.temporal`
- **Descrição**: Permite ver evolução mensal
- **Elementos Controlados**:
  - Card: **Avaliação Mensal (média 0–10)** (gráfico de linha temporal)
  - Mostra a evolução da satisfação média ao longo dos meses

## Hierarquia de Permissões

```
metricas.view (obrigatória)
├── metricas.overview
├── metricas.nps
├── metricas.setores
├── metricas.dimensoes
├── metricas.distribuicoes
└── metricas.temporal
```

**Nota**: Todas as permissões granulares dependem de `metricas.view`. Se o usuário não tiver `metricas.view`, não poderá acessar a página, mesmo que tenha outras permissões.

## Implementação no Código

### Frontend (`resources/js/pages/metricas/index.tsx`)
Cada seção é envolvida pelo componente `<Can permission="...">`:

```tsx
<Can permission="metricas.overview">
  {/* Cards de visão geral */}
</Can>

<Can permission="metricas.nps">
  {/* Card NPS e detalhes */}
</Can>

<Can permission="metricas.setores">
  {/* Gráficos de setores */}
</Can>

<Can permission="metricas.dimensoes">
  {/* Gráfico de dimensões */}
</Can>

<Can permission="metricas.distribuicoes">
  {/* Cards de distribuições */}
</Can>

<Can permission="metricas.temporal">
  {/* Gráfico de série temporal */}
</Can>
```

### Backend (`app/Http/Controllers/MetricaController.php`)
O controller verifica permissões antes de calcular e retornar dados:

- Se o usuário não tiver `metricas.overview`, `overview` não é calculado
- Se o usuário não tiver `metricas.nps`, `nps`, `npsMean` e `npsDetail` não são calculados
- E assim por diante para cada seção

### Rotas (`routes/web.php`)
A rota `/metricas` deve verificar `metricas.view`:

```php
Route::get('/metricas', [MetricaController::class, 'index'])
    ->middleware(['auth', 'permission:metricas.view'])
    ->name('metricas.index');
```

## Exemplos de Configuração de Roles

### Role: "Master" (Administrador Completo)
Todas as permissões:
- `metricas.view`
- `metricas.overview`
- `metricas.nps`
- `metricas.setores`
- `metricas.dimensoes`
- `metricas.distribuicoes`
- `metricas.temporal`

### Role: "Colaborador" (Visualização Limitada)
Apenas visão geral:
- `metricas.view`
- `metricas.overview`

### Role: "Analista de Pesquisa" (Análises Completas)
Todas as métricas, exceto distribuições:
- `metricas.view`
- `metricas.overview`
- `metricas.nps`
- `metricas.setores`
- `metricas.dimensoes`
- `metricas.temporal`

### Role: "Gerente de Setor" (Foco em Setores)
Visão geral e setores:
- `metricas.view`
- `metricas.overview`
- `metricas.setores`

## Como Gerenciar Permissões

1. **Acesse o módulo de Gerenciamento de Roles/Permissões** (se existir)
2. **Selecione o Role ou Usuário** que deseja configurar
3. **Marque/desmarque as permissões** conforme necessário:
   - `metricas.view` (obrigatória para acessar a página)
   - `metricas.overview` (visão geral)
   - `metricas.nps` (NPS e detalhes)
   - `metricas.setores` (médias e ranking por setor)
   - `metricas.dimensoes` (médias por pergunta)
   - `metricas.distribuicoes` (distribuições demográficas)
   - `metricas.temporal` (evolução temporal)
4. **Salve as alterações**

## Observações Importantes

1. **Performance**: O backend só calcula métricas se o usuário tiver permissão, economizando processamento
2. **Segurança**: Mesmo que alguém tente acessar dados via API, o middleware `CheckPermission` bloqueia requisições não autorizadas
3. **UX**: Seções sem permissão simplesmente não aparecem na interface, mantendo a experiência limpa
4. **Granularidade**: Cada seção pode ser controlada independentemente, permitindo configurações flexíveis

