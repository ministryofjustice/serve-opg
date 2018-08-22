FROM nginx:alpine
WORKDIR /var/www
COPY nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./web/index.html web/index.html
COPY ./web/robots.txt web/robots.txt
COPY ./web/assets web/assets
