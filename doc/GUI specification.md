# GUI Specification

This should be a document which specifies the GUI for testing purposes.

**This specification is not yet final and only reflects the current state**

## General
* When the programs is being launched the last viewed feed is being loaded by default

## Feed List
* You can click on the following entries:
 * folders that contain feeds
 * starred
 * new articles

* When you click on a feed or folder it should:
 * get the css class 'active'
 * load the folder or feed into the right view

* When you hover over a feed it should:
 * show a css hint that you hovered over it
 * show the delete and mark all read button

* When you hover over a folder it should:
 * show a css hint that you hovered over it
 * show the edit, delete, mark all read button

* When you hover over a folder with feeds it should:
 * show the collapse/open button (open button if opened, collapse button if collapsed

* When an entry has only read items it should have the all_read class

## Controls
* When you click on the plus button it should show a Menu which shows Feed and Folder

* When you click on the Settings symbol it should show the settings popup

* When you click on the eye symbol it should toggle the SHOW_ALL and SHOW_UNREAD mode

## Modes

### SHOW_ALL
* When you activate it it should
 * Tell the server that its activated
 * Empty the cache and reload the current items
 * Show all feeds and folders in the feedlist
 * Show all items in the itemslist
 * Change the title of the eye button to "Show everything"
 * Show empty folders

* When you click on a feed load read and unread items and show items with the read class

### SHOW_UNREAD
* When you activate it it should
 * Tell the server that its activated
 * Empty the cache and reload the current items
 * Hide all feeds with all_read class in the feedlist. If all feeds of a folder are all_read, hide the folder. 
 * Hide empty folders
 * Hide all items with read class in the itemslist
 * Change the title of the eye button to "Show only unread"
 * When the feed is selected (active class) and has the all_read class dont hide it and neither hide its parent folder but do so if it is deselected

* When you click on a feed only unread items and hide items with the read class

## Items
* Hover over a item should show the bottom util bar (keep unread)

* Click on the starred item should make it starred (add the class important) and tell the server that its starred and increase the unread count for starred items by 1

* Click on the header link or 
 Click on the text body or
 Scrolling a feed beyond the top edge
 * should mark it as read (add css read class) and tell the server that its marked read and decrease the unread count of the item and its top folder and unread articles by 1


* click on keep unread text or keep unread checkbox should
 * **When not marked yet**:
 * tell the server to make it unread
 * prevent it from being marked read
 * **When marked yet**:
 * dont prevent it from being marked read
