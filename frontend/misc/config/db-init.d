#!/bin/bash

/usr/local/bin/php /app/app/console doctrine:schema:update --force --quiet;
/usr/local/bin/php /app/app/console doctrine:fixtures:load --append
