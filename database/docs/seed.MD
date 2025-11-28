# Dados de Seed - Estrutura de Planilhas

Este documento contém a estrutura de dados para popular as tabelas do sistema através de seeders.

---

## 📊 TABELA: SATISFAÇÃO (`satisfacao`)

### Estrutura da Tabela
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cod` | ID (PK) | Código único da satisfação |
| `descricao` | String(255) | Descrição da opção de satisfação |
| `cod_tipo_pergunta` | BigInt (FK) | Código do tipo de pergunta relacionado |

### Dados para Seed

| cod | descricao | cod_tipo_pergunta | Observações |
|-----|-----------|-------------------|-------------|
| 0 | NA | 1 | Não aplicável - Tipo 1 |
| 1 | RUIM | 1 | Tipo 1 |
| 2 | REGULAR | 1 | Tipo 1 |
| 3 | BOM | 1 | Tipo 1 |
| 4 | ÓTIMO | 1 | Tipo 1 |
| 5 | SIM | 2 | Tipo 2 |
| 6 | NÃO | 2 | Tipo 2 |
| 7 | NA | 3 | Não aplicável - Tipo 3 |
| 9 | Observação | 4 | Tipo 4 |
| 10 | 0 | 3 | Escala numérica - Tipo 3 |
| 11 | 1 | 3 | Escala numérica - Tipo 3 |
| 12 | 2 | 3 | Escala numérica - Tipo 3 |
| 13 | 3 | 3 | Escala numérica - Tipo 3 |
| 14 | 4 | 3 | Escala numérica - Tipo 3 |
| 15 | 5 | 3 | Escala numérica - Tipo 3 |
| 16 | 6 | 3 | Escala numérica - Tipo 3 |
| 17 | 7 | 3 | Escala numérica - Tipo 3 |
| 18 | 8 | 3 | Escala numérica - Tipo 3 |
| 19 | 9 | 3 | Escala numérica - Tipo 3 |
| 20 | 10 | 3 | Escala numérica - Tipo 3 |

### Tipos de Pergunta Relacionados
- **Tipo 1**: Escala qualitativa (NA, RUIM, REGULAR, BOM, ÓTIMO)
- **Tipo 2**: Resposta binária (SIM, NÃO)
- **Tipo 3**: Escala numérica (0 a 10)
- **Tipo 4**: Campo de observação

---

## 📊 TABELA: SETOR DE PESQUISA (`setor_pesquis`)

### Estrutura da Tabela
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cod` | ID (PK) | Código único do setor de pesquisa |
| `descricao` | String(128) | Descrição do setor de pesquisa |

### Dados para Seed

| cod | descricao |
|-----|-----------|
| 1 | Higienização e Limpeza |
| 2 | Enfermagem |
| 3 | Medicina |
| 4 | Serviço Social |
| 5 | Nutrição e Dietética |
| 6 | Manutenção |
| 7 | Geral |

---

## 📊 TABELA: TIPO DE CONVÊNIO (`tipoconvenio`)

### Estrutura da Tabela
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cod` | ID (PK) | Código único do tipo de convênio |
| `tipo_descricao` | String(100) | Descrição do tipo de convênio |

### Dados para Seed

| cod | tipo_descricao |
|-----|----------------|
| 1 | SUS |
| 2 | IPES |
| 3 | PARTICULAR |
| 4 | DIAVERUM |
| 5 | OUTROS |

---

## 📊 TABELA: SETOR (`setor`)

### Estrutura da Tabela
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cod` | ID (PK) | Código único do setor |
| `descricao` | String(255) | Descrição do setor |

### Dados para Seed

| cod | descricao |
|-----|-----------|
| 1 | Flor de Lis |
| 2 | Flor de Cerejeira |
| 3 | Lírios |
| 4 | Flor de Laranjeira |
| 5 | Utin Amor Perfeito |
| 6 | UCinco |
| 7 | Ala Girassol |
| 8 | Alfazema |
| 9 | Jasmin |
| 10 | Recepção Principal |
| 11 | Recepção Pediatria |
| 12 | Recepção Suplementar |
| 13 | Recepção Maternidade |
| 14 | Violeta |
| 15 | Rosa |
| 16 | Cravo |
| 17 | Margarida |
| 18 | Lótus |
| 19 | UTI Hortênsia |
| 20 | UTI Orquídea |
| 21 | Lavanda |
| 22 | Centro Cirúrgico |
| 23 | Ortopedia |

---

## 📊 TABELA: LEITO (`leito`)

### Estrutura da Tabela
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cod` | ID (PK) | Código único do leito |
| `descricao` | String(255) | Descrição do leito |
| `cod_setor` | BigInt (FK) | Código do setor relacionado (nullable) |

### Dados para Seed

> **Nota**: Os leitos estão organizados por setor. O campo `cod_setor` referencia a tabela `setor`.

| cod | descricao | cod_setor | Setor Relacionado |
|-----|-----------|-----------|-------------------|
| 1-20 | LEITO 01 a LEITO 20 | 5 | Utin Amor Perfeito |
| 21-50 | LEITO 01 a LEITO 30 | 7 | Ala Girassol |
| 51-75 | LEITO 01 a LEITO 25 | 1 | Flor de Lis |
| 76-103 | LEITO 01 a LEITO 28 | 2 | Flor de Cerejeira |
| 104-119 | LEITO 01 a LEITO 16 | 3 | Lírios |
| 120-150 | LEITO 01 a LEITO 31 | 4 | Flor de Laranjeira |
| 151-180 | LEITO 01 a LEITO 30 | 6 | UCinco |
| 181-184 | LEITO 01 a LEITO 04 | 8 | Alfazema |
| 185-194 | LEITO 01 a LEITO 10 | 9 | Jasmin |
| 195-219 | LEITO 01 a LEITO 25 | 14 | Violeta |
| 220-229 | LEITO 01 a LEITO 10 | 15 | Rosa |
| 230-232 | LEITO 01 a LEITO 03 | 16 | Cravo |
| 233-258 | LEITO 01 a LEITO 26 | 17 | Margarida |
| 259-278 | LEITO 01 a LEITO 20 | 18 | Lótus |
| 279-288 | LEITO 01 a LEITO 10 | 19 | UTI Hortênsia |
| 289-299 | LEITO 01 a LEITO 11 | 20 | UTI Orquídea |
| 300-319 | LEITO 01 a LEITO 20 | 21 | Lavanda |
| 320-339 | NA | 1-22, 11-12 | Leitos não aplicáveis por setor |
| 340-356 | LEITO 01 a LEITO 17 | 23 | Ortopedia |
| 357 | NA | 23 | Não aplicável - Ortopedia |
| 358-363 | LEITO 17 a LEITO 22 | 3 | Lírios (continuação) |

### Resumo por Setor

| Setor | Quantidade de Leitos | Códigos |
|-------|---------------------|---------|
| Utin Amor Perfeito (5) | 20 | 1-20 |
| Ala Girassol (7) | 30 | 21-50 |
| Flor de Lis (1) | 25 | 51-75 |
| Flor de Cerejeira (2) | 28 | 76-103 |
| Lírios (3) | 22 | 104-119, 358-363 |
| Flor de Laranjeira (4) | 31 | 120-150 |
| UCinco (6) | 30 | 151-180 |
| Alfazema (8) | 4 | 181-184 |
| Jasmin (9) | 10 | 185-194 |
| Violeta (14) | 25 | 195-219 |
| Rosa (15) | 10 | 220-229 |
| Cravo (16) | 3 | 230-232 |
| Margarida (17) | 26 | 233-258 |
| Lótus (18) | 20 | 259-278 |
| UTI Hortênsia (19) | 10 | 279-288 |
| UTI Orquídea (20) | 11 | 289-299 |
| Lavanda (21) | 20 | 300-319 |
| Ortopedia (23) | 17 | 340-356 |
| NA (vários setores) | 20 | 320-339, 357 |

**Total de Leitos**: ~363 registros (incluindo "NA")

---

## 📊 TABELA: PERGUNTAS DESCRIÇÃO (`perguntas_descricao`)

### Estrutura da Tabela
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `cod` | ID (PK) | Código único da pergunta |
| `descricao` | String(255) | Texto da pergunta |
| `cod_setor_pesquis` | BigInt (FK) | Código do setor de pesquisa relacionado (nullable) |
| `cod_tipo_pergunta` | BigInt (FK) | Código do tipo de pergunta relacionado (nullable) |

### Dados para Seed

| cod | descricao | cod_setor_pesquis | cod_tipo_pergunta | Setor de Pesquisa | Tipo |
|-----|-----------|-------------------|-------------------|-------------------|------|
| 1 | Como classificaria o serviço da equipe de enfermagem? | 2 | 3 | Enfermagem | Escala 0-10 |
| 2 | Como classificaria o atendimento e serviço prestado pelos médicos? | 3 | 3 | Medicina | Escala 0-10 |
| 3 | Como classificaria o atendimento e clareza das informações referente a recepção por onde entrou? | 7 | 3 | Geral | Escala 0-10 |
| 4 | Se teve atendimento, como classificaria o serviço da equipe de fisioterapia? | 2 | 3 | Enfermagem | Escala 0-10 |
| 5 | Como avalia a qualidade das refeições oferecidas no hospital? | 5 | 3 | Nutrição e Dietética | Escala 0-10 |
| 6 | Se obteve atendimento, como você avalia a eficiência da equipe de serviço social em compreender e atender às suas necessidades durante sua estadia no hospital? | 4 | 3 | Serviço Social | Escala 0-10 |
| 7 | Como avalia o ambiente físico do quarto e as condições de conforto durante sua estadia? | 7 | 3 | Geral | Escala 0-10 |
| 8 | Como classificaria a higienização do setor? | 1 | 3 | Higienização e Limpeza | Escala 0-10 |
| 9 | Como você avalia a qualidade do atendimento de forma geral? | 7 | 3 | Geral | Escala 0-10 |
| 10 | Em uma escala de 0 a 10, quanto você recomendaria o Hospital e Maternidade Santa Isabel para um familiar ou amigo? | 7 | 3 | Geral | Escala 0-10 |
| 11 | Voçê se sentiu cuidado de forma segura e especial? | 7 | 2 | Geral | Sim/Não |
| 12 | Teria alguma observação a fazer? | 7 | 4 | Geral | Observação |

### Relacionamentos

- **Setores de Pesquisa**: Todas as perguntas estão vinculadas ao setor "Geral" (7), exceto perguntas específicas de setores
- **Tipos de Pergunta**:
  - Tipo 2: Pergunta binária (Sim/Não)
  - Tipo 3: Escala numérica (0 a 10)
  - Tipo 4: Campo de observação

---

## 📝 Notas

- **Formato**: Os dados estão organizados em formato de tabela Markdown para fácil leitura e manutenção
- **IDs**: Os códigos (IDs) devem ser únicos e sequenciais quando aplicável
- **Foreign Keys**: Campos com `cod_*` são chaves estrangeiras que referenciam outras tabelas
- **Nullable**: Campos marcados como `nullable` podem ser nulos
- **Leitos**: A tabela de leitos contém muitos registros. Use o resumo por setor para referência rápida

---

## 🔄 Como Usar

1. Copie os dados da tabela correspondente
2. Crie ou atualize o seeder correspondente
3. Use `DB::table('nome_tabela')->insert([...])` ou `Model::create([...])`
4. Execute: `php artisan db:seed --class=NomeSeeder`

### Exemplo de Seeder

```php
// Exemplo para SETOR_PESQUIS
DB::table('setor_pesquis')->insert([
    ['cod' => 1, 'descricao' => 'Higienização e Limpeza'],
    ['cod' => 2, 'descricao' => 'Enfermagem'],
    // ... mais registros
]);
```

---

## 📋 Checklist de Tabelas

- [x] `satisfacao` - Dados de satisfação (21 registros)
- [x] `setor_pesquis` - Setores de pesquisa (7 registros)
- [x] `tipoconvenio` - Tipos de convênio (5 registros)
- [x] `setor` - Setores do hospital (23 registros)
- [x] `leito` - Leitos por setor (~363 registros)
- [x] `perguntas_descricao` - Perguntas do questionário (12 registros)
- [ ] Outras tabelas...

---

## 📊 Estatísticas

- **Total de Tabelas Documentadas**: 6
- **Total de Registros**: ~431 registros
- **Tabelas com Foreign Keys**: 3 (leito, perguntas_descricao, satisfacao)
