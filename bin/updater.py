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

import sys
import time
import json
import argparse
import threading
import urllib.request
import urllib.error


class UpdateThread(threading.Thread):

    lock = threading.Lock()

    def __init__(self, feeds, update_url):
        super().__init__()
        self.feeds = feeds
        self.update_url = update_url

    def run(self):
        with UpdateThread.lock:
            if len(self.feeds) > 0:
                feed = self.feeds.pop()
            else:
                return

        feed['feedId'] = feed['id']
        del feed['id']

        # call the update method of one feed
        data = urllib.parse.urlencode(feed)
        headers = {
            'Content-type': 'application/json',
            'Accept': 'text/plain'
        }
        url = '%s?%s' % (self.update_url, data)

        try:
            urllib.request.urlopen(url, timeout=60)
        except urllib.error.HTTPError as e:
            print('%s: %s' % (url, e))

        self.run()


class Updater:

    def __init__(self, base_url, thread_num, interval):
        self.thread_num = thread_num
        self.interval = interval
        self.base_url = base_url

        if self.base_url[-1] != '/':
            self.base_url += '/'
        self.base_url += 'index.php/apps/news/api/v1-2'

        self.cleanup_url = '%s/cleanup' % self.base_url
        self.all_feeds_url = '%s/feeds/all' % self.base_url
        self.update_url = '%s/feeds/update' % self.base_url


    def run(self):
        try:
            # run the cleanup request and get all the feeds to update
            urllib.request.urlopen(self.cleanup_url)
            feeds_response = urllib.request.urlopen(self.all_feeds_url)
            feeds_json = feeds_response.read().decode('utf-8')
            feeds = json.loads(feeds_json)['feeds']

            # start thread_num for feeds
            threads = []
            for num in range(0, self.thread_num):
                thread = UpdateThread(feeds, self.update_url)
                thread.start()
                threads.append(thread)

            for thread in threads:
                thread.join()

            # wait until the interval finished to run again
            time.sleep(self.interval)
            self.run()

        except ValueError:
            print('%s is not a valid URL' % self.base_url)
        except urllib.error.HTTPError:
            print('%s does not exist' % self.base_url)
            exit(1)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--threads',
        help='How many feeds should be fetched in paralell, defaults to 10',
        default=10,
        type=int)
    parser.add_argument('--interval',
        help='Update interval between fetching the next round of \
            updates in minutes, defaults to 30 minutes',
        default=30,
        type=int)
    parser.add_argument('--user',
        help='A username to log into ownCloud', required=True)
    parser.add_argument('--password',
        help='A password to log into ownCloud', required=True)
    parser.add_argument('url',
        help='The URL where owncloud is installed')
    args = parser.parse_args()

    # register user and password for a certain url
    auth = urllib.request.HTTPPasswordMgrWithDefaultRealm()
    auth.add_password(None, args.url, args.user, args.password)
    auth_handler = urllib.request.HTTPBasicAuthHandler(auth)
    opener = urllib.request.build_opener(auth_handler)
    urllib.request.install_opener(opener)

    # create the updater and run the threads
    updater = Updater(args.url, args.threads, args.interval)
    updater.run()


if __name__ == '__main__':
    if sys.version_info < (3, 0):
        print('Python 3.0 or higher is required to run this script')
    else:
        main()


