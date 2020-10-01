# Configuration

The configuration is saved in the database, you can change and view them via the admin interface.

You can find them in the appconfig table.

```sql
mysql> select * from oc_appconfig where appid = 'news';
+-------+--------------------------+-------------+
| appid | configkey                | configvalue |
+-------+--------------------------+-------------+
| news  | autoPurgeCount           | 200         |
| news  | autoPurgeMinimumInterval | 60          |
| news  | enabled                  | yes         |
| news  | exploreUrl               |             |
| news  | feedFetcherTimeout       | 60          |
| news  | installed_version        | 15.0.0      |
| news  | maxRedirects             | 10          |
| news  | types                    |             |
| news  | updateInterval           | 3600        |
| news  | useCronUpdates           | 1           |
+-------+--------------------------+-------------+
```

# Legacy Configuration before News 15.0.0

All configuration values are set inside **nextcloud/data/news/config/config.ini** and can be edited in the admin panel.

The configuration is in **INI** format and looks like this:

```ini
autoPurgeMinimumInterval = 60
autoPurgeCount = 200
maxRedirects = 10
maxSize = 104857600
feedFetcherTimeout = 60
useCronUpdates = true
exploreUrl =
```


* **autoPurgeMinimumInterval**: Minimum amount of seconds after deleted feeds and folders are removed from the database. Values below 60 seconds are ignored
* **autoPurgeCount**: Defines the maximum amount of articles that can be read per feed which won't be deleted by the cleanup job; if old articles reappear after being read, increase this value; negative values such as -1 will turn this feature off completely
* **maxRedirects**: How many redirects the updater should follow
* **maxSize**: Maximum feed size in bytes. If the RSS/Atom page is bigger than this value, the update will be aborted
* **feedFetcherTimeout**: Maximum number of seconds to wait for an RSS or Atom feed to load. If a feed takes longer than that number of seconds to update, the update will be aborted
* **useCronUpdates**: To use a custom update/cron script you need to disable the cronjob which is run by Nextcloud by default by setting this to false
* **exploreUrl**: If given that url will be contacted for fetching content for the explore feed