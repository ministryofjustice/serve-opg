module "ssm_ec2_instance_operator" {
  source           = "./modules/ssm_ec2_instance"
  instance_type    = "t3.micro"
  subnet_id        = aws_subnet.private[0].id
  name             = "operator"
  tags             = local.default_tags
  instance_profile = data.aws_iam_instance_profile.operator.name
  vpc_id           = aws_default_vpc.default.id
}

data "aws_iam_instance_profile" "operator" {
  name = "operator"
}

data "aws_iam_role" "operator" {
  name = "operator"
}

# Attach AmazonSSMManagedInstanceCore to role
resource "aws_iam_role_policy_attachment" "ssm_core_role_policy_document" {
  role       = data.aws_iam_role.operator.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

# Policy Document for EC2 Start/Stop
data "aws_iam_policy_document" "start_ec2" {
  statement {
    sid     = "AllowStartStopSpecificInstance"
    effect  = "Allow"
    actions = [
      "ec2:StartInstances",
      "ec2:StopInstances",
    ]
    resources = [module.ssm_ec2_instance_operator.ssm_instance_arn]
  }
}

# Create and attach custom EC2 control policy
resource "aws_iam_policy" "start_ec2" {
  name   = "operator-ssm-policy"
  policy = data.aws_iam_policy_document.start_ec2.json
}

resource "aws_iam_role_policy_attachment" "start_ec2" {
  role       = data.aws_iam_role.operator.name
  policy_arn = aws_iam_policy.start_ec2.arn
}