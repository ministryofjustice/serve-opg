data "aws_route53_zone" "opg_service" {
  name     = "opg.service.justice.gov.uk."
  provider = aws.management
}

resource "aws_route53_record" "serve" {
  zone_id = data.aws_route53_zone.opg_service.zone_id
  name    = "${local.dns_prefix}.${data.aws_route53_zone.opg_service.name}"
  type    = "A"

  alias {
    name                   = aws_lb.loadbalancer.dns_name
    zone_id                = aws_lb.loadbalancer.zone_id
    evaluate_target_health = false
  }

  provider = aws.management
}

resource "aws_acm_certificate" "serve" {
  domain_name       = aws_route53_record.serve.fqdn
  validation_method = "DNS"
  tags              = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_route53_record" "serve_validation" {
  name    = sort(aws_acm_certificate.serve.domain_validation_options[*].resource_record_name)[0]
  type    = sort(aws_acm_certificate.serve.domain_validation_options[*].resource_record_type)[0]
  zone_id = data.aws_route53_zone.opg_service.id
  records = [sort(aws_acm_certificate.serve.domain_validation_options[*].resource_record_value)[0]]
  ttl     = 60

  provider = aws.management
}

resource "aws_acm_certificate_validation" "serve" {
  certificate_arn         = aws_acm_certificate.serve.arn
  validation_record_fqdns = [aws_route53_record.serve_validation.fqdn]
}

resource "aws_route53_health_check" "homepage" {
  fqdn              = aws_route53_record.serve.fqdn
  resource_path     = "/"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = local.default_tags
}
