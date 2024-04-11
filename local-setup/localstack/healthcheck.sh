#!/bin/bash

# Execute the commands and store their output in variables
dynamodb_output=$(awslocal dynamodb --region eu-west-1 list-tables)
s3_output=$(awslocal s3 ls)

# Count the number of items returned by each command
dynamodb_count=$(echo "$dynamodb_output" | jq -r '.TableNames | length')
s3_count=$(echo "$s3_output" | wc -l)

# Check if both commands return 2 items
if [ "$dynamodb_count" -eq 2 ] && [ "$s3_count" -eq 2 ]; then
    echo "Both commands returned 2 items each."
    exit 0
else
    echo "Error: One or both commands did not return 2 items."
    exit 2
fi
