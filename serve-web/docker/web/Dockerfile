FROM nginx:stable-alpine

RUN apk update && apk upgrade

# Add Waitforit to wait on app starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxclaus/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
COPY public/build public/build

ARG APP_HOST=${APP_HOST:-app}
ARG APP_PORT=${APP_PORT:-9000}

ENV APP_HOST=${APP_HOST:-app}
ENV APP_PORT=${APP_PORT:-9000}

RUN echo "APP HOST: $APP_HOST"
RUN echo "$APP PORT: APP_PORT"

ARG NGINX_LOG_LEVEL=${NGINX_LOG_LEVEL:-warn}

COPY docker/web/nginx.conf /etc/nginx/nginx.conf
COPY docker/web/default.conf.template /etc/nginx/conf.d/default.conf.template
# Simple envsubst instead of confd
RUN envsubst '$APP_HOST $APP_PORT $NGINX_LOG_LEVEL' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

ENV TIMEOUT=30

# Removed Confd command
CMD waitforit -address=tcp://$APP_HOST:$APP_PORT -timeout=$TIMEOUT && nginx -g "daemon off;"
