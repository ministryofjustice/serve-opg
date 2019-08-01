# Serve OPG

[![CircleCI](https://circleci.com/gh/ministryofjustice/serve-opg/tree/master.svg?style=svg&circle-token=79410497f5cde03ffb512d50e427dea8a272ff0b)](https://circleci.com/gh/ministryofjustice/serve-opg/tree/master)

Symfony 4.3 & PHP 7.2

# Prerequisites
Software to download and install
-   [docker](https://docs.docker.com/install/)
-   [docker-compose](https://docs.docker.com/compose/install/)
-   [git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

# Usage
## Build
Launch all the following commands from the project directory
```bash
# Generate self-signed certificate for the local loadbalancer
./generate_certs.sh

# Add certificate to your local trust store to avoid browser warnings
sudo security add-trusted-cert -d -r trustRoot \
-k /Library/Keychains/System.keychain certs/web.crt

# Create the s3 buckets, generate localstack data in /localstack-data
# & wait for the server to become available
docker-compose up -d localstack
docker-compose run --rm waitforit -address=http://localstack:4572 -debug
docker-compose run --rm aws --endpoint-url=http://localstack:4572 s3 mb s3://sirius_test_bucket
docker-compose run --rm aws --endpoint-url=http://localstack:4572 s3 mb s3://test_bucket

# Create dynamodb tables
docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4569 dynamodb create-table --cli-input-json file://attempts_table.json
docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4569 dynamodb create-table --cli-input-json file://sessions_table.json

# Vendor php dependencies
docker-compose run --rm composer

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

Alternatively use the `Makefile` commands if you're in a hurry:

`make build-up-prod` - Build dependencies and spin up the project in prod mode. Purges database and loads fixtures.

`make build-up-dev` - Build dependencies and spin up the project in dev mode, profiler and xdebug enabled. Purges database and loads fixtures.

`make build-up-dev` - Build dependencies and spin up the project in dev mode, profiler and xdebug disabled. Purges database and loads fixtures.

`make up-prod` - Brings the app up in prod mode - requires deps to be built

`make up-dev` - Brings the app up in dev mode with profiler and xdebug enabled - requires deps to be built

`make up-test` - Brings the app up in test mode with profiler and xdebug disabled - requires deps to be built

`make build-deps` - Builds the project dependencies and services


# View logs
```bash
docker-compose logs -f
```

The app will be available locally at:
> [https://localhost](https://localhost/)


# Dev and prod mode
The app runs in prod mode as default due to APP_ENV=prod APP_DEBUG=false being set in .env. To run in dev mode, and enable the Symfony web profiler toolbar, bring the app up using docker-compose.local.yml:

`docker-compose -f docker-compose.local.yml -f docker-compose.yml up -d --build --remove-orphans loadbalancer`

Note - this will also enable xdebug which can make the test suite run slowly. If you encounter slow test runs then revert to running the app in prod mode.

# Testing
Serve OPG uses PHPUnit and Behat to test the application

## Unit and Functional Testing
Run php unit
```bash
docker-compose run --rm app bin/phpunit --verbose tests

# specific test (if unique)
docker-compose run --rm app bin/phpunit --verbose tests --filter testHomePage

# specific test (if not unique)
docker-compose run --rm app bin/phpunit --verbose tests --filter testHomePage tests/Controller/IndexControllerTest.php

# specific test using groups

Add a @group notation above the test method or class:

/**
  * @group failing
  */
public function testSomething()
{
...
}

# Then run:

docker-compose run --rm app bin/phpunit --verbose tests --group failing
```

Or using the Makefile:

`make phpunit-tests` - Runs the full suite of PHPUnit functional and unit tests - requires the app to be built and up before running

## Integration Testing
```bash
# Load Fixtures
docker-compose run --rm app php bin/console doctrine:fixtures:load --append

# Load Fixtures truncating existing data (users, client, orders, deputies)
docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate

# Run Behat
docker-compose run --rm behat --suite=local

# Launch specific behat feature
docker-compose run --rm behat features/00-security.feature:18
```

Or using the Makefile:

`make behat-tests` - Runs behat tests - requires the app to be built and up before running

### Notify mocking
Notify is mocked via a custom script.
Requests to the service can be seen at

`http://localhost:8081/mock-data`

Behat `NotifyTrait` takes care of resetting / accessing those emails from steps in the behat context.

# Debugging
Login to Database
```bash
docker-compose exec postgres psql -U serve-opg
```

Clear Cache
```bash
docker-compose exec app rm -rf /var/www/var/cache /tmp/app-cache
```

# Xdebug
To enable Xdebug running via Docker in PHPStorm you will need to:

- In `Preferences > Build, Execution, Deployment > Docker` select `Docker for Mac` 
- In `Preferences > Languages and Frameworks > PHP` Click the `...` button next to `CLI Interpreter`
- Click the `+` button to add a new CLI  and select `From Docker, Vagrant, VM, Remote`
- Select `Docker Compose`, for `Server` choose `Docker` and select `app` for Service. Click `OK` and `Apply`.
- Click `Run > Edit Configurations` from the top menu bar, then `+` and select `PhpUnit`
- Name this configuration `Docker`, under `Test Scope` select `Directory` add `tests` directory as the filepath. Click `OK`.
- In `Preferences > Language & Frameworks > PHP > Debug` under `Xdebug > Debug port` enter `10000` . Hit `Apply` and `OK`.

As Xdebug has a large performance hit, it is not installed as part of the Dockerfile by default. Instead it is set as a build argument in docker-compose.local.yml to ensure it will only ever be enabled for local dev. To build the app image with xdebug enabled, run:

`docker-compose -f docker-compose.local.yml -f docker-compose.yml up -d --build --remove-orphans loadbalancer`

or

`make up-dev`

Now you can add break points to any line of code by clicking in the gutter next to line numbers. Then you can either run the entire test suite by selecting `DOCKER` from the dropdown next to the test buttons in the top right of the page and click the phone icon so it turns green. Hit the debug button to run the suite.

Alternatively you can run individual tests by hitting the debug button next to the test method name in the test class. Once the code gets to a break point you can step through and run executions on the current state of the app to help with debugging.

# Front end assets

Assets are compiled using Symfony Encore run via a yarn command.

```bash
# Build front end assets (JS, images, etc)
docker-compose run --rm yarn build-dev

# Build front end assets (JS, images, etc) and autocompile on any file changes in assets folder
docker-compose run --rm yarn watch
```

# Database Migrations
```bash
# Database migrations
# Generate migration script between entities and schema
docker-compose run --rm app php bin/console doctrine:migrations:diff

# Generate blank migration script
docker-compose run --rm app php bin/console doctrine:migrations:generate

# Example: run migration version 20181019141515
docker-compose run --rm app php bin/console doctrine:migrations:execute 20181019141515
```

# Utilities

```bash
#Copy a file into the container
docker cp web/app.php serve-opg_app_1:/var/www/web/app.php

# Drop the data before schema update (mainl during local development)
docker-compose run --rm app php bin/console doctrine:schema:drop --force
```

# Quality Analysis Tools
The Docker image `jakzal/phpqa` contains many useful QA tools
To list the available tools run:
```shell
docker-compose run --rm qa
```

A recommended set of checks is as follows:
-   phpcs
    ```bash
    docker-compose run --rm qa phpcs src
    ```
-   phpstan
    ```bash
    docker-compose run --rm qa phpstan analyse -l 4 src
    ```
-   lint
    ```bash
    docker-compose run --rm qa parallel-lint src web app tests
    ```
-   security-checker
    ```bash
    docker-compose run --rm qa security-checker security:check
    ```

A convenience script is provided for the above set:
```bash
docker-compose run --rm qa ./default_qa_checks.sh
```
