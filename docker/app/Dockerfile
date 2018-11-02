FROM php:7.2-fpm-alpine

# Install postgresql drivers
RUN apk add --no-cache postgresql-dev \
&& docker-php-ext-install pdo pdo_pgsql

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
&& chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
COPY web/app.php web/app.php
RUN mkdir -p web/assets
COPY web/config.php web/config.php
COPY vendor vendor
COPY src src
COPY app app
ENV TIMEOUT=20
CMD waitforit -address=tcp://$DC_DB_HOST:$DC_DB_PORT -timeout=$TIMEOUT \
&& php app/console doctrine:migrations:migrate --no-interaction \
&& chown -R www-data var \
&& php-fpm
