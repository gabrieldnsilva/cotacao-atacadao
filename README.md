# 🛒 Cotação Online Atacadão

> Sistema web pragmático para substituição de fluxos manuais em planilhas, focado em faturamento e cotação de produtos via CSV legado.

![Status: Sprint 1 Completa](https://img.shields.io/badge/Status-Sprint%201%20Completa-success)
![Stack: PHP 8.1 + Vanilla JS](https://img.shields.io/badge/Stack-PHP%208.1%20%2B%20Vanilla%20JS-blue)

## 📋 Visão Geral

O projeto **Cotação Online** foi desenvolvido para automatizar o processo de faturamento interno da filial 945 do Atacadão. Ele elimina a dependência de planilhas Google complexas, centralizando o catálogo de produtos em um banco de dados MariaDB e permitindo que múltiplos operadores criem cotações assíncronas e gerem PDFs com códigos de barras compatíveis com scanners físicos.

## ✨ Funcionalidades

### 🔐 Administrativo (Core)
- **Autenticação:** Acesso restrito via sessão nativa PHP.
- **Importação CSV:** Motor de parsing rígido para relatórios legados (delimitador `;`).
- **Limpeza Automática:** Atualização atômica do catálogo (Delete/Insert) para garantir dados sempre frescos.

### 👷 Operacional (Frontend)
- **Busca Rápida:** Filtro instantâneo por código MERC ou descrição do produto.
- **Carrinho Volátil:** Gestão de itens via `sessionStorage` (isolamento total entre abas).
- **Exportação PDF:** Geração de documento de faturamento fiel ao modelo original ("PEDIDO PARA FATURAR").
- **Barcode 39:** Inclusão automática de códigos de barras legíveis por scanners de mão.

## 🏗️ Arquitetura

O sistema utiliza o padrão **MVSC (Model-View-Service-Controller)** em um ambiente Monorepo:

- **Backend:** PHP 8.1 Vanilla (Classes puras, sem frameworks).
- **Frontend:** HTML5, CSS3 (Bootstrap 5) e JavaScript Vanilla (jQuery para manipulação DOM).
- **Banco de Dados:** MariaDB.
- **Bibliotecas:** Papa Parse (CSV), html2canvas + jsPDF (PDF), Google Fonts (Montserrat & Libre Barcode 39).

## 🚀 Como Executar (Testes Manuais)

### Pré-requisitos
- PHP 8.1 ou superior.
- Servidor MariaDB ativo.
- Usuário `filial945` com senha `senhafilial` e acesso ao banco `atc_portal`.

### Passo a Passo

1. **Clonar o repositório:**
   ```bash
   git clone <repo-url>
   cd cotacao-atacadao
   ```

2. **Configurar o Banco de Dados:**
   Execute o script de migração inicial localizado em:
   `database/migrations/20260428_init_schema.sql`

3. **Subir o Servidor Local:**
   Na raiz do projeto, execute:
   ```bash
   php -S localhost:8080
   ```

4. **Acessar a Aplicação:**
   - **Operador:** Abra `http://localhost:8080/frontend/operador/` no navegador.
   - **Administrador (Upload):** Atualmente via API `POST /api/catalog/upload` (Interface em desenvolvimento).
   - **Health Check:** `http://localhost:8080/api/health`

## 📁 Estrutura do Projeto

```text
.
├── backend/                # Lógica do Servidor (PHP)
│   ├── Controllers/        # Pontos de entrada da API
│   ├── Services/           # Regras de Negócio e Parsing
│   ├── Models/             # Abstração do MariaDB
│   └── Core/               # Configuração e Database Singleton
├── frontend/               # Interface do Usuário (HTML/JS)
│   ├── admin/              # Dashboard Administrativo
│   ├── operador/           # Interface de Cotação
│   └── assets/             # Recursos Estáticos (CSS, JS, Fontes)
├── database/               # Scripts SQL e Migrações
├── docs/                   # Especificações e Histórias de Usuário
└── tests/                  # Testes Unitários (PHPUnit)
```

---

> 💡 **Nota:** Todos os commits seguem o padrão **Conventional Commits** e o código-fonte está documentado em **Inglês (EN-US)**, conforme as boas práticas do projeto.
