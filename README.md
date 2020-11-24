# Nextcloud News app

**We need help with the frontend, check the issue tracker if you are interessted!**

![Release status](https://github.com/nextcloud/news/workflows/Build%20and%20publish%20app%20release/badge.svg)![Integration Tests](https://github.com/nextcloud/news/workflows/Integration%20Tests/badge.svg)[![Frontend status](https://travis-ci.org/nextcloud/news.svg?branch=master)](https://travis-ci.org/nextcloud/news) [![Code coverage](https://img.shields.io/codecov/c/github/nextcloud/news.svg?style=flat-square)](https://codecov.io/gh/nextcloud/news/)

The News app is an RSS/Atom feed aggregator. It offers a [RESTful API](https://github.com/nextcloud/news/tree/master/docs/externalapi/Legacy.md) for app developers. The source code is [available on GitHub](https://github.com/nextcloud/news)

## Install
See the [install document](https://github.com/nextcloud/news/blob/master/docs/install.md)

## FAQ
* [My browser shows a mixed content warning](https://github.com/nextcloud/news/blob/master/docs/faq/README.md#my-browser-shows-a-mixed-content-warning-connection-is-not-secure)
* [I am getting: Exception: Some\\Class does not exist erros in my nextcloud.log](https://github.com/nextcloud/news/blob/master/docs/faq/README.md#i-am-getting-exception-someclass-does-not-exist-erros-in-my-nextcloudlog)
* [Feeds are not updated](https://github.com/nextcloud/news/blob/master/docs/faq/README.md#feeds-not-updated)
* [Adding feeds that use self-signed certificates](https://github.com/nextcloud/news/blob/master/docs/faq/README.md#adding-feeds-that-use-self-signed-certificates)
* [Is There An Subscription URL To Easily Subscribe To Feeds](https://github.com/nextcloud/news/blob/master/docs/faq/README.md#is-there-an-subscription-url-to-easily-subscribe-to-feeds)

## Supported Browsers
* Newest Firefox (Desktop, Android, Firefox OS)
* Newest Chrome/Chromium (Desktop, Android)

## Bugs
Please read the [appropriate section in the contributing notices](https://github.com/nextcloud/news/blob/master/CONTRIBUTING.md#issues)

## Sync Clients
Nextcloud News can be synced with the following apps:
  * [RSS Guard (Windows, Linux, OS/2, Mac OS)](https://github.com/martinrotter/rssguard), [open source](https://github.com/martinrotter/rssguard)
  * [Nextcloud News Reader (Android)](https://play.google.com/store/apps/details?id=de.luhmer.owncloudnewsreader), [open source](https://github.com/nextcloud/news-android-app)
  * [OCReader (Android)](https://f-droid.org/repository/browse/?fdid=email.schaal.ocreader), [open source](https://github.com/schaal/ocreader)
  * [Newsout (Android)](https://play.google.com/store/apps/details?id=com.inspiredandroid.newsout), [open source](https://github.com/SimonSchubert/NewsOut)
  * [Readrops (Android)](https://f-droid.org/en/packages/com.readrops.app/), [open source](https://github.com/readrops/Readrops)
  * [CloudNews (iOS)](https://apps.apple.com/app/cloudnews-owncloud-news-reader/id683859706), [open source](https://github.com/owncloud/news-ios-app)
  * [Fiery Feeds (iOS)](https://apps.apple.com/us/app/fiery-feeds-rss-reader/id1158763303), closed source
  * [News Checker (Chrome extension)](https://chrome.google.com/webstore/detail/owncloud-news-checker/hnmagnmdnfdhabdlicankfbfhcdgbfhe)
  * [own News (BlackBerry)](http://appworld.blackberry.com/webstore/content/32767887/)
  * [FeedSpider (Firefox OS, WebOS, LuneOS)](http://www.feedspider.net/), [open source](https://github.com/OthelloVentures/feedspider)
  * [fastReader (Windows Phone)](http://www.windowsphone.com/en-us/store/app/fastreader/e55e696d-aa45-4a49-bb1c-a1fc7fdabec1), closed source
  * [py3status](https://github.com/ultrabug/py3status/) for [i3 (UNIX-like)](http://i3wm.org/), [open source](https://github.com/i3/i3)
  * [newsboat](http://newsboat.org/) for Unix terminal, [open source](https://github.com/newsboat/newsboat)
  * [Newsie (Ubuntu Touch)](https://open-store.io/app/newsie.martinferretti), [open source](https://gitlab.com/ferrettim/newsie)

## Custom Themes
Nextcloud News can look different with the following themes:
  * [Nextcloud News Themes](https://github.com/cwmke/nextcloud-news-themes)

## Updating Notices
To receive notifications when a new News app version was released, simply add the following Atom feed in your currently installed News app:

    https://github.com/nextcloud/news/releases.atom

## Screenshots
![](https://raw.githubusercontent.com/nextcloud/news/master/screenshots/1.png)

## Maintainers

* [Benjamin Brahmer](https://github.com/Grotax)
* [Sean Molenaar](https://github.com/SMillerDev)

### Special thanks to the Feed-IO library
Please consider donating to the developer of the RSS parser that powers nextcloud/news: [https://github.com/sponsors/alexdebril](https://github.com/sponsors/alexdebril)
