#!/usr/bin/env python

"""

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt nukeawhale@gmail.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

"""

# This script uses python 3
from argparse import ArgumentParser
from urllib import request

def main():
    parser = ArgumentParser()
    parser.add_argument('--threads',
        help='How many feeds should be fetched in paralell, defaults to 10',
        default=10)
    parser.add_argument('--interval',
        help='Minimal update interval between fetching the next round of \
            updates in minutes, defaults to 30 minutes',
        default=30)
    parser.add_argument('--user',
        help='A username to log into ownCloud', required=True)
    parser.add_argument('--pass',
        help='A password to log into ownCloud', required=True)
    parser.add_argument('url',
        help='The URL where owncloud is installed')
    args = parser.parse_args()

    # TODO: main loop with update inteval
    # TODO: make a request to the cleanup route
    # TODO: get all feeds and update them in seperate threads

    # TODO: also check for the other URLErrors
    try:
        pass
    except ValueError:
        print('Please enter a valid URL')


if __name__ == '__main__':
    main()


