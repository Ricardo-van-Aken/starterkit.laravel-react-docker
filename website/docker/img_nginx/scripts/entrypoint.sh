#!/bin/sh
set -e

#!/usr/bin/env bash
set -euo pipefail

TARGET_DIR="${TARGET_DIR:-/var/www/public}"
NGINX_USER="${NGINX_USER:-nginx}"
WWW_GROUP_NAME="www-data"

info(){ echo "[entrypoint] $*"; }

# Only attempt permission fixes if target dir exists
if [ -e "$TARGET_DIR" ]; then
  info "Found target dir: $TARGET_DIR"

  # get numeric gid of the target directory (falls back to 33 if stat fails)
  gid="$(stat -c '%g' "$TARGET_DIR" 2>/dev/null || echo 33)"
  info "Detected GID: $gid"

  # try to find an existing group with that GID
  existing_group="$(getent group "$gid" | cut -d: -f1 || true)"

  if [ -z "$existing_group" ]; then
    # create a group named www-data with that GID if possible
    if getent group "$WWW_GROUP_NAME" >/dev/null 2>&1; then
      # name taken: use that group (but it may have different gid)
      info "Group name '$WWW_GROUP_NAME' already exists; using existing group"
      group_to_use="$WWW_GROUP_NAME"
    else
      info "No group with GID $gid found — creating group '$WWW_GROUP_NAME' with GID $gid"
      groupadd -g "$gid" "$WWW_GROUP_NAME"
      group_to_use="$WWW_GROUP_NAME"
    fi
  else
    info "Found group '$existing_group' owning the directory (GID $gid)"
    group_to_use="$existing_group"
  fi

  # Ensure nginx user exists before trying to modify it
  if id -u "$NGINX_USER" >/dev/null 2>&1; then
    info "Adding user '$NGINX_USER' to group '$group_to_use'"
    # prefer usermod when available; gpasswd is an alternative
    if command -v usermod >/dev/null 2>&1; then
      usermod -aG "$group_to_use" "$NGINX_USER" || true
    else
      gpasswd -a "$NGINX_USER" "$group_to_use" || true
    fi
  else
    info "User '$NGINX_USER' does not exist in this container — skipping usermod step"
  fi

  # Fix permissions: directories -> 2775 (rwxrwsr-x), files -> 664 (rw-rw-r--)
  info "Applying directory/file permissions under $TARGET_DIR"
  find "$TARGET_DIR" -type d -exec chmod 2775 {} + || true
  find "$TARGET_DIR" -type f -exec chmod 0664 {} + || true

  # Ensure group write on existing content (useful if php needs to write)
  chmod -R g+rw "$TARGET_DIR" || true

  info "Permission adjustments complete."
else
  info "Target dir '$TARGET_DIR' does not exist — skipping permission/ownership adjustments."
fi

# Start polling in background and let it log to stdout
/usr/local/bin/polling.sh &

# Exec nginx as PID 1 so signals are handled correctly
exec /docker-entrypoint.sh "$@"