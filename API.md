# CentralCred API

Base local:

```text
http://localhost:8000/api
```

Todas as rotas, exceto `POST /api/login`, exigem:

```http
Authorization: Bearer TOKEN
Accept: application/json
Content-Type: application/json
```

Atalho usado nos exemplos:

```bash
BASE_URL="http://localhost:8000/api"
TOKEN="cole-o-token-aqui"
```

## Autenticacao

### POST /api/login

Faz login e retorna um Bearer Token.

Campos:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| email | string | sim | E-mail do usuario |
| password | string | sim | Senha do usuario |

```bash
curl -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@centralcred.com",
    "password": "password"
  }'
```

Resposta principal:

```json
{
  "token_type": "Bearer",
  "access_token": "TOKEN",
  "user": {}
}
```

### GET /api/me

Retorna o usuario autenticado.

```bash
curl "$BASE_URL/me" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### POST /api/logout

Invalida o token atual.

```bash
curl -X POST "$BASE_URL/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Utilitarios

### GET /api/dashboard

Retorna contadores gerais.

```bash
curl "$BASE_URL/dashboard" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### GET /api/options

Retorna dados auxiliares para formularios: usuarios, clientes com matriculas, bancos, convenios, tipos de contrato, tipos de contato, roles e permissoes.

```bash
curl "$BASE_URL/options" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Clientes

Campos de cliente:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| user_id | integer/null | nao | ID do vendedor responsavel |
| name | string | sim | Nome do cliente |
| cpf | string | sim | CPF com ou sem mascara. Salva como `000.000.000-00` |
| phone | string | sim | Telefone com ou sem mascara. Salva como `(00) 00000-0000` |
| email | string/null | nao | E-mail valido |
| birth_date | date/null | nao | Data de nascimento `YYYY-MM-DD` |
| notes | string/null | nao | Observacoes |
| registration_count | string | nao | `1`, `2` ou `3`; se omitido, assume `1` |
| registrations | array | sim | Lista de 1 a 3 matriculas, sem duplicar |

### GET /api/clients

Lista clientes. Aceita busca por nome ou CPF.

Query params:

| Param | Descricao |
|---|---|
| search | Busca por nome ou CPF |
| per_page | Quantidade por pagina |

```bash
curl "$BASE_URL/clients?search=joao&per_page=15" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### POST /api/clients

Cria cliente.

```bash
curl -X POST "$BASE_URL/clients" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "name": "Cliente Teste",
    "cpf": "12345678901",
    "phone": "11999999999",
    "email": "cliente@email.com",
    "birth_date": "1980-01-10",
    "notes": "Observacao do cliente",
    "registration_count": "2",
    "registrations": ["12345", "67890"]
  }'
```

### GET /api/clients/{id}

Detalha cliente com vendedor, matriculas e contratos.

```bash
curl "$BASE_URL/clients/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### PUT /api/clients/{id}

Atualiza cliente. Envie o payload completo.

```bash
curl -X PUT "$BASE_URL/clients/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "name": "Cliente Atualizado",
    "cpf": "123.456.789-01",
    "phone": "(11) 99999-9999",
    "email": "novo@email.com",
    "birth_date": "1980-01-10",
    "notes": "Atualizado via API",
    "registration_count": "1",
    "registrations": ["12345"]
  }'
```

### PATCH /api/clients/{id}

Mesmo comportamento do `PUT` no backend atual.

```bash
curl -X PATCH "$BASE_URL/clients/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "name": "Cliente Atualizado",
    "cpf": "123.456.789-01",
    "phone": "(11) 99999-9999",
    "registrations": ["12345"]
  }'
```

### DELETE /api/clients/{id}

Exclui cliente. Exige permissao `excluir_cliente`.

```bash
curl -X DELETE "$BASE_URL/clients/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Contratos

Campos de contrato:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| client_id | integer | sim | ID do cliente |
| client_registration_id | integer/null | nao | ID da matricula. Se cliente tiver 1 matricula, pode omitir |
| bank_id | integer | sim | ID do banco |
| agreement_id | integer | sim | ID do convenio |
| contract_type | string | sim | `refinancing`, `new` ou `portability` |
| contract_value | decimal/string | sim | Aceita decimal ou moeda BR (`R$ 10.000,00`) |
| installment_value | decimal/string | sim | Valor da parcela |
| total_installments | integer | sim | Total de parcelas |
| paid_installments | integer | sim | Parcelas pagas |
| minimum_installments_for_refinancing | integer | sim | Minimo para refinanciar |
| contract_date | date | sim | Data do contrato `YYYY-MM-DD` |
| first_discount_date | date/null | nao | Data do primeiro desconto |

### GET /api/contracts

Lista contratos. Aceita busca por nome ou CPF do cliente.

```bash
curl "$BASE_URL/contracts?search=maria&per_page=15" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### POST /api/contracts

Cria contrato.

```bash
curl -X POST "$BASE_URL/contracts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "client_registration_id": 1,
    "bank_id": 1,
    "agreement_id": 1,
    "contract_type": "new",
    "contract_value": "R$ 10.000,00",
    "installment_value": "R$ 350,00",
    "total_installments": 84,
    "paid_installments": 0,
    "minimum_installments_for_refinancing": 6,
    "contract_date": "2026-05-10",
    "first_discount_date": "2026-06-05"
  }'
```

Se `paid_installments = 0`, o backend valida a regra de fechamento da folha no dia 15.

### GET /api/contracts/{id}

Detalha contrato com cliente, matricula, banco, convenio, historico e status de refinanciamento.

```bash
curl "$BASE_URL/contracts/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### PUT /api/contracts/{id}

Atualiza contrato. Envie o payload completo.

```bash
curl -X PUT "$BASE_URL/contracts/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "client_registration_id": 1,
    "bank_id": 1,
    "agreement_id": 1,
    "contract_type": "refinancing",
    "contract_value": 12000,
    "installment_value": 400,
    "total_installments": 84,
    "paid_installments": 10,
    "minimum_installments_for_refinancing": 6,
    "contract_date": "2026-05-10",
    "first_discount_date": "2026-06-05"
  }'
```

### PATCH /api/contracts/{id}

Mesmo comportamento do `PUT` no backend atual.

```bash
curl -X PATCH "$BASE_URL/contracts/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "client_registration_id": 1,
    "bank_id": 1,
    "agreement_id": 1,
    "contract_type": "new",
    "contract_value": 10000,
    "installment_value": 350,
    "total_installments": 84,
    "paid_installments": 7,
    "minimum_installments_for_refinancing": 6,
    "contract_date": "2026-05-10"
  }'
```

### DELETE /api/contracts/{id}

Exclui contrato. Exige permissao `excluir_contrato`.

```bash
curl -X DELETE "$BASE_URL/contracts/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### POST /api/contracts/{id}/contact-history

Registra contato no contrato.

Campos:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| type | string | sim | Tipo do contato. Veja `/api/options` |
| description | string/null | nao | Descricao |
| contacted_at | datetime | sim | Data/hora do contato |

```bash
curl -X POST "$BASE_URL/contracts/1/contact-history" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "whatsapp",
    "description": "Cliente respondeu.",
    "contacted_at": "2026-05-27 10:30:00"
  }'
```

## Bancos

Campo:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| name | string | sim | Nome do banco |

```bash
curl "$BASE_URL/banks" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

```bash
curl -X POST "$BASE_URL/banks" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Banco Teste"}'
```

```bash
curl -X PUT "$BASE_URL/banks/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Banco Atualizado"}'
```

```bash
curl -X PATCH "$BASE_URL/banks/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Banco Atualizado"}'
```

```bash
curl -X DELETE "$BASE_URL/banks/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Convenios

Campo:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| name | string | sim | Nome do convenio |

```bash
curl "$BASE_URL/agreements" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

```bash
curl -X POST "$BASE_URL/agreements" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Convenio Teste"}'
```

```bash
curl -X PUT "$BASE_URL/agreements/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Convenio Atualizado"}'
```

```bash
curl -X PATCH "$BASE_URL/agreements/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Convenio Atualizado"}'
```

```bash
curl -X DELETE "$BASE_URL/agreements/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Usuarios

Campos:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| name | string | sim | Nome |
| email | string | sim | E-mail unico |
| password | string | sim ao criar | Senha minima de 8 caracteres |
| role | string | sim | `admin` ou `seller` |
| permissions | array | nao | Lista de permissoes |

```bash
curl "$BASE_URL/users" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

```bash
curl -X POST "$BASE_URL/users" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Usuario Teste",
    "email": "usuario@email.com",
    "password": "password",
    "role": "seller",
    "permissions": ["editar_cliente", "editar_contrato"]
  }'
```

```bash
curl -X PUT "$BASE_URL/users/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Usuario Atualizado",
    "email": "usuario@email.com",
    "password": "",
    "role": "seller",
    "permissions": ["editar_cliente"]
  }'
```

```bash
curl -X PATCH "$BASE_URL/users/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Usuario Atualizado",
    "email": "usuario@email.com",
    "role": "seller",
    "permissions": ["editar_cliente"]
  }'
```

```bash
curl -X DELETE "$BASE_URL/users/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Refinanciamento

### GET /api/refinancing

Lista contratos da fila.

Filtros:

| filter | Descricao |
|---|---|
| eligible | Aptos para refinanciamento |
| waiting | Aguardando parcelas |
| up_to_3 | Faltando ate 3 parcelas |
| up_to_6 | Faltando ate 6 parcelas |

```bash
curl "$BASE_URL/refinancing?filter=eligible" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### GET /api/refinancing-notifications

Lista notificacoes ativas.

```bash
curl "$BASE_URL/refinancing-notifications" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### POST /api/contracts/{id}/refinancing-notification/viewed

Marca notificacao como visualizada. Ela so volta apos 24 horas se o contrato continuar apto.

```bash
curl -X POST "$BASE_URL/contracts/1/refinancing-notification/viewed" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### POST /api/contracts/{id}/refinancing-notification/not-refinanced

Marca contrato como nao refinanciado e define a parcela em que ele volta a notificar.

Campo:

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---:|---|
| notify_after_paid_installments | integer | sim | Parcela exata para voltar a notificar. Deve ser maior que as parcelas pagas atuais |

```bash
curl -X POST "$BASE_URL/contracts/1/refinancing-notification/not-refinanced" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "notify_after_paid_installments": 15
  }'
```

## Permissoes

A API respeita as mesmas permissoes do painel para acoes protegidas.

```text
visualizar_usuarios, criar_usuarios, editar_usuarios, excluir_usuarios,
editar_cliente, excluir_cliente,
editar_contrato, excluir_contrato,
visualizar_bancos, criar_bancos, editar_bancos, excluir_bancos,
visualizar_convenios, criar_convenio, editar_convenio, excluir_convenio
```

Administradores tem acesso total.

## Respostas comuns

### Sem token

```json
{
  "message": "Token nao informado."
}
```

Status: `401`

### Token invalido

```json
{
  "message": "Token invalido."
}
```

Status: `401`

### Sem permissao

```json
{
  "message": "Sem permissao para executar esta acao."
}
```

Status: `403`

### Erro de validacao

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "cpf": ["CPF ja possui cadastro."]
  }
}
```

Status: `422`
