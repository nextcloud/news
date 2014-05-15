# TODO
Plans and notes how stuff should be set up

## Urls
* **/items**
* **/items/starred**
* **/items/feeds/:id**
* **/items/folders/:id**

## Controllers
Left navigation:

* NavigationController

Right content:

* AllItemsController
* FeedItemsController
* FolderItemsController
* StarredItemsController

## Settings
* preventReadOnScroll
* languageCode
* compact
* oldestFirst: needs reload
* showAll: needs reload

## On start
* show global loading, load feeds, load settings, load last used feed -> service Setup.load()
* handle route events (in Setup?)
* redirect to last used feed (in Setup?)

## Compact view
* use ng-if to prevent registering tons of listeners? Using ng-class made previous interface slower, but also leads to duplication. Then again more freedom