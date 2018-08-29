#!/usr/bin/env sh
phpcs src
phpstan analyse -l 4 src
parallel-lint src web app tests
security-checker security:check
