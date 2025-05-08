#!/bin/sh

# Usage function
usage() {
    echo "Usage: $0 -h <hostname> -p <port> -t <timeout>"
    exit 1
}

# Default timeout value
TIMEOUT=60

# Parse command-line arguments
while getopts "h:p:t:" opt; do
  case "$opt" in
    h) HOSTNAME=$OPTARG ;;
    p) PORT=$OPTARG ;;
    t) TIMEOUT=$OPTARG ;;
    *) usage ;;
  esac
done

# Validate inputs
if [[ -z "$HOSTNAME" || -z "$PORT" ]]; then
    echo "Error: Missing required arguments."
    usage
fi

# Start timer
START_TIME=$(date +%s)

# Wait for database to become available
while ! nc -z "$HOSTNAME" "$PORT"; do
    echo "Waiting for server at $HOSTNAME:$PORT..."
    sleep 2

    if [ $(( $(date +%s) - START_TIME )) -ge "$TIMEOUT" ]; then
        echo "Timeout reached after $TIMEOUT seconds. Server did not respond."
        exit 1
    fi
done

echo "Server is now reachable!"
exit 0
