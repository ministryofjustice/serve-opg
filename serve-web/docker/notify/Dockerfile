FROM php:8.4.28-fpm-alpine3.24@sha256:1c3d5f8dd7f56ae38dfaf33934c7d5f326d5291b611df1e3ea18a332d8ef9407

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
