#!/bin/sh

SQL_FILE="/usr/local/bin/create_readonly_user.sql"


# Export the database password for psql
export PGPASSWORD="$DC_DB_PASS"

# Create a temporary file for the modified SQL
temp_file=$(mktemp)

sed "s/string-to-replace-with-local-environment/$WORKSPACE/g" "$SQL_FILE" > "$temp_file"

echo "Executing SQL to create readonly user for workspace: $WORKSPACE"

# Run the modified SQL file
psql -h "$DC_DB_HOST" -U "$DC_DB_USER" -d psql -p "$DC_DB_PORT" -f "$temp_file"

 # Check for errors
if [ $? -ne 0 ]; then
    echo "Error occurred while executing SQL. Exiting."
    rm "$temp_file" # Remove the temp file if an error occurs
    exit 1
fi

# Unset the password environment variable
unset PGPASSWORD