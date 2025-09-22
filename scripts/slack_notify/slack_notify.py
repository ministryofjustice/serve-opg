import json
import urllib.request
import urllib.parse
import boto3

secrets_client = boto3.client("secretsmanager")

ROUTES = {
    "secret_alert": {
        "secret_key": "opg-digideps-team",
        "emoji": ":rotating_light:",
        "template": lambda e: (
            ":rotating_light: *SECRET UPLOADED DETECTED* :rotating_light:\n\n"
            f"*Branch:* {e.get('branch', 'unknown')}\n"
            f"*User:* {e.get('user', 'unknown')}\n"
            f"*GH Actions build url:* {e.get('gh_url', 'N/A')}\n\n"
            f"*Commit message:* {e.get('commit_message', '')}"
        ),
    },
    "workflow": {
        "secret_key": "opg-digideps-builds",
        "emoji": None,
        "template": lambda e: (
            f":pipeline: *Serve OPG Workflow {e.get('status', 'unknown').capitalize()} "
            f"{'✅' if e.get('status', '').lower() == 'success' else '❌'}*\n\n"
            f"*Branch:* {e.get('branch', 'unknown')}\n"
            f"*Workflow:* {e.get('workflow', 'unknown')}\n"
            f"*User:* {e.get('user', 'unknown')}\n\n"
            f"*Links*\n"
            f"  • *Serve Frontend:* {e.get('frontend_url', 'N/A')}\n"
            f"  • *Serve Admin:* {e.get('admin_url', 'N/A')}\n"
            f"  • *GH Actions build url:* {e.get('gh_url', 'N/A')}\n\n"
            f"*Commit message:* {e.get('commit_message', '')}"
        ),
    },
    "alarm": {
        "secret_key": "opg-default",
        "emoji": ":warning:",
        "template": lambda e: (
            f":warning: *Serve OPG Alert* - {e.get('AlarmName', 'Unknown').capitalize()}\n\n "
            f"*Description:* {e.get('AlarmDescription', 'Unknown Description').capitalize()}\n\n "
            f"Please check cloudwatch logs in the relevant *Serve* account\n"
        ),
    }
}


def get_secret_value(secret_key: str) -> str:
    resp = secrets_client.get_secret_value(SecretId="slack-webhooks")
    return json.loads(resp["SecretString"])[secret_key]


def send_to_slack(webhook_url: str, text: str, emoji: str):
    payload = {"username": "aws", "icon_emoji": emoji, "text": text}
    data = urllib.parse.urlencode({"payload": json.dumps(payload)}).encode("utf-8")
    req = urllib.request.Request(webhook_url, data=data)
    with urllib.request.urlopen(req) as resp:
        return {"status": resp.getcode()}


def handler(event, context):
    message = event
    event_type = ""

    try:
        if isinstance(event, dict) and "Records" in event:
            for record in event.get("Records", []):
                if not isinstance(record, dict):
                    continue
                sns = record.get("Sns")
                if not isinstance(sns, dict):
                    continue
                raw_message = sns.get("Message")
                if not isinstance(raw_message, str):
                    continue

                try:
                    parsed = json.loads(raw_message)
                except json.JSONDecodeError as e:
                    print(f"Failed to parse SNS message as JSON: {e}, raw_message={raw_message}")
                    continue

                if isinstance(parsed, dict):
                    message = parsed
                    if "AlarmName" in parsed:
                        event_type = "alarm"
                    else: # If not an alarm, we will assume the type is in the message
                        event_type = parsed.get("type", "")
                    break
    except Exception as e:
        print(f"Unexpected error while processing event: {e}")

    if not event_type: 
        event_type = message.get("type")

    if not event_type or event_type not in ROUTES:
        raise ValueError(f"Unknown event type: {event_type}, message: {message}")


    route = ROUTES[event_type]

    text = route["template"](message)
    emoji = route["emoji"] or ("✅" if message.get("status", "").lower() == "success" else "❌")
    webhook_url = get_secret_value(route["secret_key"])

    print(f"Sending to Slack: {text}")

    return send_to_slack(webhook_url, text, emoji)
