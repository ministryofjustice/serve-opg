## Backup and Restore via SQL dump

It's a lot easier to get just the data out of the DB rather than
restore a snapshot with all the varying options that the cluster could have.

As such we have a process that backs up to an s3 bucket and restores from the s3 bucket.

It does this by running scripts in an ECS task that do the backup and the restore.

The bucket where the backups go is called `[local env name]`.orchestration.serve.opg.digital

To perform this action you need to use the ecs-helper tool. To install it locally do the following:

```
wget "https://github.com/ministryofjustice/opg-ecs-helper/releases/download/v$HELPER_VERSION/opg-ecs-helper_Linux_x86_64.tar.gz"
mkdir ecs-helper
tar -xvf opg-ecs-helper_Linux_x86_64.tar.gz -C ecs-helper
chmod +x ecs-helper/ecs-runner
```

This is in the `.gitignore` so just make sure you don't commit this.

Once installed you need to switch to go into `terraform\environment` and do the following:

```
export TF_WORKSPACE=the_environment_identifier_i_want_to_work_with
export TASK_NAME=backup
export TIMEOUT=3600
aws-vault exec identity -- terraform apply
aws-vault exec identity -- terraform output -json > terraform.output.json
aws-vault exec identity -- ../../ecs-helper/runner -task ${TASK_NAME} -timeout ${TIMEOUT}
```

You can then restore to the same environment by changing the task_name to restore.

## How this works

The ECS helper basically uses the config from your terraform output to know how to
spin up the ECS task. The ECS task has the correct restricted permissions to access everything
it needs and runs the named task as part of the ECS cluster and outputs the output to the screen.
