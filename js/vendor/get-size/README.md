# getSize

Get the size of elements.

``` js
var size = getSize( elem );
// elem can be an element
var size = getSize( document.querySelector('#selector') )
// elem can be a string, used as a query selector
var size = getSize('#selector')
```

Returns an object with:  `width`, `height`, `innerWidth/Height`, `outerWidth/Height`, `paddingLeft/Top/Right/Bottom`, `marginLeft/Top/Right/Bottom`, `borderLeft/Top/Right/BottomWidth` and `isBorderBox`.

Browser support: IE10+, Android 4.0+, iOS 5+, and modern browsers

## Install

Install with [Bower](http://bower.io): `bower install get-size`

Install with npm: `npm install get-size`

## Firefox hidden iframe bug

[Firefox has an old bug](https://bugzilla.mozilla.org/show_bug.cgi?id=548397) that occurs within iframes that are hidden with `display: none`. To resolve this, you can use alternate CSS to hide the iframe off-screen, with out `display: none`.

``` css
.hide-iframe {
  visibility: hidden;
  position: absolute;
  left: -999em;
}
```

## MIT License

getSize is released under the [MIT License](http://desandro.mit-license.org/).
