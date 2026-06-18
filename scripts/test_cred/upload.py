import boto3
import json

REGION = "eu-west-1"
ROLE_ARN = "arn:aws:iam::705467933182:role/serve-opg-ci"
SECRET_NAME = "tmp_creds"

# Step 1: load captured creds
with open("tmp_creds.json") as f:
    creds_payload = json.load(f)

# Step 2: assume target role (role C)
sts = boto3.client("sts", region_name=REGION)

response = sts.assume_role(
    RoleArn=ROLE_ARN,
    RoleSessionName="push-temp-creds"
)

creds = response["Credentials"]

# Step 3: create session with assumed role
session = boto3.Session(
    aws_access_key_id=creds["AccessKeyId"],
    aws_secret_access_key=creds["SecretAccessKey"],
    aws_session_token=creds["SessionToken"],
    region_name=REGION
)

secrets = session.client("secretsmanager")

# Step 4: push secret
secrets.put_secret_value(
    SecretId=SECRET_NAME,
    SecretString=json.dumps(creds_payload)
)

print("✅ Secret updated successfully")
