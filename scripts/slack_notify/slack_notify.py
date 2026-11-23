import json
import logging
import urllib.parse
import urllib.request
from datetime import datetime, timedelta
from pathlib import Path

import boto3

logger = logging.getLogger(__name__)
logger.setLevel("INFO")

secrets_client = boto3.client("secretsmanager")
ALARM_CONFIGURATION_DIR = Path("/opt/alarms")

DEFAULT_LOGS = {
    "log_group": "{environment}",
    "query": [
        "fields @timestamp, @message, @logStream, @log",
        "| sort @timestamp desc",
        "| limit 100",
    ],
}


def normalise_alarm_name(alarm_name):
    return "-".join(part for part in alarm_name.split("-") if not part.isdigit())


def get_alert_configuration(alarm_name):
    normalised_alarm_name = normalise_alarm_name(alarm_name)
    matches = []

    for configuration_file in ALARM_CONFIGURATION_DIR.glob("**/*.json"):
        with configuration_file.open(encoding="utf-8") as file:
            configuration = json.load(file)

        configured_name = configuration.get("name", "")
        if configured_name and (
            normalised_alarm_name == configured_name
            or normalised_alarm_name.endswith(f"-{configured_name}")
        ):
            matches.append(configuration)

    return max(matches, key=lambda configuration: len(configuration["name"]), default={})


def get_environment(alarm_name, configuration):
    environment_position = configuration.get("environment_position", 0)
    alarm_name_parts = alarm_name.split("-")

    if environment_position >= len(alarm_name_parts):
        return "unknown"

    return alarm_name_parts[environment_position]


def encode_cloudwatch_value(value):
    return urllib.parse.quote(str(value), safe="").replace("%", "*")


def get_alarm_url(region, alarm_name):
    encoded_alarm_name = urllib.parse.quote(alarm_name, safe="")

    return (
        "https://console.aws.amazon.com/cloudwatch/home"
        f"?region={region}#alarm:alarmFilter=ANY;name={encoded_alarm_name}"
    )


def get_alarm_period(trigger):
    if trigger.get("Period"):
        return int(trigger["Period"])

    periods = [
        int(metric["MetricStat"]["Period"])
        for metric in trigger.get("Metrics", [])
        if metric.get("MetricStat", {}).get("Period")
    ]

    return max(periods, default=60)


def get_log_url(message, region, environment, logs):
    if not message.get("StateChangeTime"):
        return None

    alarm_time = datetime.fromisoformat(
        message["StateChangeTime"].replace("Z", "+00:00")
    )

    trigger = message.get("Trigger", {})
    period = get_alarm_period(trigger)
    evaluation_periods = int(trigger.get("EvaluationPeriods", 5))

    start_time = alarm_time - timedelta(seconds=period * evaluation_periods)

    start = start_time.isoformat(timespec="milliseconds").replace("+00:00", "Z")
    end = alarm_time.isoformat(timespec="milliseconds").replace("+00:00", "Z")

    query = encode_cloudwatch_value("\n".join(logs["query"]))
    log_group = encode_cloudwatch_value(
        logs["log_group"].replace("{environment}", environment)
    )

    query_parameters = [
        f"end~'{encode_cloudwatch_value(end)}",
        f"start~'{encode_cloudwatch_value(start)}",
        "timeType~'ABSOLUTE",
        "tz~'UTC",
        f"editorString~'{query}",
        "isLiveTail~false",
        f"source~(~'{log_group})",
    ]

    query_details = "~(" + "~".join(query_parameters) + ")"

    route = urllib.parse.quote(
        f"?queryDetail={query_details}",
        safe="~()*'",
    ).replace("%", "$")

    return (
        f"https://{region}.console.aws.amazon.com/cloudwatch/home"
        f"?region={region}#logsV2:logs-insights{route}"
    )


def create_link(label, url):
    return f"<{url}|{label}>" if url else None


def alarm_message(message, region):
    alarm_name = message.get("AlarmName", "Unknown alarm")
    configuration = get_alert_configuration(alarm_name)
    environment = get_environment(alarm_name, configuration)

    title = configuration.get("title", alarm_name)
    description = configuration.get(
        "description",
        message.get("AlarmDescription", "A CloudWatch alarm has changed state."),
    )

    logs = configuration.get("logs") if configuration else DEFAULT_LOGS
    log_url = (
        get_log_url(message, region, environment, logs)
        if logs
        else None
    )

    sections = [
        f":warning: *ALARM - {alarm_name}* :x:",
        f"*Alert:* {title}",
        f"*Environment:* {environment}",
        f"*Summary:* {description}",
    ]

    investigation_steps = configuration.get("investigation_steps", [])
    if investigation_steps:
        steps = "\n".join(
            f"{number}. {step}"
            for number, step in enumerate(investigation_steps, start=1)
        )
        sections.append(f"*What to do:*\n{steps}")

    links = [
        create_link("View matching logs", log_url),
        create_link("View alarm", get_alarm_url(region, alarm_name)),
        create_link("Documentation", configuration.get("confluence_link")),
        create_link("Terraform", configuration.get("terraform_link")),
    ]

    sections.append("*Links:* " + " | ".join(link for link in links if link))
    logger.info("Processed alarm %s", alarm_name)

    return "\n\n".join(sections)


def get_secret_value(secret_key: str) -> str:
    response = secrets_client.get_secret_value(SecretId="slack-webhooks")
    return json.loads(response["SecretString"])[secret_key]


def send_to_slack(webhook_url: str, text: str, emoji: str):
    payload = {"username": "aws", "icon_emoji": emoji, "text": text}
    data = urllib.parse.urlencode({"payload": json.dumps(payload)}).encode("utf-8")
    request = urllib.request.Request(webhook_url, data=data)

    with urllib.request.urlopen(request) as response:
        return {"status": response.getcode()}


REASONS_FOR_CRITICAL_ALARM = [
    "Production-availability-service",
    "production-5xx-errors",
    "production-response-time",
    "sirius-unavailable",
    "sirius-login",
    "availability",
    "guardduty-findings",
]


ROUTES = {
    "secret_alert": {
        "secret_key": "opg-digideps-team",
        "emoji": ":rotating_light:",
        "template": lambda event: (
            ":rotating_light: *SECRET UPLOADED DETECTED* :rotating_light:\n\n"
            f"*Branch:* {event.get('branch', 'unknown')}\n"
            f"*User:* {event.get('user', 'unknown')}\n"
            f"*GH Actions build url:* {event.get('gh_url', 'N/A')}\n\n"
            f"*Commit message:* {event.get('commit_message', '')}"
        ),
    },
    "workflow": {
        "secret_key": "opg-digideps-builds",
        "emoji": None,
        "template": lambda event: (
            f":pipeline: *Serve OPG Workflow {event.get('status', 'unknown').capitalize()} "
            f"{'✅' if event.get('status', '').lower() == 'success' else '❌'}*\n\n"
            f"*Branch:* {event.get('branch', 'unknown')}\n"
            f"*Workflow:* {event.get('workflow', 'unknown')}\n"
            f"*User:* {event.get('user', 'unknown')}\n\n"
            f"*Links*\n"
            f"  • *GH Actions build url:* {event.get('gh_url', 'N/A')}\n\n"
            f"*Commit message:* {event.get('commit_message', '')}"
        ),
    },
    "alarm": {
        "secret_key": "opg-default",
        "emoji": ":warning:",
        "template": lambda event: alarm_message(
            event, event.get("Region", "eu-west-1")
        ),
    },
    "critical_alarm": {
        "secret_key": "opg-default",
        "emoji": ":warning:",
        "template": lambda event: alarm_message(
            event, event.get("Region", "eu-west-1")
        ),
    },
    "breakglass": {
        "secret_key": "opg-default",
        "emoji": ":rotating_light:",
        "template": lambda event: (
            ":rotating_light: *BREAKGLASS ACCESS IN SERVE* :rotating_light:\n\n"
            "A breakglass role has been assumed. If you are this user, "
            "please reply under this message with the reason for access."
        ),
    },
}


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

                topic_arn = sns.get("TopicArn", "")
                if not isinstance(topic_arn, str):
                    continue

                if "custom_cloudwatch_alarms" in topic_arn and "breakglass" not in raw_message:
                    continue

                try:
                    parsed = json.loads(raw_message)
                except json.JSONDecodeError as error:
                    print(f"Failed to parse SNS message as JSON: {error}")
                    continue

                if not isinstance(parsed, dict):
                    continue

                message = parsed

                if "AlarmName" in parsed:
                    alarm_name = parsed.get("AlarmName", "")
                    alarm_state = parsed.get("NewStateValue", "")

                    if alarm_state != "ALARM":
                        print(f"Ignoring {alarm_state} Slack notification for {alarm_name}")
                        return {"status": 200}

                    if "breakglass" in alarm_name:
                        event_type = "breakglass"
                    elif any(
                        reason.lower() in alarm_name.lower()
                        for reason in REASONS_FOR_CRITICAL_ALARM
                    ):
                        event_type = "critical_alarm"
                    else:
                        event_type = "alarm"
                else:
                    event_type = parsed.get("type", "")

                break
    except Exception as error:
        print(f"Unexpected error while processing event: {error}")

    if not event_type:
        event_type = message.get("type", "") if isinstance(message, dict) else ""

    if not event_type or event_type not in ROUTES:
        raise ValueError(f"Unknown event type: {event_type}, message: {message}")

    route = ROUTES[event_type]
    text = route["template"](message)
    emoji = route["emoji"] or (
        "✅" if message.get("status", "").lower() == "success" else "❌"
    )
    webhook_url = get_secret_value(route["secret_key"])

    print(f"Sending to Slack: {text}")

    return send_to_slack(webhook_url, text, emoji)
