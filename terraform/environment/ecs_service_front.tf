resource "aws_iam_service_linked_role" "ecs" {
  aws_service_name = "ecs.amazonaws.com"
}

resource "aws_ecs_cluster" "serve_opg" {
  name       = local.environment
  depends_on = [aws_iam_service_linked_role.ecs]
}

resource "aws_ecs_service" "frontend" {
  name                  = "frontend"
  cluster               = aws_ecs_cluster.serve_opg.id
  task_definition       = aws_ecs_task_definition.frontend.arn
  desired_count         = 1
  launch_type           = "FARGATE"
  wait_for_steady_state = true

  network_configuration {
    security_groups  = [aws_security_group.ecs_service.id]
    subnets          = data.aws_subnet.private[*].id
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.frontend.arn
    container_name   = "web"
    container_port   = 80
  }
}

resource "aws_cloudwatch_log_group" "serve" {
  name              = local.environment
  retention_in_days = 180
}

resource "aws_security_group" "ecs_service" {
  name   = "frontend-${local.environment}"
  vpc_id = data.aws_vpc.vpc.id
  tags   = local.default_tags

  ingress {
    protocol        = "tcp"
    from_port       = 80
    to_port         = 80
    security_groups = [aws_security_group.load_balancer.id]
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
