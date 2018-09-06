#!/usr/bin/env bash
openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout certs/web.key \
    -new \
    -out certs/web.crt \
    -subj /CN=\localhost \
    -sha256 \
    -days 3650

