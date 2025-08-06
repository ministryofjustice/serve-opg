import boto3

sqs = boto3.client('sqs', endpoint_url='http://localhost:4566', region_name='eu-west-1')
queue_url = 'http://localhost:4566/000000000000/sirius-queue'
# queue_url = 'http://sqs.us-east-1.localhost.localstack.cloud:4566/000000000000/sirius-queue'

response = sqs.receive_message(
    QueueUrl=queue_url,
    MaxNumberOfMessages=1,
    WaitTimeSeconds=2
)

messages = response.get('Messages', [])
if messages:
    for msg in messages:
        print("Received message:", msg['Body'])

        # Delete message after processing
        sqs.delete_message(
            QueueUrl=queue_url,
            ReceiptHandle=msg['ReceiptHandle']
        )
else:
    print("No messages found.")
