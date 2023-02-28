resource "aws_s3_bucket" "bucket" {
  bucket = local.bucket_name
  tags   = local.default_tags

  logging {
    target_bucket = aws_s3_bucket.s3_access_logs.id
    target_prefix = "serve/"
  }

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_s3_bucket_acl" "bucket_acl" {
  bucket = aws_s3_bucket.bucket.id
  acl    = "private"
}

resource "aws_s3_bucket_versioning" "bucket_versioning" {
  bucket = aws_s3_bucket.bucket.id

  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "bucket_encryption_configuration" {
  bucket = aws_s3_bucket.bucket.bucket

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

resource "aws_s3_bucket" "logs" {
  bucket = "logs.${local.bucket_name}"
  policy = data.aws_iam_policy_document.logs.json
  tags   = local.default_tags

  logging {
    target_bucket = aws_s3_bucket.s3_access_logs.id
    target_prefix = "elb/"
  }

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_s3_bucket_acl" "logs_acl" {
  bucket = aws_s3_bucket.logs.id
  acl    = "private"
}

resource "aws_s3_bucket_versioning" "logs_versioning" {
  bucket = aws_s3_bucket.logs.id

  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "logs_encryption_configuration" {
  bucket = aws_s3_bucket.logs.bucket

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "aws:kms"
    }
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
}

resource "aws_s3_bucket_acl" "s3_access_logs_acl" {
  bucket = aws_s3_bucket.s3_access_logs.id
  acl    = "log-delivery-write"
}

resource "aws_s3_bucket_versioning" "s3_access_logs_versioning" {
  bucket = aws_s3_bucket.s3_access_logs.id

  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "s3_access_logs_lifecycle" {
  bucket = aws_s3_bucket.s3_access_logs.id

  rule {
    id     = "ExpireObjectsAfter180Days"
    status = "Enabled"

    transition {
      days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_transition {
      noncurrent_days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_expiration {
      noncurrent_days = 180
    }

    expiration {
      days                         = 180
      expired_object_delete_marker = true
    }
  }
}

resource "aws_s3_bucket_public_access_block" "s3_access_logs" {
  bucket = aws_s3_bucket.logs.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}