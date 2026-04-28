#!/bin/sh
set -e

# Read secret (trim CR/LF) if present
PASS=""
if [ -r "$REDIS_PASS_FILE" ]; then
  PASS="$(tr -d '\r\n' <"$REDIS_PASS_FILE")"
fi

# Build command; if a password was set, add the --requirepass argument to the command
CMD="docker-entrypoint.sh"
if [ -n "$PASS" ]; then
  CMD="$CMD --requirepass $PASS"
fi

# Forward args if provided, unless first arg is exactly "redis-server"
if [ $# -gt 0 ] && [ "$1" != "redis-server" ]; then
  exec $CMD "$@"
else
  exec $CMD
fi
