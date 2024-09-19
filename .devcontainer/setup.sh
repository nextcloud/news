#!/bin/bash

# SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0

(
    cd /tmp && /usr/local/bin/bootstrap.sh apache2ctl start
)

make composer
make npm