# 🎨 Design System — Atacadão Cotação Online

## 1. Visual Theme & Atmosphere

A atmosfera visual da aplicação reflete o "Pragmatismo Eficiente" do Atacadão. Diferente de sistemas puramente minimalistas, este design foca na **utilidade extrema e reconhecimento imediato**. A interface utiliza uma base sólida de verde institucional para transmitir confiança e estabilidade, enquanto o laranja vibrante é utilizado como um gatilho de ação (Call to Action), guiando o operador através do fluxo de faturamento.

A tipografia baseada na **Montserrat** confere uma legibilidade moderna a dados densos. Enquanto a interface administrativa é limpa e funcional, a área de faturamento foca no contraste de tabelas para evitar erros humanos. O sistema adota uma geometria de cantos suaves (8px) e sombras sutis para separar camadas de informação sem sobrecarregar a visão do usuário em turnos longos de trabalho.

**Key Characteristics:**

- **Tipografia:** Montserrat para leitura clara de códigos e valores.
- **Cromatismo:** Domínio de Verde Atacadão com acentos em Laranja para ações críticas.
- **Geometria:** Cards com bordas de 1px e sombras suaves (8px radius).
- **Feedback Visual:** Alertas baseados em bordas laterais coloridas para status (Sucesso, Info, Erro).
- **Foco Operacional:** Tabelas de alto contraste com suporte a leitor de código de barras (Libre Barcode 39).
- **Dualidade de Tema:** Preparado para Light Mode (fundo gelo) e Dark Mode (base verde floresta).

## 2. Color Palette & Roles (Semantic Tokens)

### Light Mode (Default)

- **Primary Green** (`--brand-primary`): `#27ae60` — Sucesso, branding e botões secundários.
- **Action Orange** (`--brand-secondary`): `#db7612` — Botões de faturamento e ações principais.
- **Page Background** (`--bg-page`): `#f0f4f8` — Fundo neutro para reduzir brilho.
- **Surface** (`--bg-surface`): `#ffffff` — Cards, tabelas e inputs.
- **Text Main** (`--text-main`): `#2c3e50` — Títulos e dados críticos.

### Dark Mode (Backlog)

- **Deep Green** (`--bg-page`): `#0a2616` — Fundo baseado no verde institucional escuro.
- **Surface Green** (`--bg-surface`): `#123a21` — Cards e áreas de conteúdo.
- **Text Light** (`--text-main`): `#f0f4f8` — Texto em off-white para conforto visual.

## 3. Typography Rules

### Font Family

- **Primary**: `Montserrat`, sans-serif.
- **Data/Barcode**: `Libre Barcode 39` (para colunas de scanner).

### Hierarchy

| Role | Size | Weight | Color Token | Notes |
|------|------|--------|-------------|-------|
| Hero Title | 2.5rem | 700 | `--text-main` | Títulos de seções administrativas |
| Section Heading | 1.8rem | 500 | `--text-main` | Títulos de cards de pedido |
| Table Header | 0.9rem | 700 | `--text-main` | Rótulos de colunas (Código, Preço) |
| Body Text | 1rem | 400 | `--text-main` | Descrições de produtos |
| Total Label | 1.5rem | 700 | `--brand-secondary` | Destaque para o valor total final |
| Technical Tags | 0.8rem | 500 | `--text-muted` | Metadados (Data/Hora, Sessão) |

## 4. Component Stylings

### Buttons

**Primary Action (Orange Gradient)**

- **Background**: `linear-gradient(90deg, #db7612, #c66a0f)`
- **Text**: `#ffffff` (Bold)
- **Use**: Finalizar Pedido, Gerar PDF.

**Secondary Action (Green Outline)**

- **Border**: `1px solid #27ae60`
- **Text**: `#27ae60`
- **Use**: Adicionar Item, Limpar Filtros.

### Cards & Containers

- **Background**: `--bg-surface`
- **Border**: `1px solid rgba(0, 0, 0, 0.05)`
- **Shadow**: `0 6px 18px rgba(0, 0, 0, 0.08)`
- **Radius**: `8px`

### Status Alerts (Feedback)

- **Success**: Borda esquerda `#198754`.
- **Info**: Borda esquerda `#0dcaf0`.
- **Danger**: Borda esquerda `#dc3545`.

## 5. Layout Principles

### Spacing System

- **Base Unit**: 8px.
- **Container Max-Width**: 1200px (Desktop).
- **Padding Interno**: 1.5rem a 2rem para cards de formulário.

### Grid & Layout

- **Admin Side**: Layout de duas colunas (Branding lateral + Formulário) em telas >768px.
- **Operator Side**: Layout focado em tabela "full-width" para maximizar a visão dos itens.

## 6. Depth & Elevation

| Level | Treatment | Use |
|-------|-----------|-----|
| Level 0 | `--bg-page` | Fundo da aplicação. |
| Level 1 | `--bg-surface` + Shadow | Cards de faturamento e tabelas. |
| Level 2 | Shadow Intensa | Modais de confirmação de exclusão. |

## 7. Do's and Don'ts

### Do

- Use o Laranja (`--brand-secondary`) apenas para o botão que finaliza o processo.
- Mantenha a fonte Montserrat em pesos 400 e 700 para clareza.
- Utilize o `sessionStorage` para garantir que o fechamento da aba destrua a sessão de pedido.
- Aplique a animação `spin` em botões durante o processamento do CSV.

### Don't

- Não use cores fora da paleta `--brand` para elementos estruturais.
- Não remova o feedback de borda lateral em mensagens de erro.
- Não utilize bordas arredondadas >8px em tabelas de dados.
- Não exiba a seção de branding em dispositivos móveis (<768px).

## 8. Responsive Behavior

| Breakpoint | Width | Behavior |
|------------|-------|----------|
| Mobile | <768px | Branding oculto, formulários ocupam 100% da largura. |
| Tablet/Small Desktop | 768px - 1024px | Layout de 2 colunas preservado. |
| Large Desktop | >1024px | Conteúdo centralizado com margens amplas para foco. |
