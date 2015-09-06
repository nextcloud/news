#!/usr/bin/env python3

import subprocess
import re
import os.path

contribs = subprocess.check_output(['git', '--no-pager', 'shortlog', '-nse', 'HEAD'])
contrib_lines = contribs.decode('utf-8').split('\n')

format_regex = r'^\s*(?P<commit_count>\d+)\s*(?P<name>.*\w)\s*<(?P<email>[^\s]+)>$'

def tuple_to_markdown(tuple):
    return ('* [%s](mailto:%s)' % (tuple[0], tuple[1]))

def line_to_tuple(line):
    result = re.search(format_regex, line)
    if result:
        return (
            result.group('commit_count'),
            result.group('name'),
            result.group('email')
        )
    else:
        return ()

def group_by_name(tuples):
    authors = {}
    for tuple in tuples:
        if tuple[1] in authors.keys():
            authors[tuple[1]]['commits'] += int(tuple[0])
        else:
            authors[tuple[1]] = {
                'commits': int(tuple[0]),
                'email': tuple[2]
            }
    result = []
    for author, info in authors.items():
        result.append((info['commits'], author, info['email']))
    return result

tuples = map(line_to_tuple, contrib_lines)
tuples = filter(lambda x: len(x) > 0, tuples)  # filter out empty results
tuples = filter(lambda x: 'Jenkins' not in x[1], tuples) # filter out jenkins
tuples = group_by_name(tuples)
tuples = sorted(tuples, key=lambda x: x[0], reverse=True)
tuples = map(lambda x: (x[1], x[2]), tuples)
authors = map(tuple_to_markdown, tuples)
authors = '\n'.join(authors)

header = '# Contributors'
contents = '%s\n%s' % (header, authors)

# write contents into contributors file
base_dir_diff = 3
current_dir = os.path.dirname(os.path.realpath(__file__))
base_dir = current_dir

for x in range(base_dir_diff):
    base_dir = os.path.join(base_dir, os.pardir)

contributors_file = os.path.join(base_dir, 'AUTHORS.md')
with open(contributors_file, 'w') as f:
    f.write(contents)
