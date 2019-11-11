# DOCKER TASKS

build-up-prod: build-deps up-prod ## Build dependencies and spin up the project in prod mode. Purges database and loads fixtures.
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n

build-up-dev: build-deps up-dev ## Build dependencies and spin up the project in dev mode, profiler and xdebug enabled. Purges database and loading fixtures.
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n

build-up-test: build-deps up-test ## Build dependencies and spin up the project in test mode, profiler and xdebug disabled. Purges database and and fixtures.
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n

up-prod: ## Brings the app up in prod mode - requires deps to be built
	docker-compose build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose up -d --remove-orphans loadbalancer

up-dev: ## Brings the app up in dev mode with profiler and xdebug enabled - requires deps to be built
	docker-compose -f docker-compose.local.yml -f docker-compose.yml build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose -f docker-compose.local.yml -f docker-compose.yml up -d --remove-orphans loadbalancer

up-test: ## Brings the app up in test mode with profiler and xdebug disabled - requires deps to be built
	docker-compose -f docker-compose.test.yml -f docker-compose.yml build app

	docker-compose run --rm app waitforit -address=tcp://postgres:5432 -timeout=20 -debug
	docker-compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

	# Build app
	docker-compose -f docker-compose.test.yml -f docker-compose.yml up -d --remove-orphans loadbalancer

phpunit-tests: ## Requires the app to be built and up before running
	docker-compose -f docker-compose.test.yml -f docker-compose.yml run --rm app php bin/phpunit --verbose tests

behat-tests: ## Requires the app to be built and up before running
	# Add sample users and cases (local env only).
	docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate -n
	docker-compose -f docker-compose.test.yml -f docker-compose.yml run --rm behat --suite=local

build-deps: ## Runs through all steps required before the app can be brought up
	# Create the s3 buckets, generate localstack data in /localstack-data
	# & wait for the server to become available
	docker-compose up -d localstack
	docker-compose run --rm waitforit -address=http://localstack:4572 -debug -timeout=30
	docker-compose run --rm aws --endpoint-url=http://localstack:4572 s3 mb s3://sirius_test_bucket
	docker-compose run --rm aws --endpoint-url=http://localstack:4572 s3 mb s3://test_bucket

	# Create dynamodb tables (using - before command allows errors. Required as the table could already exist)
	@-docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4569 dynamodb create-table --cli-input-json file://attempts_table.json
	@-docker-compose run --rm aws --region eu-west-1 --endpoint-url=http://localstack:4569 dynamodb create-table --cli-input-json file://sessions_table.json

	# Vendor php dependencies
	docker-compose run --rm composer

	# Install javascript dependencies
	docker-compose run --rm yarn

	# Compile static assets
	docker-compose run --rm yarn build-dev

