# Custom Alerting

This folder contains JSON configuration files used to produce clearer Slack notifications for Serve CloudWatch alarms.

Each JSON file represents one alarm. Alarm files are grouped by application, availability, database or infrastructure.

## Alarm matching

The `name` property must match the CloudWatch alarm name without its environment or numeric instance prefix. This is how the Lambda knows what alert is for what.

## Configuration fields

- `name`: CloudWatch alarm name or stable suffix. (MUST BE THE SAME AS DEFINED IN TF)
- `title`: Title displayed in Slack.
- `description`: Explanation of what the alarm means.
- `environment_position`: Position of the environment in the alarm name when it is not the first part. (Optional)
- `investigation_steps`: Initial investigation and incident guidance.
- `logs`: CloudWatch Logs Insights link configuration. Optional, only needed when it would be helpful.
- `confluence_link`: Alarm documentation link.
- `terraform_link`: Terraform definition link.
