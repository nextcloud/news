# User

Welcome to the User documentation of News.

# User interface

## Feed Update Errors
If a feed fails to update eight times in a row, the interface displays a red bubble at the feed showing the number of errors since the last successful update.
When hovering the mouse pointer over the red bubble, the last error message is displayed.

## Subscribe

### Web address
The URL of the RSS feed you want to add. Make sure the URL points directly to the RSS feed or to a website that supports RSS. If the feed is not obvious, the website is searched when auto discover is enabled (see below).

### Folder
Specify a folder name to organize the feed. If you leave this field blank, the feed will be added without a folder. Use folders to group related feeds for easier navigation.

### Credentials
Provide a username and password if required for a feed. Please note that the password will be stored in plain text, meaning anyone with access to the server or database will be able to view it.

### Auto discover Feed
Check this option if you want the app to automatically detect the RSS feed from the entered URL. This is useful when you’re unsure of the exact feed URL, as the system will scan the provided website for RSS links.

## Feed options

### Mark Read
Mark the feed as read.

### Pin to Top/Unpin from Top
The feed will be displayed at the top of the list.

### Default Order/Newest First/Oldest First
Sets the sorting order for displaying this feed. The default order is set in the settings.
**Note**: For technical reasons, the sorting is based on the date the article was added to the database, not the publication date.

### Enable/Disable Full Text
When enabled, articles will be fully retrieved from the website during the next fetch, instead of using the RSS feed.

### Unread/Ignore Updated
- If **Unread Updated** is selected, previously read articles will be marked as unread when updated.
- Otherwise, changes to articles will be ignored.

### Rename
Rename the feed.

### Move
Move the feed to a different location.

### Delete
Delete the feed.

### Open Feed URL
Open the feed in a new tab or window.

## Settings

### Keyboard Shortcuts
Displays the keys for keyboard navigation.

### Article Feed Information
Opens a table with information about the article feeds:

- **Last update**: Time when the feed was last downloaded.

- **Next update**: Time when the next feed update will be done.
  (Only if activated in the admin settings, otherwise the regular update interval is used.)

- **Articles per update**: Maximum number of articles reached in a feed update.

- **Error Count**: Number of errors that have occurred since the last successful feed update.
  (When hovering the mouse pointer over the error count in a row, the last error message is displayed.)

### Display mode

- **Default Mode**:
  Displays the title and preview text across multiple lines per row.

- **Compact Mode**:
  Depending on the split mode, the title and preview text are displayed on a single line per row.

- **Screen Reader Mode**:
  A specialized mode optimized for text-to-speech programs (e.g., Orca).
  In this mode, visual elements are not relevant.
  **Key features**:
	- Keyboard navigation focuses on the article link when switching between articles.
	- During tab navigation, changing the article automatically selects the newly focused article.

### Split mode

- **Vertical**:
  The default three-column layout, with navigation, article list, and article displayed side by side.

- **Horizontal**:
  Navigation on the left, with the article list and article stacked vertically on the right.

- **Off**:
  A two-row layout where navigation and the article list are side by side. The selected article is displayed over the article list.

### Disable Mark Read Through Scrolling
Articles are not marked as read while scrolling.

### Show All Articles
All available articles are always displayed.

### Reverse Ordering (Oldest on Top)
Reverses the order so that older articles are displayed first.
**Note**: For technical reasons, the sorting is based on the date the article was added to the database, not the publication date.

### Disable Automatic Refresh
This option disables automatic feed synchronization with the backend, which occurs every 60 seconds.

### Abonnements (OPML)
Feed lists in OPML format can be imported and exported here. Feeds that cannot be loaded during the import will be ignored.

# Using News with Clients

## TODO
This documentation is work in progress.
explain sync and link to clients page

