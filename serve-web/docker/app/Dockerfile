FROM php:8.1.18-fpm-alpine3.18

ENV PHP_EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20190902

RUN apk add --no-cache ${PHPIZE_DEPS}

ARG WITH_XDEBUG=0
RUN if [[ $WITH_XDEBUG = 1 ]] ; then \
  pecl install xdebug-3.1.4  ;\
  docker-php-ext-enable xdebug ; \
  echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.discover_client_host = true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.log = /tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.idekey = PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  fi ;

# Install postgresql drivers
RUN apk add --no-cache postgresql-dev \
  && docker-php-ext-install pdo pdo_pgsql

# Install zip for php
RUN apk add --no-cache \
  libzip-dev \
  zip \
  && docker-php-ext-install zip

RUN apk add --no-cache fcgi

#force upgrade packages
RUN apk upgrade libxml2 openssl libssl1.1 libcrypto1.1 libretls zlib busybox curl gzip xz xz-libs libcurl

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"

RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

#COMPOSER
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --version=2.6.6
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

#PHPUNIT
RUN composer global require "phpunit/phpunit"

ENV PATH /root/.composer/vendor/bin:$PATH

WORKDIR /var/www

RUN mkdir -p tmp/screenshots

COPY . /var/www

COPY docker/app/serve-opg.ini /usr/local/etc/php/conf.d/
COPY docker/app/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/app/health-check.sh /usr/local/bin/health-check.sh
RUN chmod +x /usr/local/bin/health-check.sh

ENV TIMEOUT=30
CMD waitforit -address=tcp://$DC_DB_HOST:$DC_DB_PORT -timeout=$TIMEOUT \
  && php bin/console doctrine:migrations:migrate --no-interaction \
  && php bin/console doctrine:migrations:up-to-date \
  && chown -R www-data var \
  && php-fpm
