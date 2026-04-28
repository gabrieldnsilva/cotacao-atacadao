# Spec: Cotação Online Atacadão

**Data:** 2026-04-28
**Status:** Validado
**Autor:** Atlas (AIOX Analyst)

## 1. Visão Geral
O sistema de "Cotação Online" visa substituir o processo manual de faturamento baseado em planilhas Google no Atacadão. A aplicação automatiza a importação de dados de catálogo via CSV, permite a montagem de pedidos de forma assíncrona por múltiplos operadores e gera documentos de faturamento (PDF) com códigos de barras integrados.

## 2. Requisitos de Negócio
- **Importação de Catálogo:** Interface administrativa para upload de CSV legado com colunas fixas.
- **Operação Assíncrona:** Múltiplos operadores podem criar pedidos simultaneamente sem interferência.
- **Isolamento de Sessão:** Uso de `sessionStorage` para garantir que os dados do pedido sejam voláteis e destruídos ao fechar a aba.
- **Exportação de PDF:** Geração de documento de faturamento fiel ao layout da planilha original.
- **Segurança:** Acesso administrativo protegido para upload de dados.

## 3. Arquitetura Técnica
### 3.1 Stack Tecnológica
- **Backend:** PHP 8.1 Vanilla (sem frameworks pesados, seguindo KISS).
- **Frontend:** HTML, CSS, Javascript Vanilla.
- **Banco de Dados:** MariaDB.
- **Bibliotecas (CDN/Local):**
  - Papa Parse (Parsing CSV)
  - SweetAlert2 (Alertas)
  - html2canvas + jsPDF (Exportação PDF)
  - Bootstrap 5 (Styling)
  - JQuery (Manipulação DOM)
  - Google Fonts: Montserrat & Libre Barcode 39.

### 3.2 Estrutura do Monorepo (MVSC)
```text
/backend
  /Controllers  - Gestão de rotas e Request/Response API.
  /Services     - Regras de negócio (Parsing CSV, Validação).
  /Models       - Abstração de dados (PDO MariaDB).
  /Core         - Utilitários e Conexão (Database.php).
/frontend
  /admin        - Interface de gerência.
  /operador     - Interface de faturamento.
  /assets       - CSS, JS e Fontes.
index.php       - Front Controller (Roteamento).
```

## 4. Detalhamento dos Módulos

### 4.1 Módulo Administrativo (Catálogo)
- **Upload:** Aceita apenas arquivos `.csv` com delimitador `;`.
- **Filtro de Colunas:** Extrai apenas: `MERC`, `DIGITO`, `DESCRICAO`, `EMBALAGEM`, `ESTOQ EMB1`, `ESTOQ EMB9`, `PRECO VENDA`.
- **Sanitização:** Converte vírgulas decimais em pontos e limpa espaços em branco.
- **Atomicidade:** Cada upload limpa a tabela `catalog` (`TRUNCATE`) e insere os novos dados.

### 4.2 Módulo de Operação (Pedidos)
- **Busca:** Autocomplete ou busca rápida no catálogo via API.
- **Cálculos:** O frontend calcula `R$ Total` em tempo real (Quantidade * Preço Venda) e a soma total do pedido.
- **Barcode:** Geração visual do código de barras usando a regra `*MERC + DIGITO*` com a fonte Libre Barcode 39.
- **Sessão:** Todos os itens do "carrinho" residem no `sessionStorage` do navegador.

### 4.3 Exportação
- **Processamento:** Lado do Cliente (Client-side).
- **Fidelidade:** O layout do PDF deve seguir rigorosamente o cabeçalho institucional definido no `requirements_natural_language.md`.

## 5. Design Visual
- **Paleta:** Verde Atacadão (`#27ae60`) e Laranja de Ação (`#db7612`).
- **UI:** Montserrat para textos gerais, tabelas de alto contraste para evitar erros de leitura.

## 6. Considerações de Desenvolvimento
- **SOLID:** Princípios aplicados especialmente na separação entre Controller e Service.
- **Clean Code:** Nomenclatura em inglês, variáveis semânticas e funções pequenas.
- **TDD:** Testes unitários para o `CsvImportService` e `CatalogModel`.
