FROM php:8.5-fpm-alpine3.23@sha256:6d75e69d93c446f9f245e81cd0d330c0db9fee45de9cee95facff0826b21378f

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
