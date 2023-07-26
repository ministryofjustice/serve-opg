resource "aws_lb" "loadbalancer" {
  subnets         = aws_default_subnet.public.*.id
  security_groups = [aws_security_group.loadbalancer.id]
  tags            = local.default_tags

  access_logs {
    bucket  = aws_s3_bucket.logs.bucket
    prefix  = "loadbalancer"
    enabled = true
  }
}

resource "aws_lb_target_group" "frontend" {
  port                 = 80
  protocol             = "HTTP"
  target_type          = "ip"
  vpc_id               = aws_default_vpc.default.id
  deregistration_delay = 0
  tags                 = local.default_tags

  health_check {
    path     = "/health-check"
    interval = 10
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_lb_listener" "loadbalancer" {
  load_balancer_arn = aws_lb.loadbalancer.arn
  port              = 443
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-2017-01"
  certificate_arn   = aws_acm_certificate.serve.arn
  depends_on        = [aws_route53_record.serve_validation]

  default_action {
    target_group_arn = aws_lb_target_group.frontend.arn
    type             = "forward"
  }
}

resource "aws_security_group" "loadbalancer" {
  name   = "loadbalancer"
  vpc_id = aws_default_vpc.default.id
  tags   = local.default_tags

  ingress {
    protocol    = "tcp"
    from_port   = 443
    to_port     = 443
    cidr_blocks = local.default_allow_list
  }

  egress {
    protocol    = "-1"
    from_port   = 0
    to_port     = 0
    cidr_blocks = ["0.0.0.0/0"]
  }

  lifecycle {
    create_before_destroy = true
  }
}

