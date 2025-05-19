import boto3

sqs = boto3.client('sqs', endpoint_url='http://localhost:4566',
                   region_name='us-east-1',
                   aws_access_key_id='test',
                   aws_secret_access_key='test')

queue_url = 'http://localhost:4566/000000000000/event-capture-sirius'

response = sqs.receive_message(
    QueueUrl=queue_url,
    MaxNumberOfMessages=5,
    WaitTimeSeconds=2
)

for message in response.get('Messages', []):
    print("Received:", message['Body'])
