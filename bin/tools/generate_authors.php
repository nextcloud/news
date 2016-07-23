<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */

$cmd = 'git --no-pager shortlog -nse HEAD';
exec($cmd, $contributors);

// extract data from git output into an array
$regex = '/^\s*(?P<commit_count>\d+)\s*(?P<name>.*\w)\s*<(?P<email>[^\s]+)>$/';
$contributors = array_map(function ($contributor) use ($regex) {
    preg_match($regex, $contributor, $result);
    return $result;
}, $contributors);

// filter out bots
$contributors = array_filter($contributors, function ($contributor) {
    return strpos($contributor['name'], 'Jenkins') !== 0;
});

// turn tuples into markdown
$markdownLines = array_map(function ($contrib) {
    return '* [' . $contrib['name'] . '](mailto:' . $contrib['email'] . ')';
}, $contributors);

// add headline
array_unshift($markdownLines, '# Authors');

$markdown = implode("\n", $markdownLines);
file_put_contents('AUTHORS.md', $markdown);
