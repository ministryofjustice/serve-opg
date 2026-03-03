FROM php:8.4-fpm-alpine3.23@sha256:b7bad36533116d6360d00c3b12820be69bf7655af6057f6222b57befa5eee5c4

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
