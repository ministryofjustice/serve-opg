FROM php:8.5-fpm-alpine3.23@sha256:82dd8cfd2aa93a98b0357e4c810f894c4ca265b5034aef3be654faae5f579487

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
