resource "aws_ecs_cluster" "serve_opg" {
  name = local.environment
}

resource "aws_ecs_service" "frontend" {
  name                  = "frontend"
  cluster               = aws_ecs_cluster.serve_opg.id
  task_definition       = aws_ecs_task_definition.frontend.arn
  desired_count         = 1
  launch_type           = "FARGATE"
  wait_for_steady_state = true

  network_configuration {
    security_groups  = [aws_security_group.frontend.id]
    subnets          = data.aws_subnet.application[*].id
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.frontend_tg.arn
    container_name   = "web"
    container_port   = 80
  }
}

resource "aws_cloudwatch_log_group" "serve" {
  name              = local.environment
  retention_in_days = 180
}

resource "aws_security_group" "frontend" {
  name        = "frontend-${local.environment}"
  vpc_id      = data.aws_vpc.main.id
  tags        = local.default_tags

  ingress {
    protocol        = "tcp"
    from_port       = 80
    to_port         = 80
    security_groups = [aws_security_group.elastic_load_balancer.id]
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
