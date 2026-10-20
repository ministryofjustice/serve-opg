FROM php:8.4.26-fpm-alpine3.24@sha256:78cd8de9970a9cd6ff4d98860a94eb5bf37dd2d5776b4785562dbfad01c29d5a

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
