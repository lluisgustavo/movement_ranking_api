# Ranking de Movimentos

Esta é uma demonstração de habilidade em programação. O desafio é implementar um endpoint **RESTful** em **PHP puro**, sem utilizar frameworks.

## Requisitos atendidos

- O endpoint recebe como parâmetro o nome ou identificador de um movimento
- A resposta contém:
  - Nome do movimento
  - Lista ordenada de usuários com:
    - Nome do usuário
    - Recorde pessoal (maior valor)
    - Posição no ranking
    - Data do recorde pessoal

## Tecnologias e práticas

- **PHP 8.1+** — linguagem
- **MySQL 8** — banco de dados
- **Composer** — autoload e dependências
- **Conventional Commits** — convenção de mensagens de commit para histórico legível e semântico
- **PSR-4** — autoload de classes (namespace App\ → src/)

## Como rodar

### Pré-requisitos

- PHP 8.1+
- MySQL 8
- Composer

### 1. Banco de dados

Crie o banco e execute o schema:

```bash
mysql -u root -p < schema.sql
```

Ou manualmente:

```bash
mysql -u root -p
```

```sql
source schema.sql
exit
```

### 2. Configuração

Copie o arquivo de exemplo e ajuste as credenciais:

```bash
cp .env.example .env
```

Edite `.env` com host, usuário e senha do MySQL.

### 3. Dependências

```bash
composer install
```

### 4. Servidor

```bash
php -S localhost:8000 -t public public/index.php
```

O terceiro argumento (`public/index.php`) é o script de roteamento. Todas as requisições passam por ele.

### 5. Testar

```bash
# Por nome
curl http://localhost:8000/ranking/Deadlift
curl http://localhost:8000/ranking/Back%20Squat

# Por ID
curl http://localhost:8000/ranking/1
curl http://localhost:8000/ranking/2

# 404 — movimento inexistente
curl -w "\nHTTP %{http_code}\n" http://localhost:8000/ranking/999
```

## API

### GET /ranking/{identifier}

Retorna o ranking de um movimento. O `identifier` pode ser o **nome** ou o **ID** do movimento.


**Resposta 200**
**Exemplo 1 — Deadlift (sem empates):**

```json
{
  "movement_name": "Deadlift",
  "ranking": [
    { "user_name": "Jose", "personal_record": "190.00", "record_date": "2021-01-06 00:00:00", "ranking_position": 1 },
    { "user_name": "Joao", "personal_record": "180.00", "record_date": "2021-01-02 00:00:00", "ranking_position": 2 },
    { "user_name": "Paulo", "personal_record": "170.00", "record_date": "2021-01-01 00:00:00", "ranking_position": 3 }
  ]
}
```

**Exemplo 2 — Back Squat (com empates):** João e José têm o mesmo recorde (130). Ambos ficam em 1º; Paulo, em 3º (a posição 2 é pulada).

```json
{
  "movement_name": "Back Squat",
  "ranking": [
    { "user_name": "Joao", "personal_record": "130.00", "record_date": "2021-01-03 00:00:00", "ranking_position": 1 },
    { "user_name": "Jose", "personal_record": "130.00", "record_date": "2021-01-03 00:00:00", "ranking_position": 1 },
    { "user_name": "Paulo", "personal_record": "125.00", "record_date": "2021-01-03 00:00:00", "ranking_position": 3 }
  ]
}
```

**Resposta 404** — movimento não encontrado:

```json
{
  "error": "Movimento não encontrado"
}
```

## Estrutura do projeto

```
├── config/
│   └── database.php       # Carrega .env e retorna instância PDO
├── public/
│   └── index.php          # Entry point e roteamento
├── src/
│   ├── Exceptions/
│   │   └── MovementNotFoundException.php
│   ├── Http/
│   │   ├── JsonResponse.php
│   │   └── Router.php
│   ├── Repositories/
│   │   ├── MovementRepository.php
│   │   └── PersonalRecordRepository.php
│   └── Services/
│       └── RankingService.php
├── .env.example
├── composer.json
├── schema.sql             # Data Definition Language + dados iniciais
└── README.md
```

A estrutura aqui não visa overengineering, mas sim demonstrar entendimento de separação de responsabilidades, injeção de dependências, prepared statements, tratamento de erros, respostas HTTP adequadas e também design patterns. 
## Decisões técnicas

### Banco de dados

- **utf8mb4** e **unicode_ci** — suporte completo a Unicode e comparações case-insensitive
- **Tabelas no plural** (`users`, `movements`, `personal_records`) — evita conflito com palavras reservadas como `user`
- **INT UNSIGNED** — IDs sempre são positivos
- **DECIMAL(10,2)** em vez de FLOAT — maior precisão para valores numéricos
- **recorded_at** em vez de `date` — nome mais semântico e evita palavras reservadas
- **Índices** — em `movement_id`, `user_id` e `(movement_id, user_id)` para melhor desempenho.  

### Arquitetura

- **config/database.php** — carrega o .env e expõe uma closure que retorna o PDO, centralizando a configuração da conexão
- **Repository** — acesso a dados, PDO injetado para reduzir o grau de dependência entre partes do código (acoplamento)
- **Service** — orquestração e regras de negócio
- **Separação** — busca de movimento e ranking em repositórios distintos para clareza e performance

### Query de ranking

A princípio eu iria utilizar o RankingService e fazer um algoritmo para conseguir classificar melhor, mas pensando em performance para grandes datasets, o ideal seria através de SQL. A consulta usa **CTE** e **window functions** (MySQL 8).

1. **CTE** — permite atribuir um nome a um conjunto de resultados temporários e referenciá-lo como se fosse uma tabela em SELECT, INSERT, UPDATE ou DELETE.

2. **Window Functions** — funções que calculam um valor para cada linha usando um *conjunto* de linhas relacionadas (a "janela"), sem agrupar o resultado em menos linhas. Diferem de agregadores como SUM/COUNT, que reduziriam várias linhas a uma. Cada linha da tabela permanece, mas recebe um valor extra (ex.: número de linha, posição, média do grupo). A janela é definida por `PARTITION BY` (quais linhas considerar) e `ORDER BY` (ordem dentro da janela). Exemplos: ROW_NUMBER(), RANK(), SUM() OVER (...).

3. **ROW_NUMBER()** — seleciona o melhor recorde de cada usuário. Cada usuário tem vários registros; precisamos apenas do maior. Exemplo para João no Deadlift: 100, 180, 150, 110 → o melhor é 180. Com `PARTITION BY user_id`, a função enumera as linhas dentro de cada usuário. Com `ORDER BY value DESC, recorded_at ASC`, o maior valor fica em 1º; em empate, a data mais antiga vence. Mantemos só onde `rn = 1`, ficando uma linha por usuário com seu recorde.

4. **RANK()** — calcula a posição no ranking e trata empates corretamente (mesmo valor = mesma posição). Exemplo: José 190 → 1º, João 180 → 2º, Paulo e Maria 170 → ambos 3º, Pedro 160 → 5º (o 4º é pulado). Diferente de ROW_NUMBER(), RANK() repete a posição em empates e pula números conforme a regra de ranking. 