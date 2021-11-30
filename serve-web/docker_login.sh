#!/usr/bin/env bash

# Assume role in management account
temp_role=$(aws sts assume-role --role-arn "arn:aws:iam::311462405659:role/serve-opg-ci"\
                               --role-session-name "serve-opg-ci")
export AWS_ACCESS_KEY_ID=$(echo $temp_role | jq .Credentials.AccessKeyId | xargs)
export AWS_SECRET_ACCESS_KEY=$(echo $temp_role | jq .Credentials.SecretAccessKey | xargs)
export AWS_SESSION_TOKEN=$(echo $temp_role | jq .Credentials.SessionToken | xargs)

aws ecr get-login-password \
    --region eu-west-1 \
| docker login \
    --username AWS \
    --password-stdin 311462405659.dkr.ecr.eu-west-1.amazonaws.com

