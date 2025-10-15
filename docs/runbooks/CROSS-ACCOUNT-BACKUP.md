# 🗄️ Cross-Account RDS Snapshot Backup Script

This repository contains a Python script designed to **automate cross-account Amazon RDS snapshot backups**.
It performs the following operations safely and idempotently:

1. Identifies the latest **automated RDS or Aurora snapshot**.
2. Copies it to a **manual snapshot** encrypted with a provided KMS key.
3. **Shares** the snapshot with a **backup AWS account**.
4. Assumes a role in that backup account.
5. Copies the shared snapshot into the backup account’s environment using its own KMS key.
6. Cleans up:
   - Deletes older manual snapshots, keeping only a defined number (default: 7).
   - Removes temporary shared snapshots once backups complete.

---

## ⚙️ How It Works

This script is executed **nightly via an AWS EventBridge rule**, which triggers a **containerised task** that:

- Spins up a minimal Python container including `boto3` and the backup script.
- Runs the script once per schedule.
- Terminates automatically upon completion.

---

## 🧩 Key Features

- ✅ Works for both **RDS Instances** and **Aurora Clusters** (`cluster=True|False`).
- 🔒 Uses **KMS encryption keys** for secure cross-account transfers.
- 🔁 Maintains only the most recent **N manual backups** (default: 7).
- 🧹 Automatically **cleans up** old snapshots.
- 🔄 Supports **cross-account sharing** using IAM role assumption.

---

## 🧰 Environment Variables

The script is configured entirely via environment variables.
The container or ECS task must provide these values at runtime:

| Variable | Description | Example |
|-----------|-------------|----------|
| `DB_ID` | RDS DB instance or cluster identifier | `my-production-db` |
| `SOURCE_ACCOUNT` | AWS account ID that owns the source RDS | `123456789012` |
| `KMS_KEY_ID` | KMS Key ID in the source account used to encrypt manual snapshots | `abcd-1234-efgh-5678` |
| `BACKUP_ACCOUNT` | AWS account ID that will receive the shared snapshot | `210987654321` |
| `BACKUP_ACCOUNT_ROLE` | Full ARN of the IAM role to assume in the backup account | `arn:aws:iam::210987654321:role/BackupSnapshotRole` |
| `CLUSTER` | `"true"` if backing up an Aurora cluster, `"false"` for a single RDS instance | `true` |

### Optional / Derived Values

| Variable | Purpose |
|-----------|----------|
| `AWS_REGION` | Region to operate in (defaults to `eu-west-1`) |
| `BACKUPS_TO_KEEP` | Number of manual snapshots to retain (default: `7`) |

---

## 🕒 Execution Flow

1. EventBridge triggers the backup container nightly.
2. The container starts and runs `python cross_account_backup.py`.
3. The script:
   - Finds the latest snapshot.
   - Creates an encrypted manual copy.
   - Shares it with the backup account.
   - Assumes the target backup role.
   - Copies it into the backup account.
   - Deletes temporary shared and aged backups.
4. Logging output is sent to CloudWatch for auditing and monitoring.

---
