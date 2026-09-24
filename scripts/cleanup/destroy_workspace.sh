#!/usr/bin/env bash

# A script to destroy a single workspace
# Usage: ./destroy_workspace.sh <workspace_name>

set -Eeuo pipefail

print_usage() {
  echo "Usage: $(basename "$0") [workspace]"
}

if [ $# -eq 0 ]; then
  print_usage
  exit 1
fi

if [ "$1" == "-h" ]; then
  print_usage
  exit 0
fi

workspace_name="$1"
reserved_workspaces="default production preproduction development integration"

for workspace in $reserved_workspaces; do
  if [ "$workspace" == "$workspace_name" ]; then
    echo "protected workspace: $workspace. refusing to destroy."
    exit 1
  fi
done

echo "cleaning up workspace ${workspace_name}..."

terraform init -input=false

if ! terraform workspace select "$workspace_name"; then
  echo "workspace ${workspace_name} does not exist. Assuming it has already been destroyed."
  exit 0
fi

if terraform destroy -auto-approve; then
  terraform workspace select default
  terraform workspace delete "$workspace_name"
else
  echo "terraform destroy failed for workspace ${workspace_name}"
  exit 1
fi
