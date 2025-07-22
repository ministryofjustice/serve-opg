#!/bin/sh

# Usage: ./bump-decider.sh "src,config,routes"

FOLDER_LIST="$1"
IFS=','

# Convert to space-separated list
for folder in $FOLDER_LIST; do
  FOLDERS="$FOLDERS $folder"
done

# Get changed files between HEAD and base (adjust `origin/main` if needed)
CHANGED_FILES=$(git diff --name-only origin/main...HEAD)

for file in $CHANGED_FILES; do
  for folder in $FOLDERS; do
    echo $folder
    echo $file
    case "$file" in
      "$folder"/*)
        echo "minor"
        exit 0
        ;;
    esac
  done
done

echo "patch"
