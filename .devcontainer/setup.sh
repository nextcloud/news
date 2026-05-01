#!/bin/bash
# SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
#
# Installs Nextcloud via the juliusknorr dev-image bootstrap script and then
# fixes file ownership so the vscode developer can work without sudo.
#
# Usage: bash .devcontainer/setup.sh
# Called automatically by devcontainer.json as postCreateCommand.

set -euo pipefail

WEBROOT="${WEBROOT:-/var/www/html}"

# Map NEXTCLOUD_VERSION to the branch name bootstrap expects.
# "master" is used as-is; any numeric version gets the "stable" prefix (e.g. 33 → stable33).
if [ -n "${NEXTCLOUD_VERSION:-}" ]; then
    if [ "$NEXTCLOUD_VERSION" = "master" ]; then
        export SERVER_BRANCH="master"
    else
        export SERVER_BRANCH="stable${NEXTCLOUD_VERSION}"
    fi
    echo "→ Using Nextcloud branch: $SERVER_BRANCH"
fi

echo "Environment:"
env | grep -E "(NEXTCLOUD|SERVER_BRANCH|SQL|WEBROOT)" | sort || true

# bootstrap.sh only triggers the installer when config.php already exists on
# disk but Nextcloud is not yet installed.  Create a placeholder now so a
# fresh container always proceeds with installation.
if [ ! -f "$WEBROOT/config/config.php" ]; then
    # config/ is root-owned in a fresh container; create placeholder as root,
    # then let bootstrap's update_permission() hand it off to www-data.
    sudo touch "$WEBROOT/config/config.php"
    echo "→ Created config.php placeholder"
fi

# Run bootstrap.
#   -E  preserves the calling environment (SERVER_BRANCH, SQL, …) which sudo
#       would otherwise strip.
#   apache2ctl start  is passed as "$@" to bootstrap; it runs as a daemon and
#       returns immediately, so bootstrap exits cleanly after setup is done.
echo "→ Running bootstrap (Apache + Nextcloud installer)…"
(cd /tmp && sudo -E /usr/local/bin/bootstrap.sh apache2ctl start)

# Wait for the background installer to finish (up to 5 minutes).
echo "→ Waiting for Nextcloud installation to complete…"
TIMEOUT=300
ELAPSED=0
until sudo -u www-data php "$WEBROOT/occ" status 2>/dev/null | grep -q "installed: true"; do
    if [ "$ELAPSED" -ge "$TIMEOUT" ]; then
        echo "✘ Timed out waiting for Nextcloud installation" >&2
        exit 1
    fi
    sleep 5
    ELAPSED=$((ELAPSED + 5))
    printf "\r   %ds / %ds…" "$ELAPSED" "$TIMEOUT"
done
echo ""
echo "✔ Nextcloud installed"

# Fix ownership so the vscode developer can read/write these directories
# without sudo.  Apache (www-data) retains access via group membership.
echo "→ Fixing permissions for development…"
for dir in config data apps-writable apps-extra apps-shared; do
    target="$WEBROOT/$dir"
    [ -d "$target" ] || continue
    sudo chown -R "$(id -u):www-data" "$target"
    sudo chmod -R g+rwX "$target"
done

echo "✔ Nextcloud is ready at http://localhost (admin / admin)"
