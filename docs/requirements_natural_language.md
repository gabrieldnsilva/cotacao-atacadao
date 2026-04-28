Cotação Online:

Stack: Frontend - HTML, CSS, Javascript (Vanilla), Lib de CSS, Lib de Parse CSV, Lib para Exportar .PDF, etc...
Backend - PHP Vanilla (Se necessário), MariaDB para banco de dados, Postfixx para envio de e-mail.

Ideia: Internamente no Atacadão, temos um processo de faturamento de pedidos.
Esse processo é atualmente feito através do google spreadsheets, o que mantêm uma dependência grande com os serviços google e com o operador da planilha saber como utilizá-la. Correndo até mesmo o risco de alterações nas fórmulas pré-definidas para se obter os resultados esperados.

Atualmente, a planilha possui um cabeçalho

| Imagem do Atacadão | PEDIDO PARA FATURAR |
| Cliente: | QUANTIDADE CUPOM | DATA ATUAL (20/04/2026, por exemplo) | Dia da semana - Horário atual|

Já o conteúdo da planilha, é estabelecido assim:

| Código | Código Interno | Descrição | Embalagem | Quantidade de Venda | Valor | R$ Total | Código de Barras |

Explicação:

Código (INT): O código é inserido manualmente (ex: 13263)

Código Interno (INT): É a extensão do código ( ex: 172) fórmula: (=ARRAY_CONSTRAIN(ARRAYFORMULA(SE(ÉERROS(ÍNDICE(BD!A:G;CORRESP($B8;BD!A:A;0);2));"";ÍNDICE(BD!A:G;CORRESP($B8;BD!A:A;0);2))); 1; 1))

Descrição (VARCHAR): Descrição interna do produto fórmula: (=ARRAY_CONSTRAIN(ARRAYFORMULA(SE(ÉERROS(ÍNDICE(BD!A:G;CORRESP($B8;BD!A:A;0);3));"";ÍNDICE(BD!A:G;CORRESP($B8;BD!A:A;0);3))); 1; 1))

Embalagem (VARCHAR): "Modelo" de embalagem referenciado (ex: CXA 1 X 4 X 5KG) - Fórmula: (=ARRAY_CONSTRAIN(ARRAYFORMULA(SE(ÉERROS(ÍNDICE(BD!A:G;CORRESP($B8;BD!A:A;0);4));"";ÍNDICE(BD!A:G;CORRESP($B8;BD!A:A;0);4))); 1; 1))

Quantidade de Venda (INT): Número inteiro para quantidade de venda
Valor (Float/Decimal): Valor por embalagem em R$.
R$ Total (Float/Decimal): Valor total da venda do produto (individual)
Código de Barra (Fonte Libre Barcode39): Código de barra para leitura através de scanner de mão (Fórmula: =SE(OU(C8="";ESQUERDA(E8;2)="KG");"";CONCATENAR("*";B8;C8;"*")))

Ao fim da planilha, temos uma linha com TOTAL: Soma de todos os valores totais dos itens da planilha

Dentro da planilha de trabalho, temos uma planilha oculta, da qual realizamos a importação manualmente dos dados para atualizar conforme cotações atuais
É um processo realizado dentro de nosso servidor, do qual não temos acesso através de API ou automações, utilizando nossas credenciais de acesso.

Acessamos um job, inserimos os parâmetros de geração do relatório e após a execução do job, o relatório é gerado em .csv

Por padrão, o relatório possui muitos campos de colunas, das quais somente alguns são utilizados, ou seja, são adequados também manualmente antes de executar um copia e cola, estes são:

| MERC | DIGITO |DESCRICAO | EMBALAGEM | ESTOQ EMB1 | ESTOQ EMB9 | PRECO VENDA |

Os valores de exemplo dos campos podem ser:
544 | 135 | CF.CHOCOLATE QUENTE PEQUENO | UND 1 X 1 1UND | 1 | 0 | 6,9 |

Esses relatórios em .CSV são o coração da aplicação e pelo menos 2 vezes na semana é necessário atualizá-los.

Além de uma dependência forte com serviços do GMAIL, existe o problema de ser um trabalho manual e repetitivo, que pode gerar retrabalhos na elaboração das fórmulas, preenchimento de dados (importação manual) e outros fatores.

A ideia é criar uma aplicação WEB, sem a obrigatoriedade de autenticação, mas que instancie o preenchimento desses pedidos para faturar de forma assíncrona

- Interface de "Administrador" para permitir atualizar o banco de dados com um .csv válido (autenticação necessária)
    - Válido = Com todas as colunas necessárias disponíveis, para não levar informações inconsistentes ao banco de dado, MIME Type de upload exclusivo .csv
- Mais de 1 usuário pode criar pedidos ao mesmo tempo
- Um usuário não deve interferir nas sessões que outro usuário criou
- Se o usuário fechar a sessão, a sessão é destruída, para que não fiquem múltiplas sessões abertas no sistema, um usuário pode fechar somente sua própria sessão

Constraints do ambiente:

PHP 8.1.2-1ubuntu2.23(cli)
Módulos:
[PHP Modules]
calendar Core ctype date exif FFI fileinfo filter ftp gettext hash iconv json libxml mysqli mysqlnd openssl pcntl pcre PDO pdo_mysql Phar posix readline Reflection session shmop sockets sodium SPL standard sysvmsg sysvsem sysvshm tokenizer Zend OPcache zlib

Javascript Vanilla
Já foram utilizados em outros projetos:

- Papa Parse
- Chart.js
- SweetAlert2
- Html2Canvas
- JSPdf
- Bootstrap
- JQuery

Mas imagino que com importações diretas com cdn possamos utilizar tudo o que temos de disponível.

É importante considerar que a arquitetura da aplicação seja monorepo, com frontend e backend no mesmo repositório mas separados.
Importante utilizar elementos de SOLID, Clean Code (nomes, comentários, variáveis, todos conceitos)

Arquitetura pode ser MVSC

- Model é a camada de dados, responsável por definir a estrutura dos dados (Schemas). É agnóstico a View e a Controller
- View é a camada de visão/interface, transforma os dados que vêm do controller numa interface visual e exibe informação de forma amigável. Agnóstico a base de dados e a lógica de negócio (Service Layer).
- Service deve executar as regras de negócio e é agnóstico à interface, nunca usar objetos específicos da web, como HttpRequest ou Response dentro de Service.
- Controller é responsável por gerir rotas e pedidos e é agnóstico ao Banco de Dados, apenas envia dados para o Service.

Utilizar fundamentos de KISS (Keep It Simple, Stupid) e YAGNI.

Devido às restrições de ambiente, testes poderão ser
