# Serve OPG

[![Path to Live](https://github.com/ministryofjustice/serve-opg/actions/workflows/live_workflow.yml/badge.svg)](https://github.com/ministryofjustice/serve-opg/actions/workflows/live_workflow.yml)

 PHP 8.1 & Symfony 5.4

## Prerequisites
Software to download and install

- [docker](https://docs.docker.com/install/)
- [git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

### Generate Certificates

Launch the following commands from the project directory
```bash
# Generate self-signed certificate for the local loadbalancer
./serve-web/generate_certs.sh

# Add certificate to your local trust store to avoid browser warnings
sudo security add-trusted-cert -d -r trustRoot \
-k /Library/Keychains/System.keychain serve-web/certs/web.crt
```

## Build and Run Serve Locally

### Starting from scratch

On first build you should run:

```commandline
make initialise
```

This builds all the containers, installs the JS and PHP dependencies, brings up the app
and resets the fixture data.

The app will be available locally at:
> [https://localhost](https://localhost/)

### Working with local environment

To get a full list of commands for working with serve type:

```make help```

From here you will get commands to bring the app up, take it down and run the various tests.

## Dev and prod mode
The app runs in prod mode as default due to APP_ENV=prod APP_DEBUG=false being set in .env. To run in dev mode,
and enable the Symfony web profiler toolbar, bring the app up using docker-compose.local.yml:

```
make up-dev
```

Note - this will also enable xdebug which can make the test suite run slowly. If you encounter slow test runs then revert to running the app in prod mode.

## Front end assets

Assets are compiled using Symfony Webpack Encore run via a yarn command.

When running `make dependencies` then you should get the volume mounted assets created locally as well.

## Database Migrations
Migrate is run as part of app start up but to manually run migrations
you can run the following commands:

```bash
# Database migrations
# Generate migration script between entities and schema
docker compose run --rm app php bin/console doctrine:migrations:diff

# Generate blank migration script
docker compose run --rm app php bin/console doctrine:migrations:generate

# Example: run migration version 20181019141515
docker compose run --rm app php bin/console doctrine:migrations:execute 20181019141515
```

## Testing
There are two suites of tests:

- Unit tests
- Integration tests (behat tests)

You can run the tests with their respective `make` commands. There is more detailed information on
updating and working with tests in the [testing ](docs/runbooks/TESTING.md) and [debugging ](docs/runbooks/DEBUGGING.md) docs.
