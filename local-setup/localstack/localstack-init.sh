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

# Setup Buses

awslocal events create-event-bus --name serve-bus
awslocal events create-event-bus --name sirius-bus

# Create forwarding from Serve Bus to Sirius Bus to simulate cross account bussing
awslocal events put-rule \
  --name forward-to-bus-sirius \
  --event-bus-name serve-bus \
  --event-pattern '{"source": ["opg.serve.order"], "detail-type": ["submitted"]}'

awslocal events put-targets \
  --rule forward-to-bus-sirius \
  --event-bus-name serve-bus \
  --targets '[
    {
      "Id": "forwardToBusSirius",
      "Arn": "arn:aws:events:us-east-1:000000000000:event-bus/sirius-bus"
    }
  ]'

# Create SQS queue to capture events so we can do things with them (this would be passthrough lambda in sirius)

awslocal sqs create-queue --queue-name event-capture-sirius

QUEUE_ARN=$(awslocal sqs get-queue-attributes \
  --queue-url http://localhost:4566/000000000000/event-capture-sirius \
  --attribute-names QueueArn \
  --query 'Attributes.QueueArn' --output text)

awslocal events put-rule \
  --name capture-events \
  --event-bus-name sirius-bus \
  --event-pattern '{}'

awslocal events put-targets \
  --rule capture-events \
  --event-bus-name sirius-bus \
  --targets "[
    {
      \"Id\": \"sqsTarget\",
      \"Arn\": \"$QUEUE_ARN\"
    }
  ]"

# Finally put an event on the Serve bus

awslocal events put-events --entries '[
  {
    "Source": "opg.serve.order",
    "DetailType": "submitted",
    "Detail": "{\"orderId\": 123}",
    "EventBusName": "serve-bus"
  }
]'
