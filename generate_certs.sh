#!/usr/bin/env bash

openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout certs/web.key \
    -new \
    -out certs/web.crt \
    -subj /CN=\localhost \
    -reqexts SAN \
    -extensions SAN \
    -config <(cat /System/Library/OpenSSL/openssl.cnf \
        <(printf '[SAN]\nsubjectAltName=DNS:localhost')) \
    -sha256 \
    -days 3650

printf "\n\nAdding certificates to your trusted certs store...\n"
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain certs/web.crt
