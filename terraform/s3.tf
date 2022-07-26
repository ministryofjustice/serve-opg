resource "aws_s3_bucket" "bucket" {
  bucket = local.bucket_name
  acl    = "private"
  tags   = local.default_tags

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "aws:kms"
      }
    }
  }

  versioning {
    enabled = true
  }

  logging {
    target_bucket = aws_s3_bucket.s3_access_logs.id
    target_prefix = "serve/"
  }

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_s3_bucket_public_access_block" "bucket" {
  bucket = aws_s3_bucket.bucket.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket" "logs" {
  bucket = "logs.${local.bucket_name}"
  acl    = "private"
  policy = data.aws_iam_policy_document.logs.json
  tags   = local.default_tags

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "aws:kms"
      }
    }
  }

  versioning {
    enabled = true
  }

  logging {
    target_bucket = aws_s3_bucket.s3_access_logs.id
    target_prefix = "elb/"
  }

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_s3_bucket_public_access_block" "logs" {
  bucket = aws_s3_bucket.logs.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

data "aws_elb_service_account" "main" {
}

data "aws_iam_policy_document" "logs" {
  statement {
    sid       = "allowLoadBalancerDelivery"
    actions   = ["s3:PutObject"]
    resources = ["arn:aws:s3:::logs.${local.bucket_name}/loadbalancer/*"]

    principals {
      type        = "AWS"
      identifiers = [data.aws_elb_service_account.main.arn]
    }
  }
}

resource "aws_s3_bucket" "s3_access_logs" {
  bucket = "s3-logging.${local.bucket_name}"
  acl    = "log-delivery-write"

  versioning {
    enabled = true
  }

  lifecycle_rule {
    transition {
      days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_transition {
      days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_expiration {
      days = 180
    }

    expiration {
      days                         = 180
      expired_object_delete_marker = true
    }

    enabled = true
  }
}

resource "aws_s3_bucket_public_access_block" "s3_access_logs" {
  bucket = aws_s3_bucket.logs.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}