import boto3
import os

def assume_role_and_get_secret_client(role_arn, session_name, region):
    # Assume the IAM role
    sts_client = boto3.client('sts')
    response = sts_client.assume_role(
        RoleArn=role_arn,
        RoleSessionName=session_name
    )

    # Create a session using the assumed role credentials
    session = boto3.Session(
        aws_access_key_id=response['Credentials']['AccessKeyId'],
        aws_secret_access_key=response['Credentials']['SecretAccessKey'],
        aws_session_token=response['Credentials']['SessionToken']
    )

    # Create a Secrets Manager client using the session
    secret_client = session.client('secretsmanager', region_name=region)

    return secret_client


if __name__ == "__main__":
    account_id = os.getenv('ACCOUNT_ID')
    role_arn = f'arn:aws:iam::{account_id}:role/serve-opg-ci'
    session_name = 'serve-ci-get-behat-user'
    secret_id = 'behat_password'
    region = 'eu-west-1'
    secret_name = 'BEHAT_PASSWORD'

    # Assume the IAM role and get Secrets Manager client
    secret_client = assume_role_and_get_secret_client(role_arn, session_name, region)

    # Get the secret value from AWS Secrets Manager
    response = secret_client.get_secret_value(SecretId=secret_id)
    secret_value = response['SecretString']

    print(secret_value)
