# Explore Feeds Section

The News app uses a JSON format to display the feeds in the explore feed section.

The feeds are stored in a JSON file in the [explore](https://github.com/nextcloud/news/tree/master/lib/Explore/feeds) folder and are localized based on their filename, meaning: feeds.en.json will only be shown for English localized Nextcloud installations, feeds.de.json only for German installations. If no other localization exists, the feeds.en.json will be taken.

You can also provide your own explore service.

## Format

The file has the following format:
```js
{
  "Nextcloud": [{  // category
      "title": "Nextcloud Planet",
      "favicon": "https://nextcloud.com/contribook/main/images/nextcloud/100.png",
      "url": "https://nextcloud.com/news/",
      "feed": "https://nextcloud.com/feed/",
      "description": "Nextcloud Planet is a blog feed aggregator",
      "votes": 1000
  }],
}
```

To ease the pain of constructing the JSON object, you can use a nextcloud command to automatically create it:

    php ./occ news:generate-explore https://path.com/to/feed.rss

By passing a second parameter you can set the vote count which determines the sorting on the explore page:

    php ./occ news:generate-explore https://path.com/to/feed.rss 1000

You can paste the output directly into the appropriate json file but you may need to add additional categories and commas

## Using A Webservice Instead of JSON Files

If you are using the News app in your company/community it might be interesting to offer your users a bunch of easily to discover default feeds. You could also create a website where people can add and up-vote news feeds like bigger cloud feed readers like Feedly do it or even convert their APIs into a service for the News app  (if someone wants to provide one for the News app, feel free to contact us by creating an issue in the bug tracker).

The URL should be a path to a directory which contains a JSON file in the format of **feeds.LANG_CODE.json** where LANG_CODE is a two character language code (e.g. **en** or **de**).

For example entering the URL **https://domain.com/directory** as explore URL will produce the following request for German users:

    GET https://domain.com/directory/feeds.de.json


**Do not forget to implement [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) in your API, otherwise the request will fail!**