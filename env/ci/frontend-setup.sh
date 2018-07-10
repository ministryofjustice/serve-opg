#!/usr/bin/env bash
set -ex

npm install

chown -R $CI_USER_ID:$CI_USER_ID node_modules web

echo "SUCCESS! npm install finished"
