FROM alpine:3.23@sha256:5b10f432ef3da1b8d4c7eb6c487f2f5a8f096bc91145e68878dd4a5019afde11

ENV POSTGRES_DATABASE=**None**
ENV POSTGRES_HOST=**None**
ENV POSTGRES_PORT=5432
ENV POSTGRES_USER=**None**
ENV POSTGRES_PASSWORD=**None**
ENV POSTGRES_EXTRA_OPTS=''
ENV S3_BUCKET=**None**
ENV S3_ENDPOINT=**None**
ENV S3_PREFIX=**None**
ENV S3_S3V4=yes

# Install AWS CLI and wget
RUN apk add --no-cache --update \
    aws-cli \
    wget

# Use 14 as later versions can cause issues with our backup and restore
RUN apk add --no-cache \
    --repository http://dl-cdn.alpinelinux.org/alpine/v3.17/main \
    postgresql14-client=14.15-r0

RUN mkdir certs && chmod 755 certs \
 && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem

RUN addgroup -S orchestration \
 && adduser -S -G orchestration orchestration \
 && mkdir /app \
 && chown orchestration:orchestration /app

COPY --chown=orchestration:orchestration backup.sh /app/backup.sh
COPY --chown=orchestration:orchestration restore.sh /app/restore.sh
COPY --chown=orchestration:orchestration common.sh /app/common.sh
COPY --chown=orchestration:orchestration analyse-database.sh /app/analyse-database.sh

WORKDIR /app

USER orchestration