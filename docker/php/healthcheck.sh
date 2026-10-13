#!/bin/sh
# Liveness check for php-fpm: talks FastCGI to the pool's ping endpoint.
SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
  cgi-fcgi -bind -connect 127.0.0.1:9000 2>/dev/null | grep -q pong
