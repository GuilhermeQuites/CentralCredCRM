# CRM Central Cred

CRM em Laravel para acompanhamento de clientes, contratos consignados, historico de contato e oportunidades de refinanciamento.

## Stack

- PHP 8.3
- Laravel 13
- Blade
- TailwindCSS
- MySQL 8
- Docker Compose
- Nginx

## Executar com Docker

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install
docker compose exec app npm run build
```

Acesse:

```txt
http://localhost:8000
```

## Usuarios seed

```txt
Admin: admin@centralcred.com / password
Vendedor: vendedor@centralcred.com / password
```

## Funcionalidades

- Login com perfis `admin` e `seller`
- Dashboard com totais da carteira
- CRUD de clientes
- CRUD de contratos
- Calculo dinamico de elegibilidade por contrato
- Fila de refinanciamento com filtros
- Historico de contatos dentro do contrato

## Testes

```bash
docker compose exec app php artisan test
```
