FROM nginx:alpine
WORKDIR /var/www
COPY ./web/index.html web/index.html
COPY ./web/robots.txt web/robots.txt
COPY ./web/assets web/assets
COPY docker/web/etc /etc
ENV CONFD_VERSION="0.16.0"
RUN wget "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" &&\
    mkdir -p /opt/confd/bin &&\
    mv confd-${CONFD_VERSION}-linux-amd64 /opt/confd/bin/confd &&\
    chmod +x /opt/confd/bin/confd
ENV PATH="$PATH:/opt/confd/bin"
CMD confd -onetime -backend env && nginx -g "daemon off;"
