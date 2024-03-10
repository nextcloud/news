# Integrations

## Is There An Subscription URL To Easily Subscribe To Feeds?

By appending `/index.php/apps/news?subscribe_to=SOME_RSS_URL` to your NextCloud base path URL, you can launch the News app with a pre-filled URL, e.g.:

Ex.

    https://yourdomain.com/nextcloud/index.php/apps/news?subscribe_to=https://github.com/nextcloud/news/releases

### Known Working Integrations

#### Chrome / Edge

1. Install [RSS Subscription Extension (by Google)](https://chrome.google.com/webstore/detail/rss-subscription-extensio/nlbjncdgjeocebhnmkbbbdekmmmcbfjd) extension
2. Open the extension's options menu
3. Click `Add..`
4. In the *Description* field, enter a description for the RSS reader entry. 'NextCloud News' is a reasonable name.
5. Enter `https://<NEXTCLOUD_BASE_PATH>/index.php/apps/news?subscribe_to=%s` replacing &lt;NEXTCLOUD_BASE_PATH&gt; with the base URL path to your NextCloud instance.
    * Domain based example: <https://cloud.mydomain.com/index.php/apps/news?subscribe_to=%s>
    * Domain+subpath based example: <https://cloud.mydomain.com/nextcloud/index.php/apps/news?subscribe_to=%s>

#### Firefox

1. Install Firefox Add-on Extension [Awesome RSS](https://addons.mozilla.org/en-US/firefox/addon/awesome-rss/)
2. Open the `Preferences` for the extension
3. In the 'Subscribe using' section, select the `NextCloud` radio button
4. In the field link field, enter the base NextCloud URL.
    * Domain based example: <https://cloud.mydomain.com/>
    * Domain+subpath based example: <https://cloud.mydomain.com/nextcloud/>
