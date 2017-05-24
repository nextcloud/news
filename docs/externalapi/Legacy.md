# External API v1-2 (Legacy)

The **News app 1.2** offers a RESTful API

## API stability contract

The API level will **change** if the following occurs:

* A field of an object is removed
* A field of an object has a different datatype
* The meaning of an API call changes

The API level will **not change** if:

* The app version is changed (e.g. 4.0.1.2 instead of 4.0 or 4.001)
* A new attribute is added (e.g. each item gets a new field "something": 1)
* The order of the JSON attributes is changed on any level (e.g. "id":3 is not the first field anymore, but the last)

You have to design your app with these things in mind!:

* **Don't depend on the order of object attributes. In JSON it does not matter where the object attribute is since you access the value by name, not by index**
* **Don't limit your app to the currently available attributes. New ones might be added. If you don't handle them, ignore them**
* **Use a library to compare versions, ideally one that uses semantic versioning**

## Authentication & Basics
Because REST is stateless you have to send user and password each time you access the API. Therefore running Nextcloud **with SSL is highly recommended** otherwise **everyone in your network can log your credentials**.

The base URL for all calls is:

    https://yournextcloud.com/index.php/apps/news/api/v1-2/

All defined routes in the Specification are appended to this url. To access all feeds for instance use this url:

    https://yournextcloud.com/index.php/apps/news/api/v1-2/feeds

Credentials need to be passed as an HTTP header using [HTTP basic auth](https://en.wikipedia.org/wiki/Basic_access_authentication#Client_side):

    Authorization: Basic $CREDENTIALS

where $CREDENTIALS is:

    base64(USER:PASSWORD)

## How To Sync
This is a small overview over how you should sync your articles with the Nextcloud News app. For more fine-grained details about the API see further down.

All routes are given relative to the base API url (e.g.: https://yournextcloud.com/index.php/apps/news/api/v1-2)

### Initial Sync
The intial sync happens, when a user adds a Nextcloud account in your app. In that case you should fetch all feeds, folders and unread or starred articles from the News app. Do not fetch all articles, not only because it syncs faster, but also because the user is primarily interested in unread articles. To fetch all unread and starred articles, you must call 4 routes:

* **unread articles**: GET /items?type=3&getRead=false&batchSize=-1
* **starred articles**: GET /items?type=2&getRead=true&batchSize=-1
* **folders**: GET /folders
* **feeds**: GET /feeds

The JSON response structures can be viewed further down.

### Syncing
When syncing, you want to push read/unread and starred/unstarred items to the server and receive new and updated items, feeds and folders. To do that, call the following routes:

* **Notify the News app of unread articles**: PUT /items/unread/multiple {"items": [1, 3, 5] }
* **Notify the News app of read articles**: PUT /items/read/multiple {"items": [1, 3, 5]}
* **Notify the News app of starred articles**: PUT /items/starred/multiple {"items": [{"feedId": 3, "guidHash": "adadafasdasd1231"}, ...]}
* **Notify the News app of unstarred articles**: PUT /items/unstarred/multiple {"items": [{"feedId": 3, "guidHash": "adadafasdasd1231"}, ...]}
* **Get new folders**: GET /folders
* **Get new feeds**: GET /feeds
* **Get new items and modified items**: GET /items/updated?lastModified=12123123123&type=3


## Accessing API from a web application

**News 1.401** implements CORS which allows web applications to access the API. **To access the API in a webapp you need to send the correct authorization header instead of simply putting auth data into the URL!**. An example request in jQuery would look like this:

```js
$.ajax({
	type: 'GET',
	url: 'https://yournextcloud.com/index.php/apps/news/api/v1-2/version',
	contentType: 'application/json',
	success: function (response) {
		// handle success
	},
	error: function () {
		// handle errors
	},
	beforeSend: function (xhr) {
		var username = 'john';
		var password = 'doe';
		var auth = btoa(username + ':' + password);
		xhr.setRequestHeader('Authorization', 'Basic ' + auth);
	}
});
```
An example with AngularJS would look like this:
```js
angular.module('YourApp', [])
    .config(['$httpProvider', '$provide', function ($httpProvider, $provide) {
        $provide.factory('AuthInterceptor', ['Credentials', '$q', function (Credentials, $q) {
            return {
                request: function (config) {
                    // only set auth headers if url matches the api url
                    if(config.url.indexOf(Credentials.url) === 0) {
                        auth = btoa(Credentials.userName + ':' + Credentials.password);
                        config.headers['Authorization'] = 'Basic ' + auth;
                    }
                    return config || $q.when(config);
                }
            };
        }]);
        $httpProvider.interceptors.push('AuthInterceptor');
    }])
    .factory('Credentials', function () {
        return {
            userName: 'user',
            password: 'password',
            url: 'https://yournextcloud.com/index.php/apps/news/api'
        };
    })
    .run(['$http', function($http) {
        $http({
            method: 'GET',
            url: 'https://yournextcloud.com/index.php/apps/news/api/v1-2/version'
        }).success(function (data, status, header, config) {
            // handle success
        }).error(function (data, status, header, config) {
            // handle error
        });
    }]);
```

## Input
In general the input parameters can be in the URL or request body, the App Framework doesnt differentiate between them.

So JSON in the request body like:
```js
{
  "id": 3
}
```
will be treated the same as

    /?id=3

It is recommended though that you use the following convention:

* **GET**: parameters in the URL
* **POST**: parameters as JSON in the request body
* **PUT**: parameters as JSON in the request body
* **DELETE**: parameters as JSON in the request body

## Output
The output is JSON.

# Folders
## Get all folders

* **Status**: Implemented
* **Method**: GET
* **Route**: /folders
* **Parameters**: none
* **Returns**:
```js
{
  "folders": [
    {
      "id": 4,
      "name": "Media"
    }, // etc
  ]
}
```

## Create a folder
Creates a new folder and returns a new folder object

* **Status**: Implemented
* **Method**: POST
* **Route**: /folders
* **Parameters**:
```js
{
  "name": "folder name"
}
```
* **Return codes**:
 * **HTTP 409**: If the folder exists already
 * **HTTP 422**: If the folder name is invalid (for instance empty)
* **Returns**:
```js
{
  "folders": [
    {
      "id": 4,
      "name": "Media"
    }
  ]
}
```

## Delete a folder
Deletes a folder with the id folderId and all the feeds it contains

* **Status**: Implemented
* **Method**: DELETE
* **Route**: /folders/{folderId}
* **Parameters**: none
* **Return codes**:
 * **HTTP 404**: If the folder does not exist
* **Returns**: nothing

## Rename a folder
Only the name can be updated

* **Status**: Implemented
* **Method**: PUT
* **Route**: /folders/{folderId}
* **Parameters**:
```js
{
  "name": "folder name"
}
```
* **Return codes**:
 * **HTTP 409**: If the folder name does already exist
 * **HTTP 404**: If the folder does not exist
 * **HTTP 422**: If the folder name is invalid (for instance empty)
* **Returns**: nothing

## Mark items of a folder as read

* **Status**: Implemented
* **Method**: PUT
* **Route**: /folders/{folderId}/read
* **Parameters**:
```js
{
    // mark all items read lower than equal that id
    // this is mean to prevent marking items as read which the client/user does not yet know of
    "newestItemId": 10
}
```
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: nothing

# Feeds

## Sanitation

The following attributes are **not sanitized** meaning: including them in your web application can lead to  XSS:

* **title**
* **link**

## Get all feeds

* **Status**: Implemented
* **Method**: GET
* **Route**: /feeds
* **Parameters**: none
* **Returns**:
```js
{
  "feeds": [
    {
      "id": 39,
      "url": "http://feeds.feedburner.com/oatmealfeed",
      "title": "The Oatmeal - Comics, Quizzes, & Stories",
      "faviconLink": "http://theoatmeal.com/favicon.ico",
      "added": 1367063790,
      "folderId": 4,
      "unreadCount": 9,
      "ordering": 0, // 0 means no special ordering, 1 means oldest first, 2 newest first, new in 5.1.0
      "link": "http://theoatmeal.com/",
      "pinned": true // if a feed should be sorted before other feeds, added in 6.0.3,
      "updateErrorCount": 0, // added in 8.6.0, 0 if no errors occured during the last update,
                             // otherwise is incremented for each failed update.
                             // Once it reaches a threshold, a message should be displayed to the user
                             // indicating that the feed has failed to update that many times.
                             // The webapp displays the message after 50 failed updates
      "lastUpdateError": "error message here"  // added in 8.6.0, empty string or null if no update
                                               // error happened, otherwise contains the last update error message
    }, // etc
  ],
  "starredCount": 2,
  "newestItemId": 3443  // only sent if there are items
}
```

## Create a feed
Creates a new feed and returns the feed

* **Status**: Implemented
* **Method**: POST
* **Route**: /feeds
* **Parameters**:
```js
{
  "url": "http:\/\/www.cyanogenmod.org\/wp-content\/themes\/cyanogenmod\/images\/favicon.ico",
  "folderId": 81 //  id of the parent folder, 0 for root
}
```
* **Return codes**:
 * **HTTP 409**: If the feed exists already
 * **HTTP 422**: If the feed cant be read (most likely contains errors)
* **Returns**:
```js
{
  "feeds": [
    {
      "id": 39,
      "url": "http://feeds.feedburner.com/oatmealfeed",
      "title": "The Oatmeal - Comics, Quizzes, & Stories",
      "faviconLink": "http://theoatmeal.com/favicon.ico",
      "added": 1367063790,
      "folderId": 4,
      "unreadCount": 9,
      "ordering": 0, // 0 means no special ordering, 1 means oldest first, 2 newest first, new in 5.1.0
      "link": "http://theoatmeal.com/",
      "pinned": true // if a feed should be sorted before other feeds, added in 6.0.3
    }
  ],
  "newestItemId": 23 // only sent if there are items
}
```

## Delete a feed
Deletes a feed with the id feedId and all of its  items

* **Status**: Implemented
* **Method**: DELETE
* **Route**: /feeds/{feedId}
* **Parameters**: none
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: nothing

## Move a feed to a different folder

* **Status**: Implemented
* **Method**: PUT
* **Route**: /feeds/{feedId}/move
* **Parameters**:
```js
{
  "folderId": 0 //  id of the parent folder, 0 for root
}
```
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: nothing

## Rename a feed

* **Status**: Implemented in 1.807
* **Method**: PUT
* **Route**: /feeds/{feedId}/rename
* **Parameters**:
```js
{
  "feedTitle": 'New Title'
}
```
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: nothing

## Mark items of a feed as read

* **Status**: Implemented
* **Method**: PUT
* **Route**: /feeds/{feedId}/read
* **Parameters**:
```js
{
  // mark all items read lower than equal that id
  // this is mean to prevent marking items as read which the client/user does not yet know of
  "newestItemId": 10
}
```
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: nothing


# Items

## Sanitation

The following attributes are **not sanitized** meaning: including them in your web application can lead to  XSS:

* **title**
* **author**
* **url**
* **enclosureMime**
* **enclosureLink**

## Get items
* **Status**: Implemented
* **Method**: GET
* **Route**: /items
* **Parameters**:
```js
{
  "batchSize": 10, //  the number of items that should be returned, defaults to -1, new in 5.2.3: -1 returns all items
  "offset": 30, // only return older (lower than equal that id) items than the one with id 30
  "type": 1, // the type of the query (Feed: 0, Folder: 1, Starred: 2, All: 3)
  "id": 12, // the id of the folder or feed, Use 0 for Starred and All
  "getRead": true, // if true it returns all items, false returns only unread items
  "oldestFirst": false  // implemented in 3.002, if true it reverse the sort order
}
```
* **Returns**:
```js
{
  "items": [
    {
      "id": 3443,
      "guid": "http://grulja.wordpress.com/?p=76",
      "guidHash": "3059047a572cd9cd5d0bf645faffd077",
      "url": "http://grulja.wordpress.com/2013/04/29/plasma-nm-after-the-solid-sprint/",
      "title": "Plasma-nm after the solid sprint",
      "author": "Jan Grulich (grulja)",
      "pubDate": 1367270544,
      "body": "<p>At first I have to say...</p>",
      "enclosureMime": null,
      "enclosureLink": null,
      "feedId": 67,
      "unread": true,
      "starred": false,
      "lastModified": 1367273003,
      "fingerprint": "aeaae2123"  // new in 8.4.0 hash over title, enclosures, body and url. Same fingerprint means same item and it's advised to locally mark the other one read as well and filter out duplicates in folder and all articles view
    }, // etc
  ]
}
```

### Example
Autopaging would work like this:

* Get the **first 20** items from a feed with **id 12**

**GET /items**:
```js
{
  "batchSize": 20,
  "offset": 0,
  "type": 1,
  "id": 12,
  "getRead": false
}
```

The item with the lowest item id is 43.

* Get the next **20** items: **GET /items**:

```js
{
  "batchSize": 20,
  "offset": 43,
  "type": 1,
  "id": 12,
  "getRead": false
}
```


## Get updated items
This is used to stay up to date.

* **Status**: Implemented
* **Method**: GET
* **Route**: /items/updated
* **Parameters**:
```js
{
  "lastModified": 123231, // returns only items with a lastModified timestamp >= than this one
                          // this may also return already existing items whose read or starred status
                          // has been changed
  "type": 1, // the type of the query (Feed: 0, Folder: 1, Starred: 2, All: 3)
  "id": 12 // the id of the folder or feed, Use 0 for Starred and All
}
```
* **Returns**:
```js
{
  "items": [
    {
      "id": 3443,
      "guid": "http://grulja.wordpress.com/?p=76",
      "guidHash": "3059047a572cd9cd5d0bf645faffd077",
      "url": "http://grulja.wordpress.com/2013/04/29/plasma-nm-after-the-solid-sprint/",
      "title": "Plasma-nm after the solid sprint",
      "author": "Jan Grulich (grulja)",
      "pubDate": 1367270544,
      "body": "<p>At first I have to say...</p>",
      "enclosureMime": null,
      "enclosureLink": null,
      "feedId": 67,
      "unread": true,
      "starred": false,
      "lastModified": 1367273003,
      "fingerprint": "aeaae2123"  // new in 8.4.0 hash over title, enclosures, body and url. Same fingerprint means same item and it's advised to locally mark the other one read as well and filter out duplicates in folder and all articles view
    }, // etc
  ]
}
```

## Mark an item as read
* **Status**: Implemented
* **Method**: PUT
* **Route**: /items/{itemId}/read
* **Parameters**: none
* **Return codes**:
 * **HTTP 404**: If the item does not exist
* **Returns**: nothing

## Mark multiple items as read
* **Status**: Implemented in 1.2
* **Method**: PUT
* **Route**: /items/read/multiple
* **Parameters**:
```js
{
  "items": [2, 3] // ids of the items
}
```
* **Returns**: nothing

## Mark an item as unread
* **Status**: Implemented
* **Method**: PUT
* **Route**: /items/{itemId}/unread
* **Parameters**: none
* **Return codes**:
 * **HTTP 404**: If the item does not exist
* **Returns**: nothing

## Mark multiple items as unread
* **Status**: Implemented in 1.2
* **Method**: PUT
* **Route**: /items/unread/multiple
* **Parameters**:
```js
{
  "items": [2, 3] // ids of the items
}
```
* **Returns**: nothing

## Mark an item as starred
* **Status**: Implemented
* **Method**: PUT
* **Route**: /items/{feedId}/{guidHash}/star
* **Parameters**: none
* **Return codes**:
 * **HTTP 404**: If the item does not exist
* **Returns**: nothing

## Mark multiple items as starred
* **Status**: Implemented in 1.2
* **Method**: PUT
* **Route**: /items/star/multiple
* **Parameters**:
```js
{
  "items": [
    {
      "feedId": 3,
      "guidHash": "sdf"
    }, // etc
  ]
}
```
* **Returns**: nothing

## Mark an item as unstarred
* **Status**: Implemented
* **Method**: PUT
* **Route**: /items/{feedId}/{guidHash}/unstar
* **Parameters**: none
* **Return codes**:
 * **HTTP 404**: If the item does not exist
* **Returns**: nothing

## Mark multiple items as unstarred
* **Status**: Implemented in 1.2
* **Method**: PUT
* **Route**: /items/unstar/multiple
* **Parameters**:
```js
{
  "items": [
    {
      "feedId": 3,
      "guidHash": "sdf"
    }, // etc
  ]
}
```
* **Returns**: nothing

## Mark all items as read

* **Status**: Implemented
* **Method**: PUT
* **Route**: /items/read
* **Parameters**:
```js
{
    // mark all items read lower than equal that id
    // this is mean to prevent marking items as read which the client/user does not yet know of
    "newestItemId": 10
}
```
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: nothing


# Updater

To enable people to write their own update scripts instead of relying on the sequential built in web and system cron, API routes and console commands have been created.

Updating should be done in the following fashion:
* Run the cleanup before the update
* Get all feeds and user ids
* For each feed and user id, run the update command
* Run the cleanup after the update.

This [implementation in Python](https://github.com/nextcloud/news-updater) should give you a good idea how to design and run it.

## Trigger cleanup before update
This is used to clean up the database. It deletes folders and feeds that are marked for deletion

* **Status**: Implemented in 1.601
* **Authentication**: Requires admin user
* **Method**: GET
* **Route**: /cleanup/before-update
* **Returns**: Nothing

**New in 8.1.0**: The console command for achieving the same result is:

    php -f nextcloud/occ news:updater:before-update

## Get feed ids and usernames for all feeds

* **Status**: Implemented in 1.203
* **Authentication**: Requires admin user
* **Method**: GET
* **Route**: /feeds/all
* **Parameters**: none
* **Returns**:
```js
{
  "feeds": [
    {
      "id": 39,
      "userId": "john",
    }, // etc
  ]
}
```

**New in 8.1.0**: The console command for achieving the same result is:

    php -f nextcloud/occ news:updater:all-feeds


## Trigger a feed update

* **Status**: Implemented in 1.601
* **Authentication**: Requires admin user
* **Method**: GET
* **Route**: /feeds/update
* **Parameters**:
```js
{
  "userId": "john",
  "feedId": 3
}
```
* **Return codes**:
 * **HTTP 404**: If the feed does not exist
* **Returns**: Nothing

**New in 8.1.0**: The console command for achieving the same result is:

    php -f nextcloud/occ news:updater:update-feed 3 john

## Trigger cleanup after update
This is used to clean up the database. It removes old read articles which are not starred

* **Status**: Implemented in 1.601
* **Authentication**: Requires admin user
* **Method**: GET
* **Route**: /cleanup/after-update
* **Returns**: Nothing

**New in 8.1.0**: The console command for achieving the same result is:

    php -f nextcloud/occ news:updater:after-update

# Version

## Get the version

* **Status**: Implemented
* **Method**: GET
* **Route**: /version
* **Parameters**: none
* **Returns**:
```js
{
  "version": "5.2.3"
}
```

# Status

This API can be used to display warnings and errors in your client if the web app is improperly configured or not working. It is a good idea to call this route on like every 10th update and after the server connection parameters have been changed since it's likely that the user set up a new instance and configured the app improperly.

## Get the status

* **Status**: Implemented in 5.2.4
* **Method**: GET
* **Route**: /status
* **Parameters**: none
* **Returns**:
```js
{
  "version": "5.2.4",
  "warnings": {
    "improperlyConfiguredCron": false,  // if true the webapp will fail to update the feeds correctly
    "incorrectDbCharset": false
  }
}
```

If **improperlyConfiguredCron** is true you should display a warning that the app will not receive updates properly.

This is due to the fact that the installation runs the cron in ajax mode to update the feeds. This is the default if you don't change anything and means that the app will only receive feed updates if the webinterface is accessed which will lead to lost updates.

You should show the following warning and the link should be clickable:

    The News App updater is improperly configured and you will lose updates.
    See http://hisdomain.com/index.php/apps/news for instructions on how to fix it.

If **incorrectDbCharset** is true you should display a warning that database charset is set up incorrectly and updates with unicode characters might fail

# User

This API can be used to retrieve metadata about the current user

## Get the status

* **Status**: Implemented in 6.0.5
* **Method**: GET
* **Route**: /user
* **Parameters**: none
* **Returns**:
```js
{
  "userId": "john",
  "displayName": "John Doe",
  "lastLoginTimestamp": 1241231233,  // unix timestamp
  "avatar": { // if no avatar exists, this is null
    "data": "asdiufadfasdfjlkjlkjljdfdf",  // base64 encoded image
    "mime": "image/jpeg"
  }
}
```
