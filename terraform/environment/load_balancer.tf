resource "aws_lb" "frontend" {
  name               = "frontend-${local.environment}"
  load_balancer_type = "application"
  subnets            = data.aws_subnet.public[*].id
  security_groups    = [aws_security_group.load_balancer.id, aws_security_group.load_balancer_hc.id]
  tags               = local.default_tags

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
  vpc_id               = data.aws_vpc.vpc.id
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

resource "aws_lb_listener" "frontend" {
  load_balancer_arn = aws_lb.frontend.arn
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

resource "aws_security_group" "load_balancer" {
  name   = "load-balancer-${local.environment}"
  vpc_id = data.aws_vpc.vpc.id
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

# ===== Healthcheck security group rules =====
data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

locals {
  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks
}

# New SG as large number of cidr ranges
resource "aws_security_group" "load_balancer_hc" {
  name   = "load-balancer-hc-${local.environment}"
  vpc_id = data.aws_vpc.vpc.id
  tags   = local.default_tags
  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "front_elb_route53_hc_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = aws_security_group.load_balancer_hc.id
  cidr_blocks       = local.route53_healthchecker_ips
  description       = "Route53 Healthcheck to Front LB"
}
