moved {
  from = aws_cloudwatch_log_group.frontend
  to   = aws_cloudwatch_log_group.serve
}

moved {
  from = aws_lb.loadbalancer
  to   = aws_lb.frontend
}

moved {
  from = aws_security_group.loadbalancer
  to   = aws_security_group.load_balancer
}

moved {
  from = aws_lb_listener.loadbalancer
  to   = aws_lb_listener.frontend
}

moved {
  from = aws_lb.loadbalancer
  to   = aws_lb.frontend
}

moved {
  from = aws_lb_target_group.loadbalancer
  to   = aws_lb_target_group.frontend
}
