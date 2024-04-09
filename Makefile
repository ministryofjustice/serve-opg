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

down: ##@application Bring the whole application down
	docker compose down -v --remove-orphans

build: ##@application Builds all the docker containers
	WITH_XDEBUG=0 docker compose build

up: ##@application Brings the app up in prod mode - requires deps to be built
	docker compose up -d --remove-orphans loadbalancer
	sleep 3

initialise: build dependencies up reset-fixtures ##@application Initialise and bring up the application

build-up: build up reset-fixtures ##@application Builds and brings the app up in prod mode

dependencies:
	# Install composer, css and javascript dependencies
	docker compose run --rm app composer install
	docker compose run --rm yarn
	docker compose run --rm yarn build-dev

clear-cache: ##@application Clears the cache of the app container
	docker compose run --rm app php bin/console cache:clear --env=prod

up-dev: ##@application Brings the app up in dev mode with profiler enabled and xdebug disabled - requires deps to be built
	WITH_XDEBUG=0 docker compose -f docker-compose.yml -f docker-compose.local.yml build app
	docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --remove-orphans loadbalancer
	docker compose -f docker-compose.yml -f docker-compose.local.yml run --rm app php bin/console cache:clear --env=dev

up-dev-xdebug: ##@application Brings the app up in dev mode with profiler and xdebug enabled - requires deps to be built
	WITH_XDEBUG=1 docker compose -f docker-compose.yml -f docker-compose.local.yml build app
	docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --remove-orphans loadbalancer
	docker compose -f docker-compose.yml -f docker-compose.local.yml run --rm app php bin/console cache:clear --env=dev

up-test: ##@application Brings the app up in test mode with profiler and xdebug disabled - requires deps to be built
	WITH_XDEBUG=0 docker compose -f docker-compose.yml -f docker-compose.test.yml build app
	docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --remove-orphans loadbalancer
	docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm app php bin/console cache:clear --env=test

unit-tests: up-test ##@testing Requires the app to be built and up before running
	docker compose -f docker-compose.yml -f docker-compose.test.yml app php bin/phpunit tests $(args)

behat-tests: up-test ##@testing Requires the app to be built and up before running
	docker compose -f docker-compose.yml -f docker-compose.test.yml run behat --suite=local

reset-fixtures: ##@application Reset the fixture data for the app
	docker compose exec app php bin/console doctrine:fixtures:load --purge-with-truncate -n
