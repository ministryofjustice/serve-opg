FROM nginx:stable-alpine@sha256:0272e4604ed93c1792f03695a033a6e8546840f86e0de20a884bb17d2c924883

ARG APP_HOST_ARG=${APP_HOST_LOCAL_ENV:-app}
ARG APP_PORT_ARG=${APP_PORT_LOCAL_ENV:-9000}

RUN apk update && apk upgrade

WORKDIR /var/www
COPY public/build public/build
COPY docker/web/nginx.conf /etc/nginx/nginx.conf
COPY docker/web/default.conf.tmpl /etc/nginx/conf.d/default.conf.tmpl

ENV APP_HOST=$APP_HOST_ARG
ENV APP_PORT=$APP_PORT_ARG
ENV LOG_LEVEL=warn
ENV TIMEOUT=30

RUN envsubst '$APP_HOST $APP_PORT $LOG_LEVEL' < /etc/nginx/conf.d/default.conf.tmpl > /etc/nginx/conf.d/default.conf

CMD /bin/sh -c "nginx -g 'daemon off;'"
