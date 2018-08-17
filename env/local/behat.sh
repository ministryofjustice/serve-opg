#!/usr/bin/env bash
set -e

/usr/local/bin/php app/console doctrine:schema:update --force
/usr/local/bin/php app/console doctrine:fixtures:load --append
bin/behat -c tests/behat/behat.yml