FROM nginx:stable-alpine

ARG APP_HOST_ARG=${APP_HOST_LOCAL_ENV:-app}
ARG APP_PORT_ARG=${APP_PORT_LOCAL_ENV:-9000}

RUN apk update && apk upgrade

## Add Waitforit to wait on app starting
#ENV WAITFORIT_VERSION="v2.4.1"
#RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxclaus/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
#  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
COPY public/build public/build
COPY docker/web/nginx.conf /etc/nginx/nginx.conf
COPY docker/web/default.conf.tmpl /etc/nginx/conf.d/default.conf.tmpl
COPY docker/waitforit/wait-for-it.sh /var/www/wait-for-it.sh
RUN chmod +x /var/www/wait-for-it.sh

ENV APP_HOST=$APP_HOST_ARG
ENV APP_PORT=$APP_PORT_ARG
ENV LOG_LEVEL=warn

RUN envsubst '$APP_HOST $APP_PORT' < /etc/nginx/conf.d/default.conf.tmpl > /etc/nginx/conf.d/default.conf

ENV TIMEOUT=30

CMD "sh", "-c", "/var/www/wait-for-db.sh -h $APP_HOST -p $APP_PORT -t $TIMEOUT \
  && nginx -g "daemon off;"
