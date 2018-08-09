#!/usr/bin/env bash
set -e

if [ $# -lt 1 ]
then
    cat <<USAGE

    dcop env_setup - Build all docker containers

    dcop composer - Composer install for the frontend

    dcop frontend_node_setup - Npm install for the frontend
    dcop frontend_node_gen - Gulp for the frontend

    dcop up - Spin up digicop project
    dcop down - Spin down digicop project

    dcop phpunit - Phpunit suite for the frontend
    dcop behat - Behat suite for the frontend

    dcop logs - Stream logs for all digicop containers

    dcop db-migrate - Update db schema

    dcop db-fixtures - Add fixtures (users)

    dcop db - Update db schema and add fixtures

    dcop cache - Clear cache

    dcop shell - Gives you a shell into the container

        dcop shell frontend - Frontend container for nginx
        dcop shell php - Frontend container for PHP-FPM
        dcop shell node - Frontend container for node_runner

        dcop shell behat - Frontend container for behat
        dcop shell phpunit - Frontend container for phpunit

        dcop shell composer - Frontend container for composer_runner

        dcop shell qa - Frontend container for QA tool

        dcop shell db - Postgres shell

USAGE
    exit
fi

TASK="${1}"
DIGICOP_PATH="$HOME/OPG/opg-digicop"

cd ${DIGICOP_PATH}

case "${TASK}" in

logs) docker-compose logs -f
    ;;
env_setup) docker-compose build
    ;;
up) docker-compose up --remove-orphan -d frontend
    #docker-compose run php php app/console doctrine:schema:update --force --quiet;
    #docker-compose run php php app/console doctrine:fixtures:load --append
    ;;
down) docker-compose down
    ;;
restart)
    docker-compose down;
    docker-compose up -d frontend
    ;;
phpunit) docker-compose run --rm phpunit
    ;;
behat)
      docker-compose up -d frontend
      docker-compose run --rm behat
    ;;
composer) docker-compose run --rm composer
    ;;
frontend_node_setup) docker-compose run --rm node /entrypoint-setup.sh
    ;;
frontend_node_gen) docker-compose run --rm node /entrypoint-generate.sh
    ;;
shell)

        if [ $# -lt 2 ]
        then
                echo "Usage : $0 shell <frontend|php|node|behat|phpunit|composer|qa>"
                exit
        fi

        case "$2" in

          frontend) docker-compose exec frontend bash
            ;;
          php) docker-compose exec php bash
            ;;
          node) docker-compose run --entrypoint="bash" node
            ;;
          composer) docker-compose run --entrypoint="bash" composer
            ;;
          behat) docker-compose run --entrypoint="bash" behat
            ;;
          phpunit) docker-compose run --entrypoint="bash" phpunit
            ;;
          qa) docker-compose run --entrypoint="sh" qa
            ;;
          db)
            docker-compose exec postgres psql -U digicop
        esac

    ;;
db)
    docker-compose run php php app/console doctrine:schema:update --force;
    docker-compose run php php app/console doctrine:fixtures:load --append
    ;;
db-migrate)
    docker-compose run php php app/console doctrine:schema:update --force
    ;;
db-fixtures)
    docker-compose run php php app/console doctrine:fixtures:load --append
    ;;
cache)
    docker-compose exec php /scripts/cache-clear.sh
;;
*) echo "Comand not found"
   ;;
esac
