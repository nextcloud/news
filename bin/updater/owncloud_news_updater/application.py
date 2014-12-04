#!/usr/bin/env python3
"""
Updater script for the news app which allows multiple feeds to be updated at
once to speed up the update process. Built in cron has to be disabled in the
news config, see the README.rst file in the top directory for more information.
"""
__author__ = 'Bernhard Posselt'
__copyright__ = 'Copyright 2012-2014, Bernhard Posselt'
__license__ = 'AGPL3+'
__maintainer__ = 'Bernhard Posselt'
__email__ = 'dev@bernhard-posselt.com'

import sys
import argparse
import configparser

from owncloud_news_updater.updater import Updater


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--testrun',
        help='Run update only once, DO NOT use this in a cron job, only \
              recommended for testing', action='store_true')
    parser.add_argument('--threads', '-t',
        help='How many feeds should be fetched in parallel, defaults to 10',
        default=10,
        type=int)
    parser.add_argument('--timeout', '-s',
        help='Maximum number of seconds for updating a feed, \
              defaults to 5 minutes',
        default=5*60,
        type=int)
    parser.add_argument('--interval', '-i',
        help='Update interval between fetching the next round of \
            updates in minutes, defaults to 30 minutes. The update timespan \
            will be subtracted from the interval.',
        default=30,
        type=int)
    parser.add_argument('--config', '-c',
        help='Path to config file where all parameters except can be defined \
        as key values pair. An example is in bin/example_config.ini')
    parser.add_argument('--user', '-u',
        help='Admin username to log into ownCloud. Must be specified on the \
        command line or in the config file.')
    parser.add_argument('--password', '-p',
        help='Admin password to log into ownCloud')
    parser.add_argument('url',
        help='The URL where owncloud is installed. Must be specified on the \
        command line or in the config file.',
        nargs='?')
    args = parser.parse_args()

    # read config file if given
    if args.config:
        config = configparser.ConfigParser()
        files = config.read(args.config)

        if len(files) <= 0:
            print('Error: could not find config file %s' % args.config)
            exit(1)

        config_values = config['updater']
        if 'user' in config_values:
            args.user = config_values['user']
        if 'password' in config_values:
            args.password = config_values['password']
        if 'testrun' in config_values:
            args.testrun = config_values.getboolean('testrun')
        if 'threads' in config_values:
            args.threads = int(config_values['threads'])
        if 'interval' in config_values:
            args.interval = int(config_values['interval'])
        if 'url' in config_values:
            args.url = config_values['url']

    # url and user must be specified either from the command line or in the
    # config file
    if not args.url or not args.user:
        parser.print_help()
        exit(1)

    # create the updater and run the threads
    updater = Updater(args.url, args.threads, args.interval, args.user,
                      args.password, args.timeout, args.testrun)
    updater.run()


if __name__ == '__main__':
    if sys.version_info < (3, 0):
        print('Python 3.0 or higher is required to run this script')
    else:
        main()


