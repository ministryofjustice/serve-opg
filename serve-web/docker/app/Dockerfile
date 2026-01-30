FROM php:8.4-fpm-alpine3.22@sha256:1c8b0a66807f214435d9d0aabee363d04d77ec30e8119997e8cfd64943b45f0a

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
RUN apk update && apk upgrade

#COMPOSER
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --version=2.8.9
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

WORKDIR /var/www

RUN mkdir -p tmp/screenshots

COPY . /var/www

COPY docker/app/serve-opg.ini /usr/local/etc/php/conf.d/
COPY docker/app/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/app/health-check.sh /usr/local/bin/health-check.sh
RUN chmod +x /usr/local/bin/health-check.sh
RUN mkdir certs && chmod 755 certs && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem

COPY docker/waitforit/wait-for-it.sh /usr/bin/wait-for-it.sh
RUN chmod +x /usr/bin/wait-for-it.sh

ENV TIMEOUT=30

CMD ["/bin/ash", "-c", "/usr/bin/wait-for-it.sh -h $DC_DB_HOST -p $DC_DB_PORT -t $TIMEOUT && \
  php bin/console doctrine:migrations:migrate --no-interaction && \
  php bin/console doctrine:migrations:up-to-date && \
  chown -R www-data var && \
  mkdir -p /tmp/app-logs && \
  chown -R www-data /tmp/app-logs && \
  php-fpm"]
