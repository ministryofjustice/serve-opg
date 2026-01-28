FROM nginx:stable-alpine@sha256:313676e806e25fbc67d448fa236a77b4c3e5fe85311e4680f4a227b354c64f62

ARG APP_HOST_ARG=${APP_HOST_LOCAL_ENV:-app}
ARG APP_PORT_ARG=${APP_PORT_LOCAL_ENV:-9000}

RUN apk update && apk upgrade

WORKDIR /var/www
COPY public/build public/build
COPY docker/web/nginx.conf /etc/nginx/nginx.conf
COPY docker/web/default.conf.tmpl /etc/nginx/conf.d/default.conf.tmpl
COPY docker/waitforit/wait-for-it.sh /var/www/wait-for-it.sh
RUN chmod +x /var/www/wait-for-it.sh

ENV APP_HOST=$APP_HOST_ARG
ENV APP_PORT=$APP_PORT_ARG
ENV LOG_LEVEL=warn

RUN envsubst '$APP_HOST $APP_PORT $LOG_LEVEL' < /etc/nginx/conf.d/default.conf.tmpl > /etc/nginx/conf.d/default.conf

ENV TIMEOUT=30

CMD /bin/sh -c "/var/www/wait-for-it.sh -h $APP_HOST -p $APP_PORT -t $TIMEOUT && nginx -g 'daemon off;'"
