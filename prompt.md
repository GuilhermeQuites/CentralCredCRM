# CRM Central Cred

## Objetivo

Desenvolver um CRM especializado em empréstimos consignados para acompanhamento de clientes, contratos e oportunidades de refinanciamento.

O sistema deve permitir que a Central Cred acompanhe toda a carteira de clientes e identifique automaticamente quando um cliente estiver elegível para refinanciamento, evitando perda de clientes para concorrentes.

---

# Problema Atual

Hoje a empresa:

- capta clientes
- realiza empréstimos
- perde o acompanhamento pós-venda
- esquece clientes elegíveis para refinanciamento
- perde oportunidades para concorrentes

O objetivo do sistema é automatizar esse acompanhamento e melhorar a retenção da carteira.

---

# Stack Tecnológica

## Backend
- PHP 8.3
- Laravel

## Frontend
- Blade
- TailwindCSS

## Banco de Dados
- MySQL 8

## Infraestrutura
- Docker
- Docker Compose
- Nginx

---

# Arquitetura

Utilizar padrão MVC do Laravel.

Estrutura esperada:

```txt
app/
├── Models/
│   ├── User.php
│   ├── Client.php
│   ├── Contract.php
│   └── ContactHistory.php
│
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       ├── ClientController.php
│       ├── ContractController.php
│       └── ContactHistoryController.php
│
├── Services/
│   └── RefinancingService.php
```

---

# Objetivo Principal do Sistema

O sistema deve:

- cadastrar clientes
- cadastrar contratos
- acompanhar parcelas pagas
- calcular elegibilidade de refinanciamento
- mostrar oportunidades de refinanciamento
- registrar histórico de contato
- permitir acompanhamento por vendedor

---

# MVP Inicial

## Funcionalidades

### Login
- autenticação de usuários
- perfil administrador
- perfil vendedor

---

### Dashboard
- total de clientes
- contratos ativos
- clientes elegíveis
- clientes próximos da elegibilidade

---

### Clientes
CRUD completo de clientes.

---

### Contratos
CRUD completo de contratos.

---

### Fila de Refinanciamento
Tela para listar clientes elegíveis ou próximos da elegibilidade.

---

### Histórico de Contato
Registro de:
- ligações
- WhatsApp
- observações
- contatos realizados

---

# Entidades

## User

Representa o usuário do sistema.

Campos:

```txt
id
name
email
password
role
created_at
updated_at
```

Roles:

```txt
admin
seller
```

---

## Client

Representa o cliente da Central Cred.

Campos:

```txt
id
user_id
name
cpf
phone
birth_date
notes
created_at
updated_at
```

Relacionamentos:

```txt
Client pertence a um User
Client possui muitos Contracts
```

---

## Contract

Representa o contrato do cliente.

Campos:

```txt
id
client_id
bank
contract_value
installment_value
total_installments
paid_installments
minimum_installments_for_refinancing
contract_date
status
created_at
updated_at
```

Status:

```txt
active
finished
cancelled
```

Relacionamentos:

```txt
Contract pertence a um Client
Contract possui muitos ContactHistory
```

---

## ContactHistory

Representa os contatos feitos com o cliente.

Campos:

```txt
id
contract_id
type
description
contacted_at
created_at
updated_at
```

Tipos:

```txt
phone
whatsapp
email
note
```

---

# Regra de Negócio

A elegibilidade para refinanciamento deve ser dinâmica.

O sistema não deve considerar uma regra fixa.

No cadastro do contrato, o usuário deve informar a quantidade mínima de parcelas pagas necessárias para aquele contrato poder ser refinanciado.

---

# Exemplo

Contrato:

```txt
84 parcelas
```

Configuração definida pelo usuário:

```txt
Parcelas mínimas para refinanciamento: 28
```

Resultado:

```txt
Cliente com 28 parcelas pagas ou mais:
Elegível para refinanciamento

Cliente com menos de 28 parcelas pagas:
Não elegível
```

---

# Campo obrigatório no contrato

Adicionar o campo:

```txt
minimum_installments_for_refinancing
```

Nome amigável:

```txt
Parcelas mínimas para refinanciamento
```

---

# Service de Refinanciamento

Criar:

```txt
app/Services/RefinancingService.php
```

Responsabilidades:

- calcular elegibilidade
- calcular parcelas restantes
- retornar status do contrato

---

# Regra de cálculo

```php
<?php

namespace App\Services;

class RefinancingService
{
    public function calculate(
        int $paidInstallments,
        int $minimumInstallmentsForRefinancing
    ): array {
        $remainingInstallments =
            $minimumInstallmentsForRefinancing - $paidInstallments;

        if ($paidInstallments >= $minimumInstallmentsForRefinancing) {
            return [
                'status' => 'eligible',
                'message' => 'Cliente elegível para refinanciamento',
                'minimum_installments_for_refinancing' =>
                    $minimumInstallmentsForRefinancing,
                'remaining_installments' => 0,
            ];
        }

        return [
            'status' => 'waiting',
            'message' =>
                "Faltam {$remainingInstallments} parcelas para refinanciamento",
            'minimum_installments_for_refinancing' =>
                $minimumInstallmentsForRefinancing,
            'remaining_installments' => $remainingInstallments,
        ];
    }
}
```

---

# Telas

## Dashboard

Mostrar:

```txt
Clientes cadastrados
Contratos ativos
Elegíveis hoje
Clientes em acompanhamento
```

Tabela:

```txt
Cliente
CPF
Telefone
Banco
Parcelas pagas
Status
```

---

## Clientes

CRUD completo:

```txt
Listar clientes
Criar cliente
Editar cliente
Visualizar cliente
Excluir cliente
```

Campos:

```txt
Nome
CPF
Telefone
Data de nascimento
Observações
Vendedor responsável
```

---

## Contratos

CRUD completo:

```txt
Listar contratos
Criar contrato
Editar contrato
Visualizar contrato
Excluir contrato
```

Campos:

```txt
Cliente
Banco
Valor contratado
Valor da parcela
Total de parcelas
Parcelas pagas
Parcelas mínimas para refinanciamento
Data do contrato
Status
```

---

## Fila de Refinanciamento

Tela responsável por listar oportunidades.

Filtros:

```txt
Elegíveis
Não elegíveis
Faltam até 3 parcelas
Faltam até 6 parcelas
```

---

## Histórico de contato

Dentro do contrato permitir registrar:

```txt
Tipo do contato
Descrição
Data do contato
```

---

# Migrations

## Users

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('name');

    $table->string('email')->unique();

    $table->string('password');

    $table->string('role')->default('seller');

    $table->timestamps();
});
```

---

## Clients

```php
Schema::create('clients', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->string('name');

    $table->string('cpf')->unique();

    $table->string('phone');

    $table->date('birth_date')->nullable();

    $table->text('notes')->nullable();

    $table->timestamps();
});
```

---

## Contracts

```php
Schema::create('contracts', function (Blueprint $table) {
    $table->id();

    $table->foreignId('client_id')
        ->constrained('clients')
        ->cascadeOnDelete();

    $table->string('bank');

    $table->decimal('contract_value', 10, 2);

    $table->decimal('installment_value', 10, 2);

    $table->integer('total_installments');

    $table->integer('paid_installments');

    $table->integer(
        'minimum_installments_for_refinancing'
    );

    $table->date('contract_date');

    $table->string('status')->default('active');

    $table->timestamps();
});
```

---

## Contact Histories

```php
Schema::create('contact_histories', function (Blueprint $table) {
    $table->id();

    $table->foreignId('contract_id')
        ->constrained('contracts')
        ->cascadeOnDelete();

    $table->string('type');

    $table->text('description')->nullable();

    $table->timestamp('contacted_at');

    $table->timestamps();
});
```

---

# Rotas web

```php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactHistoryController;

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::resource('clients', ClientController::class);

Route::resource('contracts', ContractController::class);

Route::get(
    '/refinancing',
    [ContractController::class, 'refinancing']
)->name('contracts.refinancing');

Route::post(
    '/contracts/{contract}/contact-history',
    [ContactHistoryController::class, 'store']
)->name('contracts.contact-history.store');
```

---

# Estrutura de Views

```txt
resources/views/
├── layouts/
│   └── app.blade.php
│
├── dashboard/
│   └── index.blade.php
│
├── clients/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
│
├── contracts/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── refinancing.blade.php
```

---

# Docker

## Estrutura

```txt
Dockerfile
docker-compose.yml
docker/
└── nginx/
    └── default.conf
```

---

# Serviços Docker

- app
- mysql
- nginx

---

# Docker Compose

```yml
version: '3.9'

services:
  app:
    build: .
    container_name: centralcred-app

    working_dir: /var/www

    volumes:
      - ./:/var/www

    depends_on:
      - mysql

  nginx:
    image: nginx:alpine

    container_name: centralcred-nginx

    ports:
      - "8000:80"

    volumes:
      - ./:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf

    depends_on:
      - app

  mysql:
    image: mysql:8

    container_name: centralcred-mysql

    restart: unless-stopped

    environment:
      MYSQL_DATABASE: centralcred
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: root
      MYSQL_USER: centralcred

    ports:
      - "3306:3306"

    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

---

# Dockerfile

```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
```

---

# Seeder

Criar seeders contendo:

```txt
2 usuários
10 clientes
10 contratos
contratos elegíveis
contratos não elegíveis
```

---

# Frontend

Utilizar:

- Blade
- TailwindCSS

Layout:

- administrativo
- simples
- limpo
- responsivo

---

# Requisitos do Sistema

O usuário deve conseguir:

```txt
fazer login
ver dashboard
cadastrar cliente
cadastrar contrato
editar cliente
editar contrato
visualizar contratos elegíveis
registrar contatos realizados
filtrar oportunidades de refinanciamento
```

---

# Comandos esperados

## Subir containers

```bash
docker compose up -d --build
```

---

## Instalar dependências

```bash
docker compose exec app composer install
```

---

## Gerar chave Laravel

```bash
docker compose exec app php artisan key:generate
```

---

## Rodar migrations

```bash
docker compose exec app php artisan migrate --seed
```

---

## Instalar frontend

```bash
docker compose exec app npm install
docker compose exec app npm run build
```

---

# Resultado esperado

Ao abrir o sistema, o usuário deve visualizar:

- dashboard administrativo
- contratos elegíveis
- clientes cadastrados
- contratos cadastrados
- histórico de contato
- filtros de refinanciamento

O sistema deve estar totalmente funcional utilizando:

- Laravel
- Blade
- TailwindCSS
- MySQL
- Docker
- Docker Compose
- Nginx