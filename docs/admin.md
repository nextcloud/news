# Admin
Welcome to the Admin documentation this page explains some of the configuration options for news.
## System Cron
Nextcloud uses Cron to run regular jobs, News relies on the Job system to execute the feed updates.
Alternatively you may use an [external updater](https://nextcloud.github.io/news/clients/#update-clients), in this case you need to disable the system cron in the settings.

## Auto purge count
This value represents the maximum amount of read items per feed, which won't be deleted by the cleanup job.
For example if the value is 200 there can be maximum 200 read items per feed, unread items are unaffected.
If old articles reappear after being read, try to increase this value.
To disable this feature use -1.

## Explore Service
If you are using the News app in your company/community it might be interesting to offer your users a bunch of easily to discover default feeds. You could also create a website where people can add and up-vote news feeds like bigger cloud feed readers like Feedly do it or even convert their APIs into a service for the News app (if someone wants to provide one for the News app, feel free to contact us by creating an issue in the bug tracker).

The URL should be a path to a directory which contains a JSON file in the format of **feeds.LANG_CODE.json** where LANG_CODE is a two character language code (e.g. **en** or **de**).

For example, entering the URL **https://domain.com/directory** as explore URL will produce the following request for German users:

    GET https://domain.com/directory/feeds.de.json

**Do not forget to implement [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) in your API, otherwise the request will fail!**

## Update Interval
The update interval is used to determine when the next update of all feeds should be done.
You can configure this interval as an administrator.

### What is a good update interval?
That depends on your individual needs.
Please keep in mind that the lower you set your update interval, the more traffic is generated.

### Can I set individual update intervals per feed/user?
No, the job framework of Nextcloud is pretty simple.