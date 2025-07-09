#!/bin/sh

SQL_FILE="./scripts/database/create_readonly_user.sql"

# Export the database password for psql
export PGPASSWORD="$DATABASE_PASSWORD"

# Create a temporary file for the modified SQL
temp_file=$(mktemp)

sed "s/string-to-replace-with-local-environment/$WORKSPACE/g" "$SQL_FILE" > "$temp_file"

echo "Executing SQL to create readonly user for workspace: $WORKSPACE"

# Run the modified SQL file
psql -h "$DATABASE_HOSTNAME" -U "$DATABASE_USERNAME" -d "$DATABASE_NAME" -p "$DATABASE_PORT" -f "$temp_file"

 # Check for errors
if [ $? -ne 0 ]; then
    echo "Error occurred while executing SQL. Exiting."
    rm "$temp_file" # Remove the temp file if an error occurs
    exit 1
fi

# Unset the password environment variable
unset PGPASSWORD