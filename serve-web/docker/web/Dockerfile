FROM nginx:1.31.6-alpine3.24@sha256:d0a6faaed2491faecdb9021f449e8edd7b1bbba246fe2ddf7eba63cccd5b9803

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

EXPOSE 8080

USER nginx

CMD /bin/sh -c "nginx -g 'daemon off;'"
