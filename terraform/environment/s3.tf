locals {
  bucket_name = local.environment == "production" ? "serve-opg.opg.digital" : "${local.environment}.serve-opg.opg.digital"
}

data "aws_s3_bucket" "access_logging" {
  bucket = "s3-access-logs-opg-serve-opg-${local.account.account_name}-${data.aws_region.current.name}"
}

# ===== Main bucket =====
resource "aws_s3_bucket" "bucket" {
  bucket = local.bucket_name
  tags   = local.default_tags

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_s3_bucket_logging" "bucket" {
  bucket = aws_s3_bucket.bucket.id

  target_bucket = data.aws_s3_bucket.access_logging.id
  target_prefix = "log/${aws_s3_bucket.bucket.id}/"
}

resource "aws_s3_bucket_ownership_controls" "bucket" {
  bucket = aws_s3_bucket.bucket.id

  rule {
    object_ownership = "BucketOwnerPreferred"
  }
}

resource "aws_s3_bucket_acl" "bucket" {
  depends_on = [aws_s3_bucket_ownership_controls.bucket]
  bucket     = aws_s3_bucket.bucket.id
  acl        = "private"
}

resource "aws_s3_bucket_versioning" "bucket" {
  bucket = aws_s3_bucket.bucket.id

  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "bucket" {
  bucket = aws_s3_bucket.bucket.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "aws:kms"
    }
  }
}

resource "aws_s3_bucket_public_access_block" "bucket" {
  bucket = aws_s3_bucket.bucket.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

# ===== ELB bucket =====
resource "aws_s3_bucket" "logs" {
  bucket = "logs.${local.bucket_name}"
  tags   = local.default_tags

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_s3_bucket_logging" "logs" {
  bucket = aws_s3_bucket.logs.id

  target_bucket = data.aws_s3_bucket.access_logging.id
  target_prefix = "log/${aws_s3_bucket.logs.id}/"
}

resource "aws_s3_bucket_ownership_controls" "logs" {
  bucket = aws_s3_bucket.logs.id

  rule {
    object_ownership = "BucketOwnerPreferred"
  }
}

resource "aws_s3_bucket_acl" "logs" {
  depends_on = [aws_s3_bucket_ownership_controls.logs]
  bucket     = aws_s3_bucket.logs.id
  acl        = "private"
}

resource "aws_s3_bucket_versioning" "logs" {
  bucket = aws_s3_bucket.logs.id

  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "logs" {
  bucket = aws_s3_bucket.logs.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "aws:kms"
    }
  }
}

data "aws_elb_service_account" "main" {
}

resource "aws_s3_bucket_policy" "bucket" {
  depends_on = [aws_s3_bucket_public_access_block.logs]
  bucket     = aws_s3_bucket.logs.id
  policy     = data.aws_iam_policy_document.logs.json
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

resource "aws_s3_bucket_public_access_block" "logs" {
  bucket = aws_s3_bucket.logs.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

## ===== S3 logging bucket =====
#resource "aws_s3_bucket" "s3_access_logs" {
#  bucket = "s3-logging.${local.bucket_name}"
#}
#
#resource "aws_s3_bucket_ownership_controls" "s3_access_logs" {
#  bucket = aws_s3_bucket.logs.id
#
#  rule {
#    object_ownership = "BucketOwnerPreferred"
#  }
#}
#
#resource "aws_s3_bucket_acl" "s3_access_logs" {
#  bucket = aws_s3_bucket.s3_access_logs.id
#  acl    = "log-delivery-write"
#}
#
#resource "aws_s3_bucket_versioning" "s3_access_logs" {
#  bucket = aws_s3_bucket.s3_access_logs.id
#
#  versioning_configuration {
#    status = "Enabled"
#  }
#}
#
#resource "aws_s3_bucket_lifecycle_configuration" "s3_access_logs" {
#  bucket = aws_s3_bucket.s3_access_logs.id
#
#  rule {
#    id     = "ExpireObjectsAfter180Days"
#    status = "Enabled"
#
#    transition {
#      days          = 30
#      storage_class = "GLACIER"
#    }
#
#    noncurrent_version_transition {
#      noncurrent_days = 30
#      storage_class   = "GLACIER"
#    }
#
#    noncurrent_version_expiration {
#      noncurrent_days = 180
#    }
#
#    expiration {
#      days                         = 180
#      expired_object_delete_marker = true
#    }
#  }
#}
#
#resource "aws_s3_bucket_public_access_block" "s3_access_logs" {
#  bucket = aws_s3_bucket.logs.id
#
#  block_public_acls       = true
#  block_public_policy     = true
#  ignore_public_acls      = true
#  restrict_public_buckets = true
#}
