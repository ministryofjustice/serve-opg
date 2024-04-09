#!/bin/bash
set -e

awslocal s3 mb s3://sirius-test-bucket
awslocal s3 mb s3://test-bucket

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "sirius-test-bucket"

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "test-bucket"

awslocal dynamodb create-table --region eu-west-1 --cli-input-json file:///tmp/attempts_table.json
awslocal dynamodb create-table --region eu-west-1 --cli-input-json file:///tmp/sessions_table.json
