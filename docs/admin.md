# Admin

Welcome to the Admin documentation.

## Administration via OCC

News integrates with Nextclouds command line tool occ.

To get an overview over the available commands simply execute `./occ list news`

In most environments you will have to call occ like this:

```bash
sudo -u www-data php ./occ list news
```

More information about occ here: [Nextcloud Admin Manual](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/occ_command.html)

## Settings

The following sections explain some of the more complicated settings on the admin page.

### System Cron

Nextcloud uses cron to run regular jobs, News relies on the Job system to execute the feed updates.
Alternatively you may use an [external updater](https://nextcloud.github.io/news/clients/#update-clients), in this case you need to disable the system cron in the settings.

### Auto purge count

- The default value is 200.
- To disable this feature, use -1.
- Unread and starred items are not deleted.

Auto purging automatically removes the oldest read items of every feed after every update.
The value you enter here is used as the limit of read items per feed, unless the feed comes with more items in it's feed.
The individual limit per feed is only adjusted when it's bigger. Let's say last feed update came with 210 items,
then that will be the limit for that feed as long as no bigger update with more items is fetched.
In this case the limit will be 210 instead of 200, for that feed.

This is needed to prevent items from reappearing in the feed.

### Purge unread items

This changes the behavior of the auto purging to also purge unread items. This is useful if you have users with a lot of unread items.

**Starred items are always kept.**

### Explore Service

If you are using the News app in your company/community, it might be interesting to offer your users a bunch of easily to discover default feeds. You could also create a website where people can add and up-vote news feeds like bigger cloud feed readers like Feedly do it or even convert their APIs into a service for the News app (if someone wants to provide one for the News app, feel free to contact us by creating an issue in the bug tracker).

The URL should be a path to a directory which contains a JSON file in the format of **feeds.LANG_CODE.json** where LANG_CODE is a two character language code (e.g. **en** or **de**).

For example, entering the URL **<https://domain.com/directory>** as explore URL will produce the following request for German users:

    GET https://domain.com/directory/feeds.de.json

**Do not forget to implement [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) in your API, otherwise the request will fail!**

### Update Interval

The update interval is used to determine when the next update of all feeds should be done.
By default, the value is set to 3600 seconds (1 hour) You can configure this interval as an administrator.
The new value is only applied after the next run of the updater.

#### What is a good update interval?

That depends on your individual needs.
Please keep in mind that the lower you set your update interval, the more traffic is generated.

#### Can I set individual update intervals per feed/user?

No, that is not possible.
