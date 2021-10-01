#!/bin/sh

set -e

if [[ $WITH_XDEBUG = 1 ]]; then
    apk --no-cache add autoconf alpine-sdk && \
      pecl install xdebug && \
      docker-php-ext-enable xdebug

    apk --no-cache del autoconf alpine-sdk;
fi
