resource "aws_cloudwatch_dashboard" "main" {
  dashboard_name = "Serve_OPG"

  dashboard_body = <<EOF
  {
      "widgets": [
          {
              "type": "metric",
              "x": 0,
              "y": 5,
              "width": 3,
              "height": 3,
              "properties": {
                  "metrics": [
                      [ "AWS/ApplicationELB", "RequestCount", "TargetGroup", "${aws_lb_target_group.frontend.arn_suffix}", "LoadBalancer", "${aws_lb.frontend.arn_suffix}", { "stat": "Sum", "period": 86400, "label": "requests" } ]
                  ],
                  "view": "singleValue",
                  "stacked": false,
                  "region": "eu-west-1",
                  "legend": {
                      "position": "bottom"
                  },
                  "period": 300,
                  "title": "Requests",
                  "setPeriodToTimeRange": true
              }
          },
          {
              "type": "metric",
              "x": 3,
              "y": 2,
              "width": 21,
              "height": 15,
              "properties": {
                  "metrics": [
                      [ { "expression": "SUM([tg5,lb5])", "label": "5xx Errors", "id": "s5", "color": "#d62728" } ],
                      [ { "expression": "SUM([tg4,lb4])", "label": "4xx Errors", "id": "s4" } ],
                      [ "AWS/ApplicationELB", "RequestCount", "TargetGroup", "${aws_lb_target_group.frontend.arn_suffix}", "LoadBalancer", "${aws_lb.frontend.arn_suffix}", { "stat": "Sum", "period": 300, "label": "Total Requests", "id": "r1", "color": "#2ca02c" } ],
                      [ ".", "TargetResponseTime", ".", ".", ".", ".", { "period": 300, "stat": "p95", "color": "#1f77b4", "yAxis": "right", "label": "Response Time", "id": "r2" } ],
                      [ ".", "HTTPCode_Target_4XX_Count", ".", ".", ".", ".", { "yAxis": "left", "stat": "Sum", "period": 300, "label": "TG 4xx Errors", "color": "#ff7f0e", "id": "tg4", "visible": false } ],
                      [ ".", "HTTPCode_Target_5XX_Count", ".", ".", ".", ".", { "color": "#d62728", "stat": "Sum", "period": 300, "label": "TG 5xx Errors", "id": "tg5", "visible": false } ],
                      [ ".", "HTTPCode_ELB_4XX_Count", "LoadBalancer", "${aws_lb.frontend.arn_suffix}", { "period": 300, "stat": "Sum", "id": "lb4", "visible": false, "label": "ALB 4xx Errors" } ],
                      [ ".", "HTTPCode_ELB_5XX_Count", ".", ".", { "period": 300, "stat": "Sum", "yAxis": "left", "label": "ALB 5xx Errors", "id": "lb5", "visible": false } ]
                  ],
                  "view": "timeSeries",
                  "stacked": false,
                  "region": "eu-west-1",
                  "legend": {
                      "position": "bottom"
                  },
                  "period": 300,
                  "title": "Requests"
              }
          },
          {
              "type": "metric",
              "x": 3,
              "y": 0,
              "width": 21,
              "height": 2,
              "properties": {
                  "title": "Availability",
                  "annotations": {
                      "alarms": [
                          "${aws_cloudwatch_metric_alarm.availability_24h.arn}"
                      ]
                  },
                  "view": "timeSeries",
                  "stacked": false
              }
          },
          {
              "type": "metric",
              "x": 0,
              "y": 11,
              "width": 3,
              "height": 3,
              "properties": {
                  "metrics": [
                      [ "AWS/ApplicationELB", "HTTPCode_Target_4XX_Count", "TargetGroup", "${aws_lb_target_group.frontend.arn_suffix}", "LoadBalancer", "${aws_lb.frontend.arn_suffix}", { "period": 86400, "stat": "Sum", "label": "4xx errors" } ]
                  ],
                  "view": "singleValue",
                  "region": "eu-west-1",
                  "title": "4xx Errors",
                  "period": 300,
                  "setPeriodToTimeRange": true
              }
          },
          {
              "type": "metric",
              "x": 0,
              "y": 8,
              "width": 3,
              "height": 3,
              "properties": {
                  "title": "TG 5xx Errors",
                  "annotations": {
                      "alarms": [
                          "${aws_cloudwatch_metric_alarm.errors_24h.arn}"
                      ]
                  },
                  "view": "singleValue",
                  "setPeriodToTimeRange": true
              }
          },
          {
              "type": "metric",
              "x": 0,
              "y": 8,
              "width": 3,
              "height": 3,
              "properties": {
                  "title": "LB 5xx Errors",
                  "annotations": {
                      "alarms": [
                          "${aws_cloudwatch_metric_alarm.alb_errors_24h.arn}"
                      ]
                  },
                  "view": "singleValue",
                  "setPeriodToTimeRange": true
              }
          },
          {
              "type": "metric",
              "x": 0,
              "y": 2,
              "width": 3,
              "height": 3,
              "properties": {
                  "title": "Response Time",
                  "annotations": {
                      "alarms": [
                          "${aws_cloudwatch_metric_alarm.response_time.arn}"
                      ]
                  },
                  "view": "singleValue",
                  "setPeriodToTimeRange": true
              }
          }
      ]
  }

EOF

}

data "aws_sns_topic" "alert" {
  name = "serve-opg-${local.account.account_name}-app-alert"
}

data "aws_sns_topic" "alert_us_east" {
  provider = aws.us-east-1
  name     = "serve-opg-${local.account.account_name}-alert"
}
