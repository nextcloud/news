# Article Enhancers
You can enhance the feed download and rendering process in the following ways:

* Adding custom full text rules
* Adding custom CSS

In any case, please consider **contributing your changes back** to either [picoFeed](https://github.com/fguillot/picoFeed) or [News](https://github.com/nextcloud/news/blob/master/css/custom.css)

## Custom Full Text Feed Rules
The News app uses [picoFeed](https://github.com/fguillot/picoFeed) for parsing RSS and Atom feeds. It also provides a web scraper which can be extended with custom rules. If you want to extend these rules or add your own ones, follow [the picoFeed documentation](https://github.com/fguillot/picoFeed/blob/master/docs/grabber.markdown#how-to-write-a-grabber-rules-file)

## Custom CSS
Sometimes you want to add additional CSS for a feed to improve the rendering. This can very easily be done by adding a CSS class to **css/custom.css** following the following naming convention:

* Take the URL from the \<link> attribute (e.g.: \<link>https://www.google.de/path?my=query \</link>)
* Extract the Domain from the URL (e.g.: www.google.de)
* Strip the leading **www.** (e.g.: google.de)
* Replace all . with - (e.g.: google-de)
* Prepend **custom-** (e.g.: custom-google-de)

Each class rule should be prefixed with **#app-content** and should only affect the article body. An example rule would be:

```css
#app-content .custom-google-de .body {
    /* Custom CSS rules here */
}
```