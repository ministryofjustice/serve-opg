FROM php:8.5-fpm-alpine3.23@sha256:bdc083e7b6acfb5eec64b5c85c134fe33cde8aa177da0d93fc46e43df2555b98

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
