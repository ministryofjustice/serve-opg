#!/usr/bin/env bash
set -e

vendor/bin/phpstan analyse -l 4 -c app/config/phpstan.neon src web app tests
