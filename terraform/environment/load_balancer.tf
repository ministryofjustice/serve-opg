# ===== Healthcheck security group rules =====
data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

locals {
  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks
}

resource "aws_lb" "frontend_lb" {
  name                       = "frontend-elb-${local.environment}"
  load_balancer_type         = "application"
  subnets                    = data.aws_subnet.load_balancer[*].id
  security_groups            = [aws_security_group.elastic_load_balancer.id, aws_security_group.load_balancer_health.id]
  tags                       = local.default_tags
  drop_invalid_header_fields = true

  access_logs {
    bucket  = aws_s3_bucket.logs.bucket
    prefix  = "loadbalancer"
    enabled = true
  }
}

resource "aws_lb_target_group" "frontend_tg" {
  port                 = 80
  protocol             = "HTTP"
  target_type          = "ip"
  vpc_id               = data.aws_vpc.main.id
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

resource "aws_lb_listener" "frontend_listen" {
  load_balancer_arn = aws_lb.frontend_lb.arn
  port              = 443
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS13-1-2-2021-06"
  certificate_arn   = aws_acm_certificate.serve.arn
  depends_on        = [aws_route53_record.serve_validation]

  default_action {
    target_group_arn = aws_lb_target_group.frontend_tg.arn
    type             = "forward"
  }
}

resource "aws_security_group" "elastic_load_balancer" {
  name   = "load-balancer-${local.environment}"
  vpc_id = data.aws_vpc.main.id
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

# New SG as large number of cidr ranges
resource "aws_security_group" "load_balancer_health" {
  name   = "load-balancer-hc-${local.environment}"
  vpc_id = data.aws_vpc.main.id
  tags   = local.default_tags
  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "front_elb_route53_health_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = aws_security_group.load_balancer_health.id
  cidr_blocks       = local.route53_healthchecker_ips
  description       = "Route53 Healthcheck to Front LB"
}
