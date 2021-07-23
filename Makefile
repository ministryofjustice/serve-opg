#COLORS
GREEN  := $(shell tput -Txterm setaf 2)
WHITE  := $(shell tput -Txterm setaf 7)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

HELP_FUN = \
	%help; \
	while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^([a-zA-Z\-]+)\s*:.*\#\#(?:@([a-zA-Z\-]+))?\s(.*)$$/ }; \
	print "usage: make [target]\n\n"; \
	for (sort keys %help) { \
	print "${WHITE}$$_:${RESET}\n"; \
	for (@{$$help{$$_}}) { \
	$$sep = " " x (32 - length $$_->[0]); \
	print "  ${YELLOW}$$_->[0]${RESET}$$sep${GREEN}$$_->[1]${RESET}\n"; \
	}; \
	print "\n"; }

help: ##@other Show this help.
	@perl -e '$(HELP_FUN)' $(MAKEFILE_LIST)

down-app: ##@application Bring the whole application down
	docker-compose down -v --remove-orphans

up-prod: ##@application Brings the app up in prod mode - requires deps to be built
	WITH_XDEBUG=0 docker-compose build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose up -d --remove-orphans loadbalancer
	docker-compose run --rm app php bin/console cache:clear --env=test

up-dev: ##@application Brings the app up in dev mode with profiler and xdebug enabled - requires deps to be built
	WITH_XDEBUG=0 docker-compose -f docker-compose.local.yml -f docker-compose.override.yml -f docker-compose.yml build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose -f docker-compose.local.yml -f docker-compose.override.yml -f docker-compose.yml up -d --remove-orphans loadbalancer
	docker-compose run --rm app php bin/console cache:clear --env=test

up-dev-xdebug: ##@application Brings the app up in dev mode with profiler and xdebug enabled - requires deps to be built
	WITH_XDEBUG=1 docker-compose -f docker-compose.local.yml -f docker-compose.override.yml -f docker-compose.yml build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose -f docker-compose.local.yml -f docker-compose.override.yml -f docker-compose.yml up -d --remove-orphans loadbalancer
	docker-compose run --rm app php bin/console cache:clear --env=test

up-test: ##@application Brings the app up in test mode with profiler and xdebug disabled - requires deps to be built
	WITH_XDEBUG=0 docker-compose -f docker-compose.test.yml -f docker-compose.yml build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose -f docker-compose.test.yml -f docker-compose.yml up -d --remove-orphans loadbalancer
	docker-compose run --rm app php bin/console cache:clear --env=test

phpunit-tests: up-test ##@testing Requires the app to be built and up before running
	docker-compose -f docker-compose.test.yml -f docker-compose.yml run --rm app php bin/phpunit --verbose tests $(args)

behat-tests: up-test ##@testing Requires the app to be built and up before running
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n
	docker-compose -f docker-compose.test.yml -f docker-compose.yml run --rm behat --suite=local

build-up-prod: build-deps up-prod ##@builds Build dependencies and spin up the project in prod mode. Purges database and loads fixtures.
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n

build-up-dev: build-deps up-dev ##@builds Build dependencies and spin up the project in dev mode, profiler and xdebug enabled. Purges database and loads fixtures.
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n

build-up-test: build-deps up-test ##@builds Build dependencies and spin up the project in test mode, profiler and xdebug disabled. Purges database and and fixtures.
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n

build-deps: ##@builds Runs through all steps required before the app can be brought up
	# Create the s3 buckets, generate localstack data in /localstack-data
	# & wait for the server to become available
	docker-compose up -d localstack
	docker-compose run --rm waitforit -address=http://localstack:4566 -debug -timeout=30
	docker-compose run --rm aws --endpoint-url=http://localstack:4566 s3 mb s3://sirius-test-bucket
	docker-compose run --rm aws --endpoint-url=http://localstack:4566 s3 mb s3://test-bucket

	# Create dynamodb tables (using - before command allows errors. Required as the table could already exist)
	@-docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4566 dynamodb create-table --cli-input-json file://attempts_table.json
	@-docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4566 dynamodb create-table --cli-input-json file://sessions_table.json

	# Vendor php dependencies
	docker-compose run --rm app composer install

	# Install javascript dependencies
	docker-compose run --rm yarn

	# Compile static assets
	docker-compose run --rm yarn build-dev

reset-fixtures: ##@application Reset the fixture data for the app
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n
