#!/usr/bin/env bash
set -ex

composer install --prefer-dist --no-interaction --no-scripts
composer dumpautoload -o
composer run-script post-install-cmd --no-interaction

echo "SUCCESS! Composer finished"
