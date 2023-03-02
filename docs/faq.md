# FAQ

## My browser shows a mixed content warning (Connection is Not Secure)
If you are serving your Nextcloud over HTTPS your browser will very likely warn you with a yellow warnings sign about your connection not being secure.

Chrome will show no green HTTPS lock sign, Firefox will show you the following image
![Mixed Passive Content](https://ffp4g1ylyit3jdyti1hqcvtb-wpengine.netdna-ssl.com/security/files/2015/10/mixed-passive-click1-600x221.png)

Note that this warning **is not red and won't block the page like the following images** which signal **a serious issue**:

![Untrusted Cert](https://www.inmotionhosting.com/support/images/stories/website/errors/ssl/chrome-self-signed-ssl-warning.png)
![Mixed Active Content](https://www.howtogeek.com/wp-content/uploads/2014/02/650x367xchrome-mixed-content-https-problem.png.pagespeed.gp+jp+jw+pj+js+rj+rp+rw+ri+cp+md.ic.r_lQiZiq38.png)

### What is the cause of the (yellow) error message

This warning is caused by [mixed passive content](https://developer.mozilla.org/en/docs/Security/MixedContent) and means that your page loads passive resources from non HTTPS resources, such as:

* Images
* Video/Audio

This allows a possible attacker to perform a MITM (man-in-the-middle) attack by serving you different images or audio/video.

### Why doesn't the News app fix it

The News app fully prevents mixed **active** content by only allowing HTTPS iframes from known locations; other possible mixed active content elements such as <script\> are stripped from the feed. Because images and audio/video are an integral part of a feed, we can not simply strip them.

Since an attacker can not execute code in contrast to mixed active content, but only replace images/audio/video in your feed reader, this is **not considered to be a security issue**. If, for whatever reason (e.g. feed which would allow fishing), this is a security problem for you, contact the specific feed provider and ask him to serve his feed content over HTTPS.

### Why don't you simply use an HTTPS image/audio/video proxy

For the same reason that we can't fix non HTTPS websites: It does not fix the underlying issue, but only silences it. If you are using an image HTTPS proxy, an attacker can simply attack your image proxy since the proxy fetches insecure content. **Even worse**: if your image proxy serves these images from the same domain as your Nextcloud installation, you [are vulnerable to XSS via SVG images](https://www.owasp.org/images/0/03/Mario_Heiderich_OWASP_Sweden_The_image_that_called_me.pdf). In addition, people feel safe when essentially they are not.

Since most people don't understand mixed content and don't have two domains and a standalone server for the image proxy, it is very likely they will choose to host it under the same domain.

Because we care about our users' security and don't want to hide security warnings, we won't fix (aka silence) this issue.

The only fix for this issue is that feed providers serve their content over HTTPS.

## I am getting: Exception: Some\\Class does not exist errors in my nextcloud.log
This is very often caused by missing or old files, e.g. by failing to upload all the News app files or errors during installation. Before you report a bug, please recheck if all files from the archive are in place and accessible.

## Feeds not updated
Feeds can be updated using Nextcloud's system cron or an [external updater](https://nextcloud.github.io/news/clients/#update-clients) via the API
**The feed update is not run in Webcron and AJAX cron mode!**

### System Cron
* Check if you are using the system cron (Cron) setting on the admin page. AJAX and Web cron will not update feeds
* Check if the cronjob exists with **crontab -u www-data -e** (replace www-data with your httpd user)
* Check the file permissions of the **cron.php** file and if **www-data** (or whatever your httpd user is called like) can read and execute that script
* Check if you can execute the cron with **sudo -u www-data php -f nextcloud/cron.php** (replace www-data with your httpd user)
* Check your **data/nextcloud.log** for errors
* Check if the cronjob is ever executed by placing an **error_log('updating');** in the [background job file](https://github.com/nextcloud/news/blob/master/lib/Service/UpdaterService.php#L55). If the cronjob runs, there should be an updating log statement in your httpd log.
* If there is no **updating** statement in your logs check if your cronjob is executed by executing a different script
* Check if the **oc_jobs** table has a **reserved_at** entry with a value other than 0. If it does for whatever reason, set it to 0. You can check this by executing:

  ```sql
  SELECT * from oc_jobs WHERE class LIKE '%News%' ORDER BY id;
  ```

You will get two rows where column `class`will be `OCA\News\Cron\Updater` and `OCA\News\Cron\UpdaterJob`.

!!! info

    In newer versions of News (21.x.x) the old job OCA\News\Cron\Updater was removed from the DB.

 Reset the `reserved_at` by executing

  ```sql
  UPDATE oc_jobs SET reserved_at = 0 WHERE id = <id from above SELECT statement>;
  ```

 * If your cron works fine, but Nextcloud's cronjobs are never executed, file a bug in [server](https://github.com/nextcloud/server/)

### External Updater
* Check if your configuration is set to **not** use the system cron.
* Consult the documentation of the updater
* Check your **data/nextcloud.log** for errors

## Adding feeds that use self-signed certificates
If you want to add a feed that uses a self-signed certificate that is not signed by a trusted CA the request will fail with "SSL certificate is invalid". A common solution is to turn off the certificate verification **which is wrong** and **makes your installation vulnerable to MITM attacks**. Therefore **turning off certificate verification is not supported**.


If you have control over the feed in question, consider signing your certificate for free on one of the following providers:

* [Let's Encrypt](https://letsencrypt.org/)
* [ZeroSSL](https://zerossl.com/)

If you do not have control over the chosen feed, you should [download the certificate from the feed's website](https://superuser.com/questions/97201/how-to-save-a-remote-server-ssl-certificate-locally-as-a-file) and [add it to your server's trusted certificates](https://www.onlinesmartketer.com/2009/06/23/curl-adding-installing-trusting-new-self-signed-certificate/). The exact procedure however may vary depending on your distribution.

## Is There An Subscription URL To Easily Subscribe To Feeds

By appending **?subscribe_to=SOME_URL** to your News app URL, you can launch the News app with a pre-filled URL, e.g.:

    https://yourdomain.com/nextcloud/index.php/apps/news?subscribe_to=https://github.com/nextcloud/news/releases

## Database table grows too big

If your users have subscribed to some high-volume feeds where a lot of items remain unread, 
this can lead to an oversized news table over time. As a consequence, the database upgrade of the news app can take several hours, during which Nextcloud cannot be used.

By default, Nextcloud News purges old news items above a certain threshold each time it fetches new news items. The maximum number of items per feed
that should be kept during the purging can be defined through the “Maximum read count per feed” setting in the admin UI or the `autoPurgeCount`
value in the config.
Additionally you may enable the option to also purge unread items `purgeUnread`. This is useful if your users have large amounts of unread items.
Starred items are always exempt from purging.

The command `occ news:updater:after-update [--purge-unread] [<purge-count>]` can be used to manually purge old news items across the instance. With
the `--purge-unread` option, unread items are also purged (starred items are still exempt). If `purge-count` is not specified, the configured
`autoPurgeCount` is used.

The purge count only applies to the items that are purged. For example, when purging a feed that has 100 unread items, 100 starred read
items and 100 unstarred read items, using a `purge-count` of 50 would keep all unread and starred items and the latest 50 read items. Using
a `purge-count` of 50 along with `--purge-unread` would keep the all starred items plus the latest 50 from the set of unread and read items.

## Missing 4-byte support SQLSTATE[22007]: Invalid datetime format: 1366 Incorrect string value: ...

This is likely caused by your feed using emojis in the feed title or text.

The DB is then not able to store the feed and runs into strange decoding errors.

You need to convert your DB to support 4 bytes, check the [Nextcloud documentation](https://docs.nextcloud.com/server/stable/admin_manual/configuration_database/mysql_4byte_support.html).

References [#1165](https://github.com/nextcloud/news/issues/1165) [#526](https://github.com/nextcloud/news/issues/526)