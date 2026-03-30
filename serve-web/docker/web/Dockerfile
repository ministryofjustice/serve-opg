FROM nginx:stable-alpine-slim@sha256:b33eedfdf089be1f83759ced27b4deec5b6f1b6fc2a6819ebce0ae351a4406e5
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
