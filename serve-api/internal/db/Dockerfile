# vim:set ft=dockerfile:
FROM postgres:13-alpine

# Custom initialization scripts
COPY ./create_user.sh   /docker-entrypoint-initdb.d/10-create_user.sh
