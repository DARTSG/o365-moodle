#!/bin/bash

# This script installs these plugins on a remote server using rsync over SSH.
plugins_to_install=(
    "auth/oidc/"
    "blocks/microsoft/"
    "local/o365/"
    "local/office365/"
    "repository/office365/"
    "theme/boost_o365teams/"
)
remote_ip="1.2.3.4"  # Replace with the actual IP address of the remote server

# Check if rsync is installed and its version
if ! command -v rsync &> /dev/null; then
  echo "Error: rsync is not installed. Please install rsync version 3 or above." >&2
  exit 1
fi

rsync_version=$(rsync --version 2>/dev/null | head -n1 | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -n1)
rsync_major=$(echo "$rsync_version" | cut -d. -f1)
if [[ "$rsync_major" -lt 3 ]]; then
  echo "Error: rsync version 3 or above is required. Found: $rsync_version" >&2
  exit 1
fi

# Check SSH connectivity to the remote server
ssh -o BatchMode=yes -o ConnectTimeout=5 "root@$remote_ip" echo "OK" > /dev/null 2>&1
if [[ $? -ne 0 ]]; then
  echo "Error: Unable to connect to $remote_ip with root user via SSH. Root user is required for the installation process." >&2
  exit 1
fi

for plugin in "${plugins_to_install[@]}"; do
  rsync -rltvz \
  --chmod=D755,F644 \
  --chown=www-data:www-data \
  -e "ssh" \
  $plugin \
  "root@$remote_ip:/var/www/html/$plugin"
done


