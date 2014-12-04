#!/usr/bin/env python3

import json
import threading
import requests
import time
import logging

def check_status_code(response):
    if response.status_code != 200:
        raise Exception('Request failed with %i: %s' % (response.status_code,
            response.text))


class Updater:

    def __init__(self, base_url, thread_num, interval, user, password, timeout,
                 run_once):
        self.thread_num = thread_num
        self.interval = interval
        self.base_url = base_url
        self.user = user
        self.password = password
        self.timeout = timeout
        self.run_once = run_once

        if self.base_url[-1] != '/':
            self.base_url += '/'
        self.base_url += 'index.php/apps/news/api/v1-2'

        self.before_cleanup_url = '%s/cleanup/before-update' % self.base_url
        self.after_cleanup_url = '%s/cleanup/after-update' % self.base_url
        self.all_feeds_url = '%s/feeds/all' % self.base_url
        self.update_url = '%s/feeds/update' % self.base_url

        # logging
        self.logger = logger.getLogger('ownCloud News Updater')
        self.logger.setLevel(logging.INFO)

        format = '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
        self.logger.basicConfig(format=format)


    def run(self):
        while True:
            self.start_time = time.time()  # reset clock

            try:
                # run the cleanup request and get all the feeds to update
                auth = (self.user, self.password)

                before = requests.get(self.before_cleanup_url, auth=auth)
                check_status_code(before)

                feeds_response = requests.get(self.all_feeds_url, auth=auth)
                check_status_code(feeds_response)

                feeds_json = feeds_response.text
                feeds = json.loads(feeds_json)['feeds']

                # start thread_num threads which update the feeds
                threads = []
                for num in range(0, self.thread_num):
                    thread = UpdateThread(feeds, self.update_url, self.user,
                        self.password, self.timeout, self.logger)
                    thread.start()
                    threads.append(thread)

                for thread in threads:
                    thread.join()

                after = requests.get(self.after_cleanup_url, auth=auth)
                check_status_code(after)

                if self.run_once:
                    return

                # wait until the interval finished to run again and subtract
                # the update run time from the interval
                timeout = self.interval - int((time.time() - self.start_time))
                if timeout > 0:
                    time.sleep(timeout)

            except (Exception) as e:
                self.logger.error('%s: %s Trying again in 30 seconds' %
                                   (self.base_url, e))
                time.sleep(30)


class UpdateThread(threading.Thread):

    lock = threading.Lock()

    def __init__(self, feeds, update_url, user, password, timeout, logger):
        super().__init__()
        self.feeds = feeds
        self.update_url = update_url
        self.user = user
        self.password = password
        self.timeout = timeout
        self.logger = logger

    def run(self):
        while True:
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
                auth = (self.user, self.password)
                request = requests.get(url, auth=auth, timeout=self.timeout)
                check_status_code(request)
            except (Exception) as e:
                self.logger.error('%s: %s' % (url, e))
