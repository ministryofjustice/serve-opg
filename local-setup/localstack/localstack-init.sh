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

awslocal secretsmanager create-secret --name "database-password" --secret-string "dcdb2018!" --region eu-west-1

# Create EventBridge buses
awslocal events create-event-bus --name serve-bus --region eu-west-1
awslocal events create-event-bus --name sirius-bus --region eu-west-1

# Create forwarding rule on serve-bus
awslocal events put-rule \
  --name forward-to-sirius \
  --event-bus-name serve-bus \
  --region eu-west-1 \
  --event-pattern '{"source": ["opg.supervision.serve"]}'

# Add target: sirius-bus
awslocal events put-targets \
  --event-bus-name serve-bus \
  --rule forward-to-sirius \
  --region eu-west-1 \
  --targets '[{"Id":"sirius-bus-target","Arn":"arn:aws:events:eu-west-1:000000000000:event-bus/sirius-bus"}]'

# The queue doesn't actually exist on sirius side but we can't pull directly from eventbus so we add it to test our setup works
awslocal sqs create-queue --queue-name sirius-queue --region eu-west-1

awslocal sqs get-queue-attributes \
  --region eu-west-1 \
  --queue-url http://localhost:4566/000000000000/sirius-queue \
  --attribute-names QueueArn

# Create rule on sirius-bus
awslocal events put-rule \
  --name sirius-receiver \
  --event-bus-name sirius-bus \
  --region eu-west-1 \
  --event-pattern '{"source": ["opg.supervision.serve"]}'

# Add target: SQS queue
awslocal events put-targets \
  --event-bus-name sirius-bus \
  --rule sirius-receiver \
  --region eu-west-1 \
  --targets '[{
    "Id": "sirius-queue-target",
    "Arn": "arn:aws:sqs:eu-west-1:000000000000:sirius-queue"
  }]'
