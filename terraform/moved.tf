moved {
  from = aws_cloudwatch_log_group.frontend
  to   = aws_cloudwatch_log_group.serve
}

moved {
  from = aws_lb.loadbalancer
  to   = aws_lb.frontend
}
