#!/usr/bin/env python3

import json
import threading
import requests
import time
import logging
import urllib
from subprocess import check_output

def check_status_code(response):
    if response.status_code != 200:
        raise Exception('Request failed with %i: %s' % (response.status_code,
            response.text))


class Updater:

    def __init__(self, thread_num, interval, run_once, log_level):
        self.thread_num = thread_num
        self.run_once = run_once
        self.interval = interval
        # logging
        format = '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
        logging.basicConfig(format=format)
        self.logger = logging.getLogger('ownCloud News Updater')
        if log_level == 'info':
            self.logger.setLevel(logging.INFO)
        else:
            self.logger.setLevel(logging.ERROR)

    def run(self):
        if self.run_once:
            self.logger.info('Running update once with %d threads' %
                                self.thread_num)
        else:
            self.logger.info(('Running update in an interval of %d seconds '
                              'using %d threads') % (self.interval,
                                                     self.thread_num))
        while True:
            self.start_time = time.time()  # reset clock
            try:
                self.before_update()
                feeds = self.all_feeds()

                threads = []
                for num in range(0, self.thread_num):
                    thread = self.start_update_thread(feeds)
                    thread.start()
                    threads.append(thread)
                for thread in threads:
                    thread.join()

                self.after_update()

                if self.run_once:
                    return
                # wait until the interval finished to run again and subtract
                # the update run time from the interval
                update_duration_seconds = int((time.time() - self.start_time))
                timeout = self.interval - update_duration_seconds
                if timeout > 0:
                    self.logger.info(('Finished updating in %d seconds, '
                                      'next update in %d seconds') %
                                      (update_duration_seconds, timeout))
                    time.sleep(timeout)
            except (Exception) as e:
                self.logger.error('%s: Trying again in 30 seconds' % e)
                time.sleep(30)

    def before_update(self):
        raise NotImplementedError

    def start_update_thread(self, feeds):
        raise NotImplementedError

    def all_feeds(self):
        raise NotImplementedError

    def after_update(self):
        raise NotImplementedError


class UpdateThread(threading.Thread):

    lock = threading.Lock()

    def __init__(self, feeds, logger):
        super().__init__()
        self.feeds = feeds
        self.logger = logger

    def run(self):
        while True:
            with WebUpdateThread.lock:
                if len(self.feeds) > 0:
                    feed = self.feeds.pop()
                else:
                    return
            try:
                self.logger.info('Updating feed with id %s and user %s' %
                    (feed['id'], feed['userId']))
                self.update_feed(feed)
            except (Exception) as e:
                self.logger.error(e)

    def update_feed(self, feed):
        raise NotImplementedError


class WebUpdater(Updater):

    def __init__(self, base_url, thread_num, interval, run_once,
                 user, password, timeout, log_level):
        super().__init__(thread_num, interval, run_once, log_level)
        self.base_url = base_url
        self.auth = (user, password)
        self.timeout = timeout

        if self.base_url[-1] != '/':
            self.base_url += '/'
        self.base_url += 'index.php/apps/news/api/v1-2'

        self.before_cleanup_url = '%s/cleanup/before-update' % self.base_url
        self.after_cleanup_url = '%s/cleanup/after-update' % self.base_url
        self.all_feeds_url = '%s/feeds/all' % self.base_url
        self.update_url = '%s/feeds/update' % self.base_url

    def before_update(self):
        self.logger.info('Calling before update url:  %s' % self.before_cleanup_url)
        before = requests.get(self.before_cleanup_url, auth=self.auth)
        check_status_code(before)

    def start_update_thread(self, feeds):
        return WebUpdateThread(feeds, self.logger, self.update_url, self.auth,
                               self.timeout)

    def all_feeds(self):
        feeds_response = requests.get(self.all_feeds_url, auth=self.auth)
        check_status_code(feeds_response)
        feeds_json = feeds_response.text
        self.logger.info('Received these feeds to update: %s' % feeds_json)
        return json.loads(feeds_json)['feeds']

    def after_update(self):
        self.logger.info('Calling after update url:  %s' % self.after_cleanup_url)
        after = requests.get(self.after_cleanup_url, auth=self.auth)
        check_status_code(after)


class WebUpdateThread(UpdateThread):

    def __init__(self, feeds, logger, update_url, auth, timeout):
        super().__init__(feeds, logger)
        self.update_url = update_url
        self.auth = auth
        self.timeout = timeout

    def update_feed(self, feed):
        # rewrite parameters, a feeds id is mapped to feedId
        feed['feedId'] = feed['id']
        del feed['id']

        # turn the pyton dict into url parameters
        data = urllib.parse.urlencode(feed)
        headers = {
            'Accept': 'text/plain'
        }
        url = '%s?%s' % (self.update_url, data)
        request = requests.get(url, auth=self.auth, timeout=self.timeout)
        check_status_code(request)


class ConsoleUpdater(Updater):

    def __init__(self, directory, thread_num, interval, run_once, log_level):
        super().__init__(thread_num, interval, run_once, log_level)
        self.directory = directory.rstrip('/')
        base_command = ['php', '-f', self.directory + '/occ']
        self.before_cleanup_command = base_command + ['news:updater:before-update']
        self.all_feeds_command = base_command + ['news:updater:all-feeds']
        self.update_feed_command = base_command + ['news:updater:update-feed']
        self.after_cleanup_command = base_command + ['news:updater:after-update']

    def before_update(self):
        self.logger.info('Running before update command %s' %
                            ' '.join(self.before_cleanup_command))
        check_output(self.before_cleanup_command)

    def start_update_thread(self, feeds):
        return ConsoleUpdateThread(feeds, self.logger, self.update_feed_command)

    def all_feeds(self):
        feeds_json = check_output(self.all_feeds_command).strip()
        feeds_json = str(feeds_json, 'utf-8')
        self.logger.info('Received these feeds to update: %s' % feeds_json)
        return json.loads(feeds_json)['feeds']

    def after_update(self):
        self.logger.info('Running after update command %s' %
                            ' '.join(self.after_cleanup_command))
        check_output(self.before_cleanup_command)


class ConsoleUpdateThread(UpdateThread):

    def __init__(self, feeds, logger, update_base_command):
        super().__init__(feeds, logger)
        self.update_base_command = update_base_command

    def update_feed(self, feed):
        command = self.update_base_command + [str(feed['id']), feed['userId']]
        self.logger.info('Running update command %s' % ' '.join(command))
        check_output(command)
