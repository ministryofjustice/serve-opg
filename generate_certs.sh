#!/usr/bin/env bash

openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout docker/loadbalancer/certs/web.key \
    -new \
    -out docker/loadbalancer/certs/web.crt \
    -subj /CN=\localhost \
    -reqexts SAN \
    -extensions SAN \
    -config <(cat /System/Library/OpenSSL/openssl.cnf \
        <(printf '[SAN]\nsubjectAltName=DNS:localhost')) \
    -sha256 \
    -days 3650

sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain docker/loadbalancer/certs/web.crt
