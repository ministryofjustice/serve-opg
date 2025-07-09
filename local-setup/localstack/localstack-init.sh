#!/bin/bash
set -e

bucket="test-bucket"
awslocal s3api create-bucket --bucket $bucket
awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::test-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::test-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "test-bucket"

bucket="sirius-test-bucket"
awslocal s3api create-bucket --bucket $bucket
key_output=$(awslocal kms create-key --region eu-west-1 --description "sirius key" --key-usage ENCRYPT_DECRYPT --origin AWS_KMS)
key_id=$(echo $key_output | jq -r '.KeyMetadata.KeyId')
awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-test-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-test-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "sirius-test-bucket"
awslocal kms create-alias --region eu-west-1 --alias-name alias/sirius-s3 --target-key-id $key_id
awslocal s3api put-bucket-encryption --region eu-west-1 --bucket $bucket --server-side-encryption-configuration "{\"Rules\":[{\"ApplyServerSideEncryptionByDefault\":{\"SSEAlgorithm\":\"aws:kms\",\"KMSMasterKeyID\":\"$key_id\"},\"BucketKeyEnabled\":true}]}"
awslocal s3api get-bucket-encryption --region eu-west-1 --bucket $bucket

awslocal dynamodb create-table --region eu-west-1 --cli-input-json file:///tmp/attempts_table.json
awslocal dynamodb create-table --region eu-west-1 --cli-input-json file:///tmp/sessions_table.json

awslocal secretsmanager create-secret --name "database-password" --secret-string "dcdb2018!"
