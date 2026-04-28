# 🏛️ Arquitetura Técnica — Cotação Online Atacadão

Este documento detalha a estrutura técnica, padrões de projeto e decisões arquiteturais para o sistema de Cotação Online.

## 1. Visão Geral da Arquitetura

O sistema adota uma abordagem **Monorepo** com uma arquitetura **MVSC (Model-View-Service-Controller)** no backend e uma interface **Vanilla JS** no frontend. O objetivo principal é manter a simplicidade (**KISS**) e evitar dependências desnecessárias (**YAGNI**).

### 1.1 Stack Tecnológica

- **Servidor Web:** Apache/Nginx (Ubuntu).
- **Linguagem Backend:** PHP 8.1 (Vanilla).
- **Banco de Dados:** MariaDB.
- **Linguagem Frontend:** HTML5, CSS3, Javascript (Vanilla).
- **Bibliotecas Frontend:** Bootstrap 5, jQuery, Papa Parse, SweetAlert2, jsPDF, html2canvas.

## 2. Estrutura de Diretórios

```text
cotacao-atacadao/
├── backend/                # Lógica do Servidor
│   ├── Controllers/        # Processamento de Requests e Respostas (JSON)
│   ├── Services/           # Regras de Negócio e Lógica Pesada
│   ├── Models/             # Abstração de Dados e Queries SQL
│   ├── Core/               # Componentes Base (Database, Router, Config)
│   └── index.php           # Front Controller (Único ponto de entrada)
├── frontend/               # Interface do Usuário
│   ├── admin/              # Dashboard Administrativo (Upload CSV)
│   ├── operador/           # Interface de Cotação (Operação)
│   ├── assets/             # CSS, JS, Imagens e Fontes
│   └── index.html          # Landing Page / Login
├── docs/                   # Documentação do Projeto
└── tests/                  # Testes Unitários e de Integração (PHPUnit)
```

## 3. Backend (PHP MVSC)

### 3.1 Front Controller Pattern
Todas as requisições para `/api/*` são direcionadas para o `backend/index.php`. Este arquivo é responsável por:
1. Configurar headers de CORS e Content-Type.
2. Gerenciar sessões administrativas.
3. Roteamento básico para os Controllers apropriados.

### 3.2 Camada de Model
Responsável pela estrutura de dados e persistência. Utiliza **PDO** para interagir com o MariaDB.
- `CatalogModel.php`: CRUD para a tabela `catalog`.
- `AdminModel.php`: Verificação de credenciais.

### 3.3 Camada de Service
Contém a lógica de negócio agnóstica à interface.
- `CsvImportService.php`: Realiza o parsing, validação e sanitização do arquivo `.csv`.
- `AuthService.php`: Gerencia a lógica de autenticação.

### 3.4 Camada de Controller
Intermedia o Request e o Service. Retorna sempre JSON.
- `CatalogController.php`: Endpoints para busca de produtos e upload de CSV.
- `AuthController.php`: Endpoints para login/logout administrativo.

## 4. Banco de Dados (MariaDB)

### 4.1 Tabela `catalog`
Armazena os produtos importados. O conteúdo é efêmero (sofre `TRUNCATE` a cada novo upload válido).

| Campo | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | INT AUTO_INC | PK |
| `merc` | INT | Código do Produto |
| `digito` | INT | Dígito verificador |
| `descricao` | VARCHAR(255) | Nome do produto |
| `embalagem` | VARCHAR(100) | Tipo de embalagem |
| `estoq_emb1` | INT | Estoque disponível 1 |
| `estoq_emb9` | INT | Estoque disponível 9 |
| `preco_venda` | DECIMAL(10,2) | Preço de venda atualizado |

### 4.2 Tabela `admin_users`
Armazena os usuários com permissão de upload.

| Campo | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | INT AUTO_INC | PK |
| `username` | VARCHAR(50) | Nome de usuário |
| `password` | VARCHAR(255) | Hash da senha (password_hash) |

## 5. Frontend (Vanilla JS & sessionStorage)

O frontend opera como uma aplicação de página única (SPA) simples.

### 5.1 Isolamento de Sessão
- Os dados da cotação atual (itens selecionados, quantidades) são armazenados no `sessionStorage`.
- Isso garante que:
    1. Múltiplas abas não interfiram entre si.
    2. Os dados sejam destruídos ao fechar a aba.
    3. O servidor permaneça *stateless* em relação aos pedidos.

### 5.2 Exportação de PDF
Utiliza `html2canvas` para renderizar a tabela HTML formatada e `jsPDF` para gerar o documento final no cliente. A fonte **Libre Barcode 39** é carregada via CSS para garantir a leitura correta dos códigos de barras.

## 6. Fluxo de Importação (Backend)

1. Recebe arquivo via POST (`multipart/form-data`).
2. Valida MIME Type (`text/csv`).
3. Abre o arquivo e verifica a presença das colunas obrigatórias fixas.
4. Inicia transação no banco:
    - `TRUNCATE TABLE catalog;`
    - Inserção em massa dos dados sanitizados.
5. Retorna status de sucesso ou erro detalhado.

## 7. Padrões e Princípios

- **SOLID:** Separação de responsabilidades nas classes PHP.
- **KISS:** Uso de funções nativas do PHP 8.1 sempre que possível.
- **DRY:** Centralização da conexão de banco no `Core/Database.php`.
- **Security:** Prepared statements no PDO, sanitização de inputs e hashes de senha.
