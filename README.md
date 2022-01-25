# Serve OPG

[![CircleCI](https://circleci.com/gh/ministryofjustice/serve-opg/tree/master.svg?style=svg&circle-token=79410497f5cde03ffb512d50e427dea8a272ff0b)](https://circleci.com/gh/ministryofjustice/serve-opg/tree/master)

Symfony 4.4 & PHP 7.4

# Prerequisites

Software to download and install
-   [docker](https://docs.docker.com/install/)
-   [docker-compose](https://docs.docker.com/compose/install/)
-   [git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
## Build
Launch the following commands from the project directory

```bash
# Generate self-signed certificate for the local loadbalancer
./generate_certs.sh

# Add certificate to your local trust store to avoid browser warnings
sudo security add-trusted-cert -d -r trustRoot \
-k /Library/Keychains/System.keychain certs/web.crt
```

Then run either `make build-up-prod` or `make build-up-dev` to build and bring the app up in prod or dev mode. See the Makefile section below for further details on the make commands available.

Once the app has been built once it can be brought up with `make up-prod` or `make up-dev`.

If there are any issues when using any of the make commands the full manual list of commands to build and bring the app up are below:

```bash
# Generate self-signed certificate for the local loadbalancer
./generate_certs.sh

# Add certificate to your local trust store to avoid browser warnings
sudo security add-trusted-cert -d -r trustRoot \
-k /Library/Keychains/System.keychain certs/web.crt

# Create the s3 buckets, generate localstack data in /localstack-data
# & wait for the server to become available
docker-compose up -d localstack
docker-compose run --rm waitforit -address=http://localstack:4572 -debug -timeout=30
docker-compose run --rm aws --endpoint-url=http://localstack:4572 s3 mb s3://sirius-test-bucket
docker-compose run --rm aws --endpoint-url=http://localstack:4572 s3 mb s3://test-bucket

# Create dynamodb tables
docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4569 dynamodb create-table --cli-input-json file://attempts_table.json
docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4569 dynamodb create-table --cli-input-json file://sessions_table.json

# Vendor php dependencies
docker-compose run --rm app composer install --no-interaction

# Install javascript dependencies
docker-compose run --rm yarn

# Compile static assets
docker-compose run --rm yarn build-dev

# Build app
docker-compose up -d --build --remove-orphans loadbalancer
# --build Build images before starting containers
# -d Detached mode: Run containers in the background

# Add sample users and cases (local env only).
# See docker-compose.yml app container, DC_FIXURES_USERS variable
docker-compose run --rm app php bin/console doctrine:fixtures:load --append
```

## Makefile
The Makefile included with the project includes a few different options for building the app and/or dependencies:

`make build-up-prod` - Build dependencies and spin up the project in prod mode. Purges database and loads fixtures.

`make build-up-dev` - Build dependencies and spin up the project in dev mode, profiler and xdebug enabled. Purges database and loads fixtures.

`make build-up-test` - Build dependencies and spin up the project in test mode, profiler and xdebug disabled. Purges database and loads fixtures.

`make up-prod` - Brings the app up in prod mode - requires deps to be built

`make up-dev` - Brings the app up in dev mode with profiler and xdebug enabled - requires deps to be built

`make up-test` - Brings the app up in test mode with profiler and xdebug disabled - requires deps to be built

`make build-deps` - Builds the project dependencies and services

## View logs

```bash
docker-compose logs -f
```

The app will be available locally at:
> [https://localhost](https://localhost/)

## Dev and prod mode

The app runs in prod mode as default due to APP_ENV=prod APP_DEBUG=false being set in .env. To run in dev mode, and enable the Symfony web profiler toolbar, bring the app up using docker-compose.local.yml:

`docker-compose -f docker-compose.local.yml -f docker-compose.yml up -d --build --remove-orphans loadbalancer`

Note - this will also enable xdebug which can make the test suite run slowly. If you encounter slow test runs then revert to running the app in prod mode.

## Front end assets

Assets are compiled using Symfony Webpack Encore run via a yarn command.

```bash
# Build front end assets (JS, images, etc)
docker-compose run --rm yarn build-dev

# Build front end assets (JS, images, etc) and autocompile on any file changes in assets folder
docker-compose run --rm yarn watch
```

## Database Migrations

```bash
# Database migrations
# Generate migration script between entities and schema
docker-compose run --rm app php bin/console doctrine:migrations:diff

# Generate blank migration script
docker-compose run --rm app php bin/console doctrine:migrations:generate

# Example: run migration version 20181019141515
docker-compose run --rm app php bin/console doctrine:migrations:execute 20181019141515
```

## Utilities

```bash
#Copy a file into the container
docker cp web/app.php serve-opg_app_1:/var/www/web/app.php

# Drop the data before schema update (mainl during local development)
docker-compose run --rm app php bin/console doctrine:schema:drop --force
```
