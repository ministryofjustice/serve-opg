#!/usr/bin/env bash

openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout docker/loadbalancer/certs/localhost.key \
    -new \
    -out docker/loadbalancer/certs/localhost.crt \
    -subj /CN=\digicop \
    -reqexts SAN \
    -extensions SAN \
    -config <(cat /System/Library/OpenSSL/openssl.cnf \
        <(printf '[SAN]\nsubjectAltName=DNS:digicop')) \
    -sha256 \
    -days 3650

sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain docker/loadbalancer/certs/localhost.crt
