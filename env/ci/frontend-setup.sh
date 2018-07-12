#!/usr/bin/env bash

npm install

chown -R $CI_USER_ID:$CI_USER_ID node_modules web

echo "SUCCESS! npm install finished"
