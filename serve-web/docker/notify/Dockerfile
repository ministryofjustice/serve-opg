FROM php:8.4-fpm-alpine3.23@sha256:a67e1e5260be06ca14c88e0453d5cbe5601afb9841c4943e2dea108f5778d919

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
