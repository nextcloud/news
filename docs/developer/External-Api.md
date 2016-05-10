# External API v2 (Draft)

The **News app** offers a RESTful API which can be used to sync folders, feeds and items. The API also supports [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) which means that you can access the API from your browser using JavaScript.

In addition, an updater API is exposed which enables API users to run feed updates in parallel using a REST API or ownCloud console API.

## Conventions
This document uses the following conventions:

* Object aliases as comments
* Error objects are omitted

### Object Aliases As Comments

In order to only specify the JSON objects once, comments are used to alias them.

There are two types of aliases:
* Objects
* Object arrays

**Objects**:
```js
{
    "folder": { /* folder object */ },
}
```

means that the folder attributes will be listed inside the **folder** object

**Object arrays**:
```js
{
    "folders": [ /* array of folder objects */ ],
}
```

means that folder objects will be listed inside the **folders** array.

### Error Objects Are Omitted

This means that the error object will not be explicitely shown in the examples. All HTTP 400 response status codes contain an error object:

```json
{
    "error": {
        "code": 1,
        "message": "error message"
    }
}
```

## API Stability Contract

The API level will **change** if the following occurs:

* a required HTTP request header is added
* a required request parameter is added
* a field of a response object is removed
* a field of a response object is changed to a different datatype
* an HTTP response header is removed
* an HTTP response header is changed to a different datatype
* the meaning of an API call changes (e.g. /sync will not sync any more but show a sync timestamp)

The API level will **not change** if:

* a new HTTP response header is added
* an optional new HTTP request header is added
* a new response parameter is added (e.g. each item gets a new field "something": 1)
* The order of the JSON attributes is changed on any level (e.g. "id":3 is not the first field anymore, but the last)

You have to design your app with these things in mind!:

* **Don't depend on the order** of object attributes. In JSON it does not matter where the object attribute is since you access the value by name, not by index
* **Don't limit your app to the currently available attributes**. New ones might be added. If you don't handle them, ignore them
* **Use a library to compare versions**, ideally one that uses semantic versioning

## Authentication
Because REST is stateless you have to re-send user and password each time you access the API. Therefore running ownCloud **with SSL is highly recommended** otherwise **everyone in your network can log your credentials**.

The base URL for all calls is:

    https://yourowncloud.com/index.php/apps/news/api/v2

All defined routes in the Specification are appended to this url. To access the sync for instance you'd use the following url:

    https://yourowncloud.com/index.php/apps/news/api/v2/sync

Credentials are passed as an HTTP header using [HTTP basic auth](https://en.wikipedia.org/wiki/Basic_access_authentication#Client_side):

    Authorization: Basic $CREDENTIALS

where $CREDENTIALS is:

    base64(USER:PASSWORD)

This authentication/authorization method will be the recommended default until core provides an easy way to do OAuth

## Request Format
The required request headers are:
* **Accept**: application/json

Any request method except GET:
* **Content-Type**: application/json; charset=utf-8

Any route that allows caching:
* **If-None-Match**: an Etag, e.g. 6d82cbb050ddc7fa9cbb659014546e59. If no previous Etag is known, this header should be omitted

The request body is either passed in the URL in case of a **GET** request (e.g.: **?foo=bar&index=0**) or as JSON, e.g.:

```json
{
    "foo": "bar",
    "index": 0
}
```

## Response Format
The status codes are not always provided by the News app itself, but might also be returned because of ownCloud internal errors.

The following status codes can always be returned by ownCloud:
* **401**: The provided credentials to log into ownCloud are invalid.
* **403**: The user is not allowed to access the route. This can happen if for instance of only users in the admin group can access the route and the user is not in it.
* **404**: The route can not be found or the resource does not exist. Can also happen if for instance you are trying to delete a folder which does not exist.
* **5xx**: An internal server error occurred. This can happen if the server is in maintenance mode or because of other reasons.

The following status codes are returned by News:
* **200**: Everything went fine
* **304**: In case the resource was not modified, contains no response body. This means that you can ignore the request since everything is up to date.
* **400**: There was an app related error, check the **error** object if specified
* **409**: Conflict error which means that the resource exists already. Can be returned when updating (**PATCH**) or creating (**POST**) a resource, e.g. a folder

The response headers are:
* **Content-Type**: application/json; charset=utf-8
* **Etag**: A string containing a cache header of maximum length 64, e.g. 6d82cbb050ddc7fa9cbb659014546e59

The response body is a JSON structure that looks like this, which contains the actual data on the first level. The key is the resource in singular if it's a single resource or plural if its a collection. In case of HTTP 400, an error object is also present to help distinguishing between different error types:

```json
{
    "error": {
        "code": 1,
        "message": "error message"
    }
}
```

* **error**: Only present when an HTTP 400 is returned to help distinguishing between error causes
  * **code**: A unique error code
  * **message**: A translated error message. The user's configured locale is used.

In case of an **4xx** or **5xx** error the request was not successful and has to be retried. For instance marking items as read locally and syncing should send the same request again the next time the user syncs in case an error occured.

## Security Guidelines
Read the following notes carefully to prevent being subject to security exploits:
* You should always enforce SSL certificate verification and never offer a way to turn it off. Certificate verification is important to prevent MITM attacks which is especially important in the mobile world where users are almost always connected to untrusted networks. In case a user runs a self-signed certificate on his server ask him to either install his certificate on his device or direct him to one of the many ways to sign his certificate for free (most notably letsencrypt.com)
* All string fields in a JSON response **expect an item's body** are **not sanitized**. This means that if you do not escape it properly before rendering you will be vulnerable to [XSS](https://www.owasp.org/index.php/Cross-site_Scripting_%28XSS%29) attacks
* Basic Auth headers can easily be decrypted by anyone since base64 is an encoding, not an encryption. Therefore only send them if you are accessing an HTTPS website or display an easy to understand warning if the user chooses HTTP
* When creating a feed you can choose to add basic auth authentication credentials. These must be stored in clear text so anyone with access to your database (however they might have achieved it, think of Sql injection) can read them and use them to access the website. You should warn the user about this.
* If you are building a client in JavaScript or are using a link with **target="blank"**, remember to set the **window.opener** property to **null** and/or add a **rel="noreferrer"** to your link to prevent your app from being [target by an XSS attack](https://medium.com/@jitbit/target-blank-the-most-underestimated-vulnerability-ever-96e328301f4c#.wf2ddytbh)

## Syncing
All routes are given relative to the base API url, e.g.: **/sync** becomes  **https://yourowncloud.com/index.php/apps/news/api/v2/sync**

There are two usecases for syncing:
* **Initial sync**: the user does not have any data at all
* **Syncing local and remote changes**: the user has synced at least once and wants to submit and receive changes

### Initial Sync
The intial sync happens when a user adds an ownCloud account in your app. In that case you want to download all folders, feeds and unread/starred items. To do this, make the following request:

* **Method**: GET
* **Route**: /sync
* **Authentication**: [required](#authentication)
* **HTTP headers**:
  * **Accept: "application/json"**

This will return the following status codes:
* **200**: Success

and the following HTTP headers:
* **Content-Type**: application/json; charset=utf-8
* **Etag**: A string containing a cache header, maximum size 64 ASCII characters, e.g. 6d82cbb050ddc7fa9cbb659014546e59

and the following request body:
```js
{
    "folders": [ /* array of folder objects */ ],
    "feeds": [ /* array of feed objects */ ],
    "items": [ /* array of item objects */ ]
}
```

**Note**: Each object is explained in more detail in a separate section:
* [Folders](#folders)
* [Feeds](#feeds)
* [Items](#items)


### Sync Local And Remote Changes
After the initial sync the app has all folders, feeds and items. Now you want to push changes and retrieve updates from the server. To do this, make the following request:

* **Method**: POST
* **Route**: /sync
* **Authentication**: [required](#authentication)
* **HTTP headers**:
  * **Content-Type: "application/json; charset=utf-8"**
  * **Accept: "application/json"**
  * **If-None-Match: "6d82cbb050ddc7fa9cbb659014546e59"** (Etag from the previous request to the /sync route)

with the following request body:

```js
{
    "items": [{
            // read and starred
            "id": 5,
            "isStarred": false,
            "isRead": true,
            "fingerprint": "08ffbcf94bd95a1faa6e9e799cc29054"
        }, {
            // only read
            "id": 6,
            "isRead": true,
            "fingerprint": "09ffbcf94bd95a1faa6e9e799cc29054"
        }, {
            // only starred
            "id": 7,
            "isStarred": false,
            "fingerprint": "18ffbcf94bd95a1faa6e9e799cc29054"
    }, /* etc */]
}
```

If no items have been read or starred, simply leave the **items** array empty, e.g.:

```js
{
    "items": []
}
```

The response matches the **GET** call, except there can be two different types of item objects:
* **[Full](#full)**: Contains all attributes
* **[Reduced](#reduced)**: Contains only **id**, **isRead** and **isStarred**

The deciding factor whether a full or reduced item object is being returned depends on the fingerprint in the request: If the fingerprint matches the record in the database a reduced item object is being returned, otherwise a full object is used. Both can occur in the same items array at the same time.

The idea behind this special handling is that if the fingerprint matches the record in the database, the actual item content did not change. Therefore it is enough to know the item status. This greatly reduces the amount sent over the Net which is especially important for mobile apps.

If you push a list of items to be marked read/starred, there can also be less items in the response than the ones which were initially sent. This means that the item was deleted by the cleanup job and should be removed from the client device.

For instance let's take a look at the following example. You are **POST**ing the following JSON:
```json
{
    "items": [{
            "id": 5,
            "isStarred": false,
            "isRead": true,
            "fingerprint": "08ffbcf94bd95a1faa6e9e799cc29054"
        }, {
            "id": 6,
            "isRead": true,
            "fingerprint": "09ffbcf94bd95a1faa6e9e799cc29054"
        }, {
            "id": 7,
            "isStarred": false,
            "fingerprint": "18ffbcf94bd95a1faa6e9e799cc29054"
    }]
}
```

and receive the following output in return:

```json
{
    "items": [{
            "id": 5,
            "isStarred": false,
            "isRead": true
        }, {
            "id": 6,
            "isRead": true,
            "isStarred": false
    }]
}
```

The item with the **id** **7** is missing from the response. This means that it was deleted on the server.

## Folders
Folders are represented using the following data structure:
```json
{
    "id": 3,
    "name": "funny stuff"
}
```

The attributes mean the following:
* **id**: 64bit Integer, id
* **name**: Abitrary long text, folder's name

### Deleting A Folder
To delete a folder, use the following request:
* **Method**: DELETE
* **Route**: /folders/{id}
* **Route Parameters**:
  * **{id}**: folder's id
* **Authentication**: [required](#authentication)

The following response is being returned:

Status codes:
* **200**: Folder was deleted successfully
* **404**: Folder does not exist

In case of an HTTP 200, the deleted folder is returned in full in the response, e.g.:

```js
{
    "folder": { /* folder object */ }
}
```

**Note**: Deleted folders will not appear during the next sync so you also need to delete the folder locally afterwards. Folders should only be deleted locally if an HTTP **200** or **404** was returned.

**Note**: If you delete a folder locally, you should also delete all feeds whose **folderId** attribute matches the folder's **id** attribute and also delete all items whose **feedId** attribute matches the feeds' **id** attribute. This is done automatically on the server and will also be missing on the next request.

### Creating A Folder
To create a folder, use the following request:
* **Method**: POST
* **Route**: /folders
* **Authentication**: [required](#authentication)

with the following request body:
```json
{
    "name": "Folder name"
}
```

The following response is being returned:

Status codes:
* **200**: Folder was created successfully
* **400**: Folder creation error, check the error object:
  * **code**: 1: folder name is empty
* **409**: Folder with given name exists already

In case of an HTTP 200 or 409, the created or already existing folder is returned in full in the response, e.g.:

```js
{
    "folder": { /* folder object */ }
}
```

### Changing A Folder
The following attributes can be changed on the folder:
* **name**

To change any number of attributes on a folder, use the following request and provide as much attributes that can be changed as you want:
* **Method**: PATCH
* **Route**: /folders/{id}
* **Route Parameters**:
  * **{id}**: folder's id
* **Authentication**: [required](#authentication)

with the following request body:
```json
{
    "name": "New folder name"
}
```

* **name**: Abitrary long text, the folder's name

The following response is being returned:

Status codes:
* **200**: Folder was created successfully
* **400**: Folder creation error, check the error object:
  * **code**: 1: folder name is empty
* **409**: Folder with given name exists already
* Other ownCloud errors, see [Response Format](#response-format)

In case of an HTTP 200 or 409, the changed or already existing folder is returned in full in the response, e.g.:

```js
{
    "folder": { /* folder object */ }
}
```


## Feeds
Feeds are represented using the following data structure:

```json
{
    "id": 4,
    "name": "The Oatmeal - Comics, Quizzes, & Stories",
    "faviconLink": "http://theoatmeal.com/favicon.ico",
    "folderId": 3,
    "ordering": 0,
    "fullTextEnabled": false,
    "updateMode": 0,
    "isPinned": true,
    "error": {
        "code": 1,
        "message": ""
    }
}
```

The attributes mean the following:
* **id**: 64bit Integer, id
* **name**: Abitrary long text, feed's name
* **faviconLink**: Abitrary long text, feed's favicon location, **null** if not found
* **folderId**: 64bit Integer, the feed's folder or **0** in case no folder is specified
* **ordering**: 64bit Integer, overrides the feed's default ordering:
  * **0**: Default
  * **1**: Oldest on top
  * **2**: Newest on top
* **updateMode**: 64bit Integer, describing how item updates are handled:
  * **0**: No special behavior
  * **1**: If an item is updated, mark it unread
* **isPinned**: Boolean, Used to list certain feeds before others. Feeds are first ordered by their **isPinned** value (true before false) and then by their name in alphabetical order
* **error**: error object, only present if an error occurred:
  * **code**: The error code:
    * **1**: Error occured during feed update
  * **message**: Translated error message depending on the user's configured server locale


### Deleting A Feed
To delete a feed, use the following request:
* **Method**: DELETE
* **Route**: /feeds/{id}
* **Route Parameters**:
  * **{id}**: feed's id
* **Authentication**: [required](#authentication)


The following response is being returned:

Status codes:
* **200**: Feed was deleted successfully
* **404**: Feed with given id was not found, no error object
* Other ownCloud errors, see [Response Format](#response-format)


In case of an HTTP 200, the deleted feed is returned in full in the response, e.g.:

```js
{
    "feed": { /* feed object */ }
}
```

**Note**: Deleted feeds will not appear during the next sync so you also need to delete the feed locally afterwards. Feeds should only be deleted locally if an HTTP **200** or **404** was returned.

**Note**: If you delete a feed locally, you should also delete all items whose **feedId** attribute matches the feeds' **id** attribute. This is done automatically on the server and will also be missing on the next request.

### Creating A feed
To create a feed, use the following request:
* **Method**: POST
* **Route**: /feeds
* **Authentication**: [required](#authentication)


with the following request body:
```json
{
    "url": "https://feed.url.com",
    "name": "Folder name",
    "ordering": 0,
    "isPinned": true,
    "fullTextEnabled": false,
    "basicAuthUser": "user",
    "basicAuthPassword": "password"
}
```
* **url**: Abitrary long text, the url needs to have the full schema e.g. https://the-url.com. In case the user omits the schema, prepending **https** is recommended
* **name (optional)**: Abitrary long text, the feeds name or if not given taken from the RSS/Atom feed
* **basicAuthUser (optional)**: Abitrary long text, if given basic auth headers are sent for the feed
* **basicAuthPassword (optional)**: Abitrary long text, if given basic auth headers are sent for the feed
* **ordering (optional)**: See [Feeds](#feeds)
* **isPinned (optional)**: See [Feeds](#feeds)
* **fullTextEnabled (optional)**: See [Feeds](#feeds)


The following response is being returned:

Status codes:
* **200**: Feed was created successfully
* **400**: Feed creation error, check the **error** object:
  * **code**: 1: url is empty
  * **code**: 2: malformed xml
  * **code**: 3: no feed found for url (e.g. website does not have an RSS or Atom feed or direct link to feed is no feed)
  * **code**: 4: feed format not supported (e.g. too old RSS version)
  * **code**: 5: ssl issues (e.g. SSL certificate is invalid or php has issues accessing certificates on your server)
  * **code**: 6: url can not be found or accessed
  * **code**: 7: maximum redirects reached
  * **code**: 8: maximum size reached
  * **code**: 9: request timed out
  * **code**: 10: invalid or missing http basic auth headers
  * **code**: 11: not allowed to access the feed (difference here is that the user can be authenticated but not allowed to access the feed)

In case of an HTTP 200, the created feed is returned in full in the response, e.g.:

```js
{
    "feed": { /* feed object */ }
}
```

**Note**: Because the next sync would also pull in the added feed and items again, the added items will be omitted for saving bandwidth. This also means that after successfully creating a feed you will need to query the [sync route](#sync-local-and-remote-changes) again.

### Changing A Feed
To change a feed, use the following request:
* **Method**: PATCH
* **Route**: /feeds/{id}
* **Route Parameters**:
  * **{id}**: feed's id
* **Authentication**: [required](#authentication)


with the following request body:
```json
{
    "url": "https://feed.url.com",
    "name": "Folder name",
    "ordering": 0,
    "isPinned": true,
    "fullTextEnabled": false,
    "basicAuthUser": "user",
    "basicAuthPassword": "password"
}
```

All parameters are optional

* **url (optional)**: Abitrary long text, the url which was entered by the user with the full schema
* **name (optional)**: Abitrary long text, the feeds name or if not given taken from the RSS/Atom feed
* **basicAuthUser (optional)**: Abitrary long text, if given basic auth headers are sent for the feed
* **basicAuthPassword (optional)**: Abitrary long text, if given basic auth headers are sent for the feed
* **ordering (optional)**: See [feeds](#Feeds)
* **isPinned (optional)**: See [feeds](#Feeds)
* **fullTextEnabled (optional)**: See [feeds](#Feeds)

The following response is being returned:

Status codes:
* **200**: Feed was changed successfully
* **400**: Feed creation error, check the error object:
  * **code**: 1: url is empty
  * **code**: 2: malformed xml
  * **code**: 3: no feed found for url (e.g. website does not have an RSS or Atom feed or direct link to feed is no feed)
  * **code**: 4: feed format not supported (e.g. too old RSS version)
  * **code**: 5: ssl issues (e.g. SSL certificate is invalid or php has issues accessing certificates on your server)
  * **code**: 6: url can not be found or accessed
  * **code**: 7: maximum redirects reached
  * **code**: 8: maximum size reached
  * **code**: 9: request timed out
  * **code**: 10: invalid or missing http basic auth headers
  * **code**: 11: not allowed to access the feed (difference here is that the user can be authenticated but not allowed to access the feed)
* Other ownCloud errors, see [Response Format](#response-format)

In case of an HTTP 200, the changed feed is returned in full in the response, e.g.:

```js
{
    "feed": { /* feed object */ }
}
```

**Note**: Because the next sync would also pull in the changed feed and items again, the added or updated items will be omitted for saving bandwidth. This also means that after successfully updating a feed you will need to query the [sync route](#sync-local-and-remote-changes) again.

## Items

Items can occur in two different formats:

* Full
* Reduced

The attributes mean the following:
* **id**: 64bit Integer, id
* **url**: Abitrary long text, location of the online resource
* **title**: Abitrary long text, item's title
* **author**: Abitrary long text, name of the author/authors
* **publishedAt**: String representing an ISO 8601 DateTime object, when the item was published
* **updatedAt**: String representing an ISO 8601 DateTime object, when the item was updated
* **enclosure**: An enclosure object or null if none is present
  * **mimeType**: Abitrary long text, the enclosures mime type
  * **url**: Abitrary long text, location of the enclosure
* **body**: Abitrary long text, **sanitized (meaning: does not have to be escape)**, contains the item's content
* **feedId**: 64bit Integer, the item's feed it belongs to
* **isUnread**: Boolean, true if unread, false if read
* **isStarred**: Boolean, true if starred, false if not starred
* **fingerprint**: 64 ASCII characters, hash that is used to determine if an item is the same as an other one. The following behavior should be implemented:
  * Items in a stream (e.g. All items, folders, feeds) should be filtered so that no item with the same fingerprint is present.
  * When marking an item read, all items with the same fingerprint should also be marked as read.

### Full
A full item contains the full content:
```json
{
    "id": 5,
    "url": "http://grulja.wordpress.com/2013/04/29/plasma-nm-after-the-solid-sprint/",
    "title": "Plasma-nm after the solid sprint",
    "author": "Jan Grulich (grulja)",
    "publishedAt": "2005-08-15T15:52:01+0000",
    "updatedAt": "2005-08-15T15:52:01+0000",
    "enclosure": {
        "mimeType": "video/webm",
        "url": "http://video.webmfiles.org/elephants-dream.webm"
    },
    "body": "<p>At first I have to say...</p>",
    "feedId": 4,
    "isUnread": true,
    "isStarred": true,
    "fingerprint": "08ffbcf94bd95a1faa6e9e799cc29054"
}
```

### Reduced
A reduced item only contains the item status:
```json
{
    "id": 5,
    "isUnread": true,
    "isStarred": true
}
```

## Updater
Instead of using the built in, slow cron updater you can use the parallel update API to update feeds. The API can be accessed through REST or ownCloud console API.

The API should be used in the following way:

* Clean up before the update
* Get all feeds and user ids
* For each feed and user id, run the update
* Clean up after the update

The reference [implementation in Python](https://github.com/owncloud/news-updater) should give you a good idea how to design your own updater.

If the REST API is used, Authorization is required via Basic Auth and the user needs to be in the admin group.
If the ownCloud console API is used, no authorization is required.

### Clean Up Before Update
This is used to clean up the database. It deletes folders and feeds that are marked for deletion.

**Console API**:

    php -f /path/to/owncloud/occ news:updater:before-update

**REST API**:

* **Method**: GET
* **Route**: /updater/before-update
* **Authentication**: [admin](#authentication)

### Get All Feeds And User Ids
This call returns pairs of feed ids and user ids.

**Console API**:

    php -f /path/to/owncloud/occ news:updater:all-feeds

**REST API**:

* **Method**: GET
* **Route**: /updater/all-feeds
* **Authentication**: [admin](#authentication)


Both APIs will return the following response body or terminal output:

```js
{
    "updater": [{
      "feedId": 3,
      "userId": "john"
    }, /* etc */]
}
```

### Update A User's Feed
After all feed ids and user ids are known, feeds can be updated in parallel.

**Console API**:
* **Positional Parameters**:
  * **{feedId}**: the feed's id
  * **{userId}**: the user's id


    php -f /path/to/owncloud/occ news:updater:update-feed {feedId} {userId}

**REST API**:

* **Method**: GET
* **Route**: /updater/update-feed?feedId={feedId}&userId={userId}
* **Route Parameters**:
  * **{feedId}**: the feed's id
  * **{userId}**: the user's id
* **Authentication**: [admin](#authentication)


### Clean Up After Update
This is used to clean up the database. It removes old read articles which are not starred.

**Console API**:

    php -f /path/to/owncloud/occ news:updater:after-update

**REST API**:

* **Method**: GET
* **Route**: /updater/after-update
* **Authentication**: [admin](#authentication)

## Meta Data
The retrieve meta data about the app, use the following request:

* **Method**: GET
* **Route**: /
* **Authentication**: [required](#authentication)


The following response is being returned:

Status codes:
* **200**: Meta data accessed successfully

In case of an HTTP 200, the the following response is returned:

```json
{
    "version": "9.0.0",
    "issues": {
        "improperlyConfiguredCron": false
    },
    "user": {
        "userId": "john",
        "displayName": "John Doe",
        "avatar": {
            "data": "asdiufadfasdfjlkjlkjljdfdf",
            "mime": "image/jpeg"
        }
    }
}
```

The attributes mean the following:
* **version**: Abitrary long text, News app version
* **issues**: An object containing a dictionary of issues which need to be displayed to the user:
  * **improperlyConfiguredCron**: Boolean, if true this means that no feed updates are run on the server because the updater is misconfigured
* **user**: user information:
  * **userId**: Abitrary long text, the login name
  * **displayName**: Abitrary long text, the full name like it's displayed in the web interface
  * **avatar**: an avatar object, null if none is set
    * **data**: Abitrary long text, the user's image encoded as base64
    * **mime**: Abitrary long text, avatar mimetype
