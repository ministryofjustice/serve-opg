module "ssm_ec2_instance_data_access" {
  source           = "./modules/ssm_ec2_instance"
  instance_type    = "t3.micro"
  subnet_id        = module.network.application_subnets[0].id
  name             = "data-access"
  tags             = local.default_tags
  instance_profile = data.aws_iam_instance_profile.data_access.name
  vpc_id           = module.network.vpc.id
}

data "aws_iam_instance_profile" "data_access" {
  name = "data-access"
}

data "aws_iam_role" "data_access" {
  name = "data-access"
}

# Attach AmazonSSMManagedInstanceCore to role
resource "aws_iam_role_policy_attachment" "ssm_core_policy_document" {
  role       = data.aws_iam_role.data_access.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

# Policy Document for EC2 Start/Stop
data "aws_iam_policy_document" "start_ec2_instance" {
  statement {
    sid    = "AllowStartStopSpecificInstance"
    effect = "Allow"
    actions = [
      "ec2:StartInstances",
      "ec2:StopInstances",
    ]
    resources = [module.ssm_ec2_instance_data_access.ssm_instance_arn]
  }
}

# Create and attach custom EC2 control policy
resource "aws_iam_policy" "start_ec2_instance" {
  name   = "data-access-ssm"
  policy = data.aws_iam_policy_document.start_ec2_instance.json
}

resource "aws_iam_role_policy_attachment" "start_ec2_instance" {
  role       = data.aws_iam_role.data_access.name
  policy_arn = aws_iam_policy.start_ec2_instance.arn
}
