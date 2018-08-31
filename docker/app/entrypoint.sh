#!/usr/bin/env bash

# Wait for dependencies
./wait-for-it.sh $DC_DB_HOST:$DC_DB_PORT

# Apply migrations to database
php app/console doctrine:schema:update --force --no-interaction

# Fix perms on var directory
chown -R www-data var

# Run the app
php-fpm
