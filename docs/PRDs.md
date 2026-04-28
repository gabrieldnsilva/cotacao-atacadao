# 📋 Análise Completa + PRDs — Cotação Online Atacadão

## PARTE 1: Definição da Arquitetura (Monorepo MVSC)

### Resumo Tecnológico

* **Backend:** PHP 8.1 Vanilla (TDD via PHPUnit).
* **Frontend:** HTML, CSS, JS Vanilla (Papa Parse, Chart.js, SweetAlert2, Html2Canvas, JSPdf, Bootstrap, JQuery).
* **Banco de Dados:** MariaDB.
* **Arquitetura:** MVSC (Model, View, Service, Controller) + Front Controller Pattern (`index.php` único).

### Estrutura de Banco de Dados (Inicial)

| Tabela | Responsabilidade | Status |
| :--- | :--- | :--- |
| `catalog` | Armazenar os produtos importados via CSV (MERC, DIGITO, etc.) | Pendente |
| `admin_users` | Armazenar credenciais de acesso para a área de upload | Pendente |

---

## PARTE 2: PRDs (Product Requirements Documents)

Cada PRD abaixo é **autocontida** e desenhada para ser entregue à IA num prompt único e focado.

### PRD-001: Fundação — Configuração Base e Roteamento

**Prioridade:** 🔴 CRÍTICA (Bloqueia tudo)
**Conceitos:** Front Controller, SOLID (DIP), Factory Pattern

**Objetivo:** Criar o esqueleto do monorepo, configurar o roteamento básico do PHP para responder a chamadas da API e servir o frontend.

**Escopo:**

1. **Estrutura de pastas:** Separar `/frontend` e `/backend`.
2. **`index.php` (Front Controller):** Receber todos os requests HTTP, tratar o CORS e direcionar para o Controller correto baseado na URL (ex: `/api/catalog`).
3. **Database Connection:** Criar uma classe Singleton/Factory simples usando `PDO` para conectar ao MariaDB.
4. **Configuração TDD:** Configurar o `phpunit.xml` para a pasta de testes.

**Critério de aceite:** O servidor embutido do PHP (`php -S localhost:8000`) responde com sucesso em `GET /api/health` com um JSON `{ "status": "ok" }`. A conexão com o banco não gera erros.

---

### PRD-002: Autenticação Administrativa

**Prioridade:** 🟡 ALTA (Segurança)
**Conceitos:** KISS (Sessão PHP Nativa)

**Objetivo:** Proteger a rota de upload do CSV para garantir que apenas administradores atualizem o catálogo.

**Escopo:**

1. **`AdminController`:** Receber credenciais de login.
2. **`AuthService`:** Validar credenciais (contra variável de ambiente ou tabela `admin_users` estática — manter KISS).
3. **Sessão:** Usar `$_SESSION` do PHP para manter o admin logado apenas na área de gerência.
4. **Frontend:** Tela de login simples (`/admin.html`).

**Critério de aceite:** Tentar acessar `/api/upload-csv` sem estar autenticado retorna `401 Unauthorized`. Login com sucesso cria sessão no backend.

---

### PRD-003: Módulo de Importação de Catálogo (Upload CSV)

**Prioridade:** 🔴 CRÍTICA (Coração do backend)
**Conceitos:** Agnóstico à Web (Service), Validação Estrita

**Objetivo:** Processar o ficheiro `.csv` de cotações gerado pelo Job, validar os dados e atualizar o MariaDB.

**Escopo:**

1. **`CatalogController`:** Recebe o arquivo `$_FILES`, verifica se o MIME type é `text/csv` e passa o caminho temporário para o Service.
2. **`CsvImportService`:** Recebe o caminho físico. Verifica se as colunas obrigatórias existem (`MERC`, `DIGITO`, `DESCRICAO`, `EMBALAGEM`, `ESTOQ EMB1`, `ESTOQ EMB9`, `PRECO VENDA`). Trata a tipagem (ex: converte `,` para `.` no valor financeiro).
3. **`CatalogModel`:** Recebe o array formatado do Service e executa um `TRUNCATE` seguido de um `INSERT` massivo (ou `UPSERT`) na tabela `catalog`.
4. **TDD:** Escrever testes validando ficheiros `.csv` corrompidos, sem colunas, e em formato válido.

**Critério de aceite:** Upload de um CSV válido atualiza o banco de dados e retorna `200 OK`. Upload de ficheiro sem a coluna `PRECO VENDA` retorna `422 Unprocessable Entity`.

---

### PRD-004: Módulo de Operação (Frontend / Pedidos Assíncronos)

**Prioridade:** 🔴 CRÍTICA (Core Business)
**Conceitos:** Isolamento de Sessão (JS), Single Page Application (SPA)

**Objetivo:** Permitir que múltiplos operadores consultem o catálogo e montem seus pedidos sem interferência.

**Escopo:**

1. **Backend (`CatalogController` & `Service`):** Endpoint `GET /api/catalog` para retornar os itens disponíveis com busca rápida.
2. **Frontend (`OperatorView`):** Interface para o operador inserir o Código (MERC) e a Quantidade.
3. **Gestão de Estado (JS):** Usar `sessionStorage` para guardar o "carrinho" atual. Assim, se dois operadores abrirem o sistema no mesmo PC em abas diferentes, não há conflito. Ao fechar a aba, a sessão morre nativamente.
4. **Lógica JS:** Ao adicionar um item, calcular automaticamente o "Código Interno" (concatenando colunas se for o caso), e calcular "R$ Total" = (Quantidade * Valor). Gerar linha de TOTAL.

**Critério de aceite:** Operador consegue pesquisar item, adicionar quantidade, e ver a tabela atualizada com os valores totais calculados instantaneamente. Refresh na aba não perde os dados (devido ao `sessionStorage`).

---

### PRD-005: Exportação (PDF e Código de Barras)

**Prioridade:** 🟡 ALTA (Fechamento do ciclo)

**Objetivo:** Gerar o documento final para faturamento no formato exato da planilha antiga.

**Escopo:**

1. **Geração de Código de Barras:** Usar a fonte *Libre Barcode 39* (via CSS `@font-face` ou biblioteca JS) aplicando a regra: `CONCATENAR("*"; MERC; DIGITO; "*")` em tempo real na tabela HTML.
2. **Exportação (Html2Canvas + JSPdf):** Capturar a `div` da tabela formatada contendo o cabeçalho ("PEDIDO PARA FATURAR", Data Atual, Horário Atual) e exportar como `.pdf` para o PC do operador baixar e imprimir.

**Critério de aceite:** O clique no botão "Gerar Faturamento" faz o download automático de um PDF com a mesma identidade visual da planilha atual, incluindo códigos de barras legíveis por scanner físico.

---

## PARTE 3: Ordem de Execução Recomendada (Sprints)

```text
PRD-001 (Fundação e Config)       ── Sprint 1: "Hello World MVSC"
PRD-003 (Upload CSV e Model DB)   ── Sprint 2: "Base de Dados Viva"
PRD-002 (Autenticação Admin)      ── Sprint 3: "Segurança de Upload"
PRD-004 (Tela Operador / Busca)   ── Sprint 4: "Fim das Planilhas"
PRD-005 (Exportação PDF/Barras)   ── Sprint 5: "Pronto para Produção"
```

---

Para garantir que o nosso "Model" de Operação fique perfeitamente alinhado com o teu objetivo (YAGNI): Os pedidos montados pelos operadores precisam de ser **salvos no MariaDB** para um histórico centralizado, ou o objetivo é apenas processá-los no navegador (Javascript) e exportá-los em PDF, descartando-os do banco de dados após a impressão?
