#!/bin/bash

# SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0

# better prompt
echo 'export PS1="\[\e[32m\]root@devcontainer\[\e[0m\]:\[\e[34m\]\W\[\e[0m\]# "' >> ~/.bashrc

# Install zizmor for GitHub Actions security analysis
echo "Installing zizmor via pip..."
pip3 install --quiet zizmor
echo "zizmor installed: $(zizmor --version)"

# Map NEXTCLOUD_VERSION to SERVER_BRANCH for the bootstrap script
if [ -n "$NEXTCLOUD_VERSION" ]; then
    export SERVER_BRANCH="stable${NEXTCLOUD_VERSION}"
    echo "Setting SERVER_BRANCH to: $SERVER_BRANCH"
fi

# Show what we're using
echo "Environment variables:"
env | grep -E "(NEXTCLOUD|SERVER_BRANCH)" | sort

(
    cd /tmp && /usr/local/bin/bootstrap.sh apache2ctl start
)

make composer
make npm