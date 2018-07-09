#!/usr/bin/env bash
set -e

pwd
ls -l
ls -l bin
bin/phpunit -c tests/phpunit
