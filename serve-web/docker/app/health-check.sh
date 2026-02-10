#!/bin/sh
set -e

if REQUEST_URI=/login SCRIPT_NAME=/login SCRIPT_FILENAME=/var/www/public/index.php REMOTE_ADDR=127.0.0.1 REQUEST_METHOD=GET SERVER_NAME=app cgi-fcgi -bind -connect 127.0.0.1:9000; then
	exit 0
fi

exit 1
