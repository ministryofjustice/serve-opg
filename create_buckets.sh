#! /usr/bin/env bash

export AWS_ACCESS_KEY_ID="foo"
export AWS_SECRET_ACCESS_KEY="foo"

aws --endpoint-url=http://localhost:4569 s3 mb s3://sirius_test_bucket
aws --endpoint-url=http://localhost:4569 s3 mb s3://test_bucket

unset AWS_ACCESS_KEY_ID
unset AWS_SECRET_ACCESS_KEY
