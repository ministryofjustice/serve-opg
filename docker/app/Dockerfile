FROM php:7.2-fpm-alpine

# Install postgresql drivers
RUN apk add --no-cache postgresql-dev \
  && docker-php-ext-install pdo pdo_pgsql

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
COPY public/index.php public/index.php
RUN mkdir -p public/assets
RUN mkdir -p tmp/screenshots
COPY vendor vendor
COPY src src
COPY translations translations
COPY bin bin
COPY .env .env
COPY config config
COPY templates templates
COPY docker/app/serve-opg.ini /usr/local/etc/php/conf.d/

ARG WITH_XDEBUG=false

RUN apk --no-cache add autoconf alpine-sdk

RUN if [ $WITH_XDEBUG = "true" ] ; then \
        pecl install xdebug; \
        docker-php-ext-enable xdebug; \
        echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "xdebug.remote_host=docker.for.mac.host.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "xdebug.remote_port=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "xdebug.remote_log="/tmp/xdebug.log"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    fi ;

RUN apk --no-cache del autoconf alpine-sdk

ENV TIMEOUT=20
CMD waitforit -address=tcp://$DC_DB_HOST:$DC_DB_PORT -timeout=$TIMEOUT \
  && php bin/console doctrine:migrations:migrate --no-interaction \
  && chown -R www-data var \
  && php-fpm

