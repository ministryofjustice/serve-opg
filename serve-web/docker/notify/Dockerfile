FROM php:8.5.7-fpm-alpine3.24@sha256:fd9669cdc9caa2bc4857a2c9f6b5e86c47a7f85043a6717c29901ca4a8b1cd4e

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
