#!/usr/bin/env bash

gulp

chown -R $CI_USER_ID:$CI_USER_ID node_modules web
