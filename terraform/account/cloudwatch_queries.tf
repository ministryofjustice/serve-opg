resource "aws_cloudwatch_query_definition" "non_healthcheck_requests" {
  name            = "Analysis/App-Services-No-Healthchecks"
  log_group_names = [local.account.name]

  query_string = <<QUERY
# Purpose: General view of logs without the healthchecks and base url
# Usage: Useful for general view of what's happening with less noise
fields @timestamp, service_name, request_uri, status, @message
| sort @timestamp desc
| filter ispresent(service_name)
| filter request_uri NOT LIKE /health-check/
| filter request_uri != "/"
QUERY
}

resource "aws_cloudwatch_query_definition" "errors" {
  name            = "Analysis/Error-Exceptions"
  log_group_names = [local.account.name]

  query_string = <<QUERY
# Purpose: Display application errors from logs
# Usage: Useful for diagnosing application problems
fields @timestamp, level, message, @message
| sort @timestamp desc
| filter tolower(@message) like /error|exception|critical/
QUERY
}

resource "aws_cloudwatch_query_definition" "slow_response_times" {
  name            = "Analysis/Requests-With-Slow-Response-Times"
  log_group_names = [local.account.name]

  query_string = <<QUERY
# Purpose: Shows requests with response time of more than 2 seconds
# Usage: Identify which areas of the app are performing slowly
fields @timestamp, service_name, upstream_response_time, request_uri, status, real_forwarded_for
| filter upstream_response_time > 2.0
| sort upstream_response_time desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "status_5xx" {
  name            = "Analysis/Requests-With-5xx-Status"
  log_group_names = [local.account.name]

  query_string = <<QUERY
# Purpose: 5xx webserver responses and messages that contain error strings
# Usage: Look for 5xx errors in status column and find likely related errors with similar timestamp
fields @timestamp, @logStream, status, service_name, request_uri, message, @message
| filter ((!ispresent(status) and tolower(@message) like /exception|error|critical/ and @message not like /NOTICE|open()/) or status > 499)
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "status_4xx" {
  name            = "Analysis/Requests-With-4xx-Status"
  log_group_names = [local.account.name]

  query_string = <<QUERY
# Purpose: 4xx webserver responses
# Usage: Look for unusual request_uris or increases of particular status over time
fields @timestamp, status, service_name, request_uri, upstream_response_time
| filter (status > 399 and status < 500)
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "response_distribution" {
  name            = "Analysis/Response-Distribution-By-Status"
  log_group_names = [local.account.name]

  query_string = <<QUERY
# Purpose: Get an idea of response distribution compared to baseline
# Usage: Run against set timeframe now and similar timeframe from the day before and compare
fields service_name, status
| stats count() as count by service_name, status
| sort by service_name, status
QUERY
}
