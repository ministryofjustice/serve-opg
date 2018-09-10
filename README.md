# DigiCop

[![CircleCI](https://circleci.com/gh/ministryofjustice/opg-digicop/tree/master.svg?style=svg&circle-token=79410497f5cde03ffb512d50e427dea8a272ff0b)](https://circleci.com/gh/ministryofjustice/opg-digicop/tree/master)

Symfony 3.4 & PHP 7.2

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

# Vendor php dependencies
docker-compose run --rm composer

# Generate static assets
docker-compose run --rm npm

# Build app
docker-compose up -d --build loadbalancer
# --build Build images before starting containers
# -d Detached mode: Run containers in the background

# Migrate database
docker-compose run --rm app php app/console doctrine:schema:update --force

# enable dev mode (local development only)
docker-compose exec app touch /var/www/.enableDevMode

# To disable dev mode and re-enable prod mode (default):
docker-compose exec app rm /var/www/.enableDevMode
```

View logs
```bash
docker-compose logs -f
```

The app will be available locally at:
> [https://localhost](https://localhost/)




# Dev and prod mode
```bash
# dev mode
docker-compose exec app touch /var/www/.enableDevMode

# prod mode (default)
docker-compose exec app rm /var/www/.enableDevMode

```

# Testing
DigiCOP uses PHPUnit and Behats to test the application

## Unit Testing
Run php unit
```bash
docker-compose run --rm phpunit
```

## Integration Testing
```bash
# Load Fixtures
docker-compose run --rm app php app/console doctrine:fixtures:load --append

# Load Fixtures truncating existing data (users, client, orders, deputies)
docker-compose run --rm app php app/console doctrine:fixtures:load --purge-with-truncate

# Run Behat
docker-compose run --rm behat
```

# Debugging
Login to Database
```bash
docker-compose exec postgres psql -U digicop
```

Clear Cache
```bash
docker-compose exec app rm -rf /var/www/var/cache /tmp/app-cache
```

# Front end assets

```bash
# Gulp tasks
# Bash into the npm container
docker-compose run npm bash
# Then run any gulp tasks from there, ie:
gulp watch
```



# Utilities


```bash
#Copy a file into the container
docker cp web/app.php opg-digicop_app_1:/var/www/web/app.php

# Drop the data before schema update (mainl during local development)
docker-compose run --rm app php app/console doctrine:schema:drop --force

```


# Launch specific behat feature
//TODO find an aesier way
comment out "entrypoint" and "command" in behat container
docker-compose up behat
docker-compose exec behat bin/behat -c tests/behat/behat.yml tests/behat/features/03-cases.feature


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
