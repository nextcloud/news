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
# Fun messages are printed every 10 seconds to keep the developer entertained.
FUN_TASKS=(
    "Summoning PHP 8.3 🐘"
    "Cloning Nextcloud ${SERVER_BRANCH:-master} 🌿"
    "Setting up the database 🗄️"
    "Running maintenance:install 🔧"
    "Enabling apps 🧩"
    "Configuring Apache 🪁"
    "Calibrating Xdebug 🔍"
    "Feeding the RSS gnomes 📰"
    "Polishing the user interface ✨"
    "Teaching owls to hoot 🦉"
    "Brewing extra coffee ☕"
    "Checking for gremlins 🐛"
    "Untangling spaghetti code 🍝"
    "Counting database rows 🔢"
    "Crossing fingers 🤞"
    "Chanting install mantras 🙏"
    "Bribing the build gods ⚡"
    "Asking the PHP fairy nicely 🧚"
    "Definitely almost done ⏳"
)
TASK_INDEX=0
TIMEOUT=300
ELAPSED=0

echo "→ Waiting for Nextcloud installation to complete…"
echo ""

until sudo -u www-data php "$WEBROOT/occ" status 2>/dev/null | grep -q "installed: true"; do
    if [ "$ELAPSED" -ge "$TIMEOUT" ]; then
        echo ""
        echo "✘ Timed out waiting for Nextcloud installation" >&2
        exit 1
    fi
    sleep 5
    ELAPSED=$((ELAPSED + 5))
    # Print a new "completed task" every 10 seconds to entertain the developer.
    if [ "$ELAPSED" -gt "0" ] && [ "$(( ELAPSED % 10 ))" -eq "0" ]; then
        printf "  ✔ %s\n" "${FUN_TASKS[$TASK_INDEX]}"
        TASK_INDEX=$(( (TASK_INDEX + 1) % ${#FUN_TASKS[@]} ))
    fi
done
echo ""
echo "✔ Nextcloud installed"

# Disable the profiler.  The dev base image enables it by default; it must be
# turned off after the installer finishes writing config.php (any earlier
# attempt races against the background installer and gets overwritten).
echo "→ Disabling Nextcloud profiler…"
sudo -u www-data php "$WEBROOT/occ" config:system:set profiler --value=false --type=bool
echo "✔ Profiler disabled"

# Fix ownership so the vscode developer can read/write these directories
# without sudo.  Apache (www-data) retains access via group membership.
echo "→ Fixing permissions for development…"
for dir in config data apps-writable apps-extra apps-shared; do
    target="$WEBROOT/$dir"
    [ -d "$target" ] || continue
    sudo chown -R "$(id -u):www-data" "$target"
    sudo chmod -R g+rwX "$target"
done

# Bootstrap runs as root (via sudo -E), so any Nextcloud temp files it created
# in /tmp are owned by root.  Remove them so the vscode user can recreate them
# with the correct owner on the first `./occ` run (otherwise FileSequence
# finds a root-owned directory that exists but is not writable).
sudo find /tmp -maxdepth 1 -type d -user root \( -name 'oc_*' -o -name 'nc_*' \) \
    -exec rm -rf {} + 2>/dev/null || true

echo "✔ Nextcloud is ready at http://localhost (admin / admin)"