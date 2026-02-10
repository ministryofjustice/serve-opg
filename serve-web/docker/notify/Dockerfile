FROM php:8.4-fpm-alpine3.23@sha256:41cc31d474f8444ee260b5e8a73d2b00a1c3a437cfc57c2d2d0cbc22bc5a3925

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
