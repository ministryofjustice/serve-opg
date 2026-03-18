FROM php:8.4-fpm-alpine3.23@sha256:38c3275e8d8fe1b395dd81dccdbdbec6b0fa1fa318e2391c4ad98eae3b6dc0e3

ENV APP_DIR /var/www/app
RUN mkdir -p $APP_DIR
WORKDIR $APP_DIR

COPY index.php /var/www/app/index.php

EXPOSE 8081
WORKDIR /var/www/app

CMD ["php", "-S", "0.0.0.0:8081", "-t", ".", "index.php"]
