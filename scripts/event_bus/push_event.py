import boto3
import json

eventbridge = boto3.client('events', endpoint_url='http://localhost:4566', region_name='eu-west-1')

response = eventbridge.put_events(
    Entries=[
        {
            'Source': 'opg.supervision.serve',
            'DetailType': 'court-order-submitted',
            'Detail': json.dumps({
                'clientId': 1,
                'variousDetailsTBC': 2345
            }),
            'EventBusName': 'serve-bus'
        }
    ]
)

print("PutEvents response:", response)
