# Slack Notifications

This service uses an AWS Lambda function called **serve-opg-slack** to handle notifications sent to Slack.

The Lambda consumes JSON messages from SNS topics that are subscribed to it. Each incoming message is parsed to determine what type of notification should be sent and which Slack channel it should be delivered to.

Within the Lambda’s Python code, there is a table named **ROUTES**. This table defines:
- Which Slack webhook secret to retrieve from AWS Secrets Manager
- Which Slack channel to notify (based on the webhook)
- The message template used for the notification, which can be customised per notification

The Slack webhooks are stored in Secrets Manager under the **slack-webhooks** secret.

---

## Adding a New Notification

### 1. Create or Use an SNS Topic
You must have a way to publish a JSON message to SNS.
- Either reuse an existing SNS topic, or
- Create a new SNS topic and subscribe the **serve-opg-slack** Lambda to it

All SNS topics and subscriptions must be created and managed via Terraform.

---

### 2. Add a New Route
Edit the Lambdas Python script and add a new entry to the **ROUTES** table.  
Each route must define:
- A key that identifies the notification type
- The name of the Slack webhook secret stored in Secrets Manager
- The message template, including title text and emojis if required

Ensure the referenced webhook exists under the **slack-webhooks** secret in Secrets Manager, otherwise it will error.

---

### 3. Update the Handler Logic
Update the Lambda handler so it can correctly identify the new notification type from the incoming JSON payload and map it to the appropriate route in the **ROUTES** table.

This allows the handler to:
- Select the correct route
- Retrieve the correct webhook
- Format and send the notification to Slack

---

### 4. Test and Deploy
After making changes:
- Test by publishing sample messages to the SNS topic
- Verify behaviour using Lambda logs

This can be done using AWS commands to post json to the SNS topic.
