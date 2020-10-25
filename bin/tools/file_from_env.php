#!/usr/bin/env php
<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Benjamin Brahmer <info@b-brahmer.de>
 * @copyright Benjamin Brahmer 2020
*/

if ($argc < 2) {
    echo "This script expects two parameters:\n";
    echo "./file_from_env.php ENV_VAR PATH_TO_FILE\n";
    exit;
}

# Read environment variable
$content = getenv($argv[1]);

file_put_contents($argv[2], $content);

echo "Done...\n";