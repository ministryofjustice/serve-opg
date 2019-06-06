FROM php:7.2-fpm-alpine

# Install postgresql drivers
RUN apk add --no-cache postgresql-dev \
  && docker-php-ext-install pdo pdo_pgsql

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
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
COPY docker/app/xdebug.sh xdebug.sh
RUN chmod 777 xdebug.sh

ARG WITH_XDEBUG
ENV WITH_XDEBUG=${WITH_XDEBUG:-false}

RUN ./xdebug.sh

ENV TIMEOUT=20
CMD waitforit -address=tcp://$DC_DB_HOST:$DC_DB_PORT -timeout=$TIMEOUT \
  && php bin/console doctrine:migrations:migrate --no-interaction \
  && chown -R www-data var \
  && php-fpm

