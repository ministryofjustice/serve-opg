FROM nginx:stable-alpine

RUN apk upgrade libxml2 openssl libssl1.1 libcrypto1.1 libretls zlib

# Add Confd to configure nginx on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
  && chmod +x /usr/local/bin/confd

# Add Waitforit to wait on app starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
COPY public/build public/build
COPY docker/web/etc /etc

ENV TIMEOUT=30
CMD confd -onetime -backend env \
  && waitforit -address=tcp://$APP_HOST:$APP_PORT -timeout=$TIMEOUT \
  && nginx -g "daemon off;"
