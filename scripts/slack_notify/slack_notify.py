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
        "secret_key": "opg-digideps-devs",
        "emoji": ":warning:",
        "template": lambda e: (
            f":warning: *Serve OPG Alert - {e.get('AlarmName', 'Unknown').capitalize()} "
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
    if "Records" in event:
        for record in event["Records"]:
            if "Sns" in record:
                message = json.loads(record["Sns"]["Message"])
                if "AlarmName" in message:
                    event_type = "alarm"

    if event_type == "":
        event_type = message.get("type")
    if event_type not in ROUTES:
        raise ValueError(f"Unknown event type: {event_type}")

    route = ROUTES[event_type]

    text = route["template"](message)
    emoji = route["emoji"] or ("✅" if message.get("status", "").lower() == "success" else "❌")
    webhook_url = get_secret_value(route["secret_key"])

    print(f"Sending to Slack: {text}")

    return send_to_slack(webhook_url, text, emoji)
