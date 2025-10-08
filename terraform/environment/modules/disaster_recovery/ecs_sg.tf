resource "aws_security_group" "ecs_service" {
  name        = "backup-cross-account-${var.environment}"
  description = "Cross Account Backup Service"
  vpc_id      = var.aws_vpc_id
  tags        = var.default_tags

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
