#!/usr/bin/env bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout ${DIR}/certs/web.key \
    -new \
    -out ${DIR}/certs/web.crt \
    -subj /CN=\localhost \
    -sha256 \
    -days 3650

