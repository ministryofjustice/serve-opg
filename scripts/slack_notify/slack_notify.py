import os
import json
import urllib.request


def send_to_slack(text, channel, emoji=":warning:"):
    webhook_url = os.environ["SLACK_WEBHOOK_URL"]

    payload = {
        "channel": channel,
        "username": "aws",
        "icon_emoji": emoji,
        "text": text
    }

    data = json.dumps(payload).encode("utf-8")
    req = urllib.request.Request(
        webhook_url,
        data=data,
        headers={"Content-Type": "application/json"}
    )

    with urllib.request.urlopen(req) as resp:
        return {"status": resp.getcode()}


def format_secret_alert(event):
    branch = event.get("branch", "unknown")
    user = event.get("user", "unknown")
    gh_url = event.get("gh_url", "N/A")
    commit_msg = event.get("commit_message", "")

    text = (
        ":rotating_light: *SECRET UPLOADED DETECTED* :rotating_light:\n\n"
        f"*Branch:* {branch}\n"
        f"*User:* {user}\n"
        f"*GH Actions build url:* {gh_url}\n\n"
        f"*Commit message:* {commit_msg}"
    )

    return text, ":rotating_light:"


def format_workflow_alert(event):
    status = event.get("status", "unknown").capitalize()
    branch = event.get("branch", "unknown")
    workflow = event.get("workflow", "unknown")
    user = event.get("user", "unknown")
    frontend_url = event.get("frontend_url", "N/A")
    admin_url = event.get("admin_url", "N/A")
    gh_url = event.get("gh_url", "N/A")
    commit_msg = event.get("commit_message", "")

    emoji = "✅" if status.lower() == "success" else "❌"

    text = (
        f"*Serve OPG Workflow {status} {emoji}*\n\n"
        f"*Branch:* {branch}\n"
        f"*Workflow:* {workflow}\n"
        f"*User:* {user}\n\n"
        f"*Links*\n"
        f"  • *Serve Frontend:* {frontend_url}\n"
        f"  • *Serve Admin:* {admin_url}\n"
        f"  • *GH Actions build url:* {gh_url}\n\n"
        f"*Commit message:* {commit_msg}"
    )

    return text, emoji


def handler(event, context):
    default_channel = os.environ.get("SLACK_CHANNEL", "#serve-opg")
    secrets_channel = os.environ.get("SECRETS_CHANNEL", "#opg-digideps-team")

    if event.get("secret_alert", False):
        text, emoji = format_secret_alert(event)
        channel = secrets_channel
    else:
        text, emoji = format_workflow_alert(event)
        channel = default_channel

    return send_to_slack(text, channel, emoji)
