# Deploy do CentralCred no Railway

Este guia descreve como colocar o CentralCred em producao no Railway usando o repositorio do GitHub.

## Arquitetura recomendada

Use 3 servicos no Railway:

1. **App Service**
   - Roda o Laravel e recebe as requisicoes web/API.
   - Deve ser o unico servico com dominio publico.

2. **MySQL Service**
   - Banco de dados de producao.

3. **Cron Service**
   - Roda o scheduler do Laravel.
   - Necessario para atualizar `paid_installments` todos os dias e liberar notificacoes de refinanciamento automaticamente.

> Importante: o Railway nao usa `docker-compose.yml` para deploy. O `docker-compose.yml` deste projeto fica para ambiente local.

## Ponto importante sobre o Dockerfile

O `Dockerfile` local fica em:

```text
docker/Dockerfile
```

Ele foi movido para fora da raiz justamente para o Railway nao tentar usar esse Dockerfile local.

O Dockerfile local sobe apenas `php-fpm`, porque no desenvolvimento local quem serve HTTP e o `nginx` do `docker-compose.yml`. Se o Railway usar esse Dockerfile sozinho, a aplicacao pode subir sem responder HTTP no `$PORT`, gerando:

```text
Application failed to respond
```

Para deploy no Railway, deixe o builder automatico do Railway/Railpack detectar Laravel.

O comportamento esperado no Railway e:

- detectar Laravel;
- instalar dependencias PHP/Node;
- compilar assets;
- rodar o app com PHP-FPM/Caddy ou runtime equivalente do Railway.

## 1. Criar o projeto no Railway

1. Acesse o Railway.
2. Crie um **New Project**.
3. Escolha **Deploy from GitHub repo**.
4. Selecione o repositorio do CentralCred.
5. Crie tambem um servico **MySQL** no mesmo projeto.

## 2. Variaveis do App Service

No servico principal da aplicacao, configure:

```env
APP_NAME=CentralCred
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SEU-DOMINIO.up.railway.app
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Gere o `APP_KEY` localmente:

```bash
docker compose exec app php artisan key:generate --show
```

Copie o valor gerado e adicione no Railway:

```env
APP_KEY=base64:VALOR_GERADO
```

Depois que o Railway gerar o dominio publico, atualize `APP_URL` com a URL final.

## 3. Build e deploy do App Service

No App Service:

- **Source**: GitHub repo do CentralCred.
- **Build Command**:

```bash
npm run build
```

- **Pre-deploy Command**:

```bash
chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh
```

Nao rode `db:seed` automaticamente em producao. O seeder atual cria usuarios de teste:

- `admin@centralcred.com`
- `vendedor@centralcred.com`
- senha `password`

Se usar seed em producao para criar usuario inicial, troque a senha imediatamente ou crie um seeder proprio apenas para producao.

## 4. Dominio publico

No App Service:

1. Abra **Settings**.
2. Va em **Networking**.
3. Clique em **Generate Domain**.
4. Copie a URL gerada.
5. Atualize a variavel `APP_URL`.
6. Redeploy o servico.

## 5. Cron Service

Crie um segundo servico conectado ao mesmo repositorio GitHub.

Nome sugerido:

```text
centralcred-cron
```

Use as mesmas variaveis do App Service, principalmente:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Configure o **Start Command** do Cron Service:

```bash
chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh
```

Esse processo fica ativo e executa o scheduler do Laravel. Dentro do projeto ja existe a rotina:

```php
Schedule::command('contracts:sync-paid-installments')
    ->dailyAt('01:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping();
```

Ela roda todo dia as `01:00` no horario de Sao Paulo.

## 6. O que a rotina diaria faz

Comando:

```bash
php artisan contracts:sync-paid-installments
```

Responsabilidade:

- buscar contratos ativos;
- usar `first_discount_date` como base;
- calcular quantas parcelas ja deveriam estar pagas ate a data atual;
- atualizar `paid_installments`;
- permitir que a notificacao de refinanciamento apareca quando `paid_installments >= minimum_installments_for_refinancing`.

Exemplo:

- primeiro desconto: `05/02/2026`;
- contrato de `42` parcelas;
- minimo para refinanciamento: `42`;
- em `04/07/2029`, ainda fica com `41` parcelas pagas;
- em `05/07/2029`, completa `42` parcelas pagas;
- a notificacao de refinanciamento passa a aparecer.

## 7. Comandos uteis no Railway

Rodar migrations manualmente:

```bash
php artisan migrate --force
```

Verificar se o scheduler reconhece a rotina:

```bash
php artisan schedule:list
```

Executar sincronizacao manualmente:

```bash
php artisan contracts:sync-paid-installments
```

Limpar/cachear configuracoes depois de variaveis ajustadas:

```bash
php artisan optimize:clear
php artisan optimize
```

## 8. Checklist antes de liberar producao

- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_KEY` configurada.
- `APP_URL` apontando para o dominio final.
- MySQL conectado.
- Migrations rodando com sucesso.
- App Service com dominio publico.
- Cron Service rodando `php artisan schedule:work`.
- Login testado.
- API testada com a collection Postman.
- Senha do admin inicial trocada, caso tenha usado seed.
- Notificacoes testadas acessando o sino e a tela de contrato.

## 9. Teste rapido pos-deploy

1. Acesse o dominio do Railway.
2. Faca login.
3. Cadastre um cliente.
4. Cadastre banco e convenio.
5. Cadastre um contrato com:
   - `first_discount_date` preenchido;
   - `paid_installments` abaixo do minimo;
   - `minimum_installments_for_refinancing` definido.
6. Rode manualmente no Railway:

```bash
php artisan contracts:sync-paid-installments
```

7. Confira se o campo `paid_installments` foi atualizado quando a data ja justificar novas parcelas.
8. Confira se o sino mostra notificacao quando o contrato atingir o minimo.

## 10. Fontes oficiais usadas

- Railway Laravel Guide: https://docs.railway.com/guides/laravel
- Railway Start Command: https://docs.railway.com/deployments/start-command
- Railway Dockerfiles: https://docs.railway.com/deploy/dockerfiles
