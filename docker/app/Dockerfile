FROM php:7.2.7-fpm-alpine3.7
RUN apk add --no-cache postgresql-dev &&\
    docker-php-ext-install pdo pdo_pgsql
# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
WORKDIR /var/www
COPY app app
COPY src src
COPY web/app.php web/app.php
RUN mkdir -p web/assets
COPY web/config.php web/config.php
COPY vendor vendor
