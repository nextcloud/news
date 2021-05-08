# Custom CSS
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