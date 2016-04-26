# Sync API v2 (Draft)

The **News app** offers a RESTful API which can be used to sync folders, feeds and items. The API also supports [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) which means that you can access the API from your browser using JavaScript.

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
The status codes are:
* **200**: Everything went fine
* **403**: ownCloud Error: The provided authorization headers are invalid. No **error** object is available.
* **404**: ownCloud Error: The route can not be found. This can happen if the app is disabled or because of other reasons. No **error** object is available.
* **4xx**: There was an app related error, check the **error** object
* **5xx**: ownCloud Error: A server error occurred. This can happen if the server is in maintenance mode or because of other reasons. No **error** object is available.

The response headers are:
* **Content-Type**: application/json; charset=utf-8
* **Etag**: A string containing a cache header of maximum length 64, e.g. 6d82cbb050ddc7fa9cbb659014546e59

The response body is a JSON structure that looks like this:

```js
{
    "data": {
        // payload is in here
    },
    // if an error occured
    "error": {
        "code": 1,  // an error code that is unique in combination with
                    // the HTTP status code to distinguish between multiple error types
        "message": "Folder exists already"  // a translated error message depending on the user's set locale
    }
}
```

## Security Guidelines
Read the following notes carefully to prevent being subject to security exploits:
* All string fields in a JSON response unless explicitly noted otherwise are provided in without sanitation. This means that if you do not escape it properly before rendering you will be vulnerable to [XSS](https://www.owasp.org/index.php/Cross-site_Scripting_%28XSS%29) attacks
* Basic Auth headers can easily be decrypted by anyone since base64 is an encoding, not an encryption. Therefore only send them if you are accessing an HTTPS website or display an easy to understand warning if the user chooses HTTP

## Syncing
All routes are given relative to the base API url, e.g.: **/sync** becomes  **https://yourowncloud.com/index.php/apps/news/api/v2/sync**

There are two usecases for syncing:
* **Initial sync**: the user does not have any data at all
* **Syncing local and remote changes**: the user has synced at least once and wants submit and receive changes

### Initial Sync
The intial sync happens when a user adds an ownCloud account in your app. In that case you want to download all folders, feeds and unread/starred items. To do this, make the following request:

* **Method**: GET
* **Route**: /sync
* **HTTP headers**:
  * **Accept: "application/json"**
  * Authorization headers

This will return the following status codes:
* **200**: Successully synced
* **400**: An error occurred, check the **error** object for more information
* Other ownCloud errors, see **Response Format**

and the following HTTP headers:
* **Content-Type**: application/json; charset=utf-8
* **Etag**: A string containing a cache header of maximum size 64, e.g. 6d82cbb050ddc7fa9cbb659014546e59

and the following request body:
```js
{
    "data": {
        "folders": [{
            "id": 3,
            "name": "funny stuff"
        }, /* etc */],
        "feeds": [{
            "id": 4,
            "name": "The Oatmeal - Comics, Quizzes, & Stories",
            "faviconLink": "http://theoatmeal.com/favicon.ico",
            "folderId": 3,
            "ordering": 0,
            "isPinned": true,
            "error": {
                "code": 1,
                "message": ""
            }
        }, /* etc */],
        "items": [{
            "id": 5,
            "url": "http://grulja.wordpress.com/2013/04/29/plasma-nm-after-the-solid-sprint/",
            "title": "Plasma-nm after the solid sprint",
            "author": "Jan Grulich (grulja)",
            "pubDate": "2005-08-15T15:52:01+0000",
            "enclosures": [{
                "mime": "video/webm",
                "url": "http://video.webmfiles.org/elephants-dream.webm"
            }],
            "body": "<p>At first I have to say...</p>",
            "feedId": 4,
            "isUnread": true,
            "isStarred": true,
            "fingerprint": "08ffbcf94bd95a1faa6e9e799cc29054"
        }, /* etc */]
    }
}
```

Each resource's (aka folder/feed/item) attributes are explained in separate chapters.

**Important**: Read the **Security Guidelines**

### Sync Local And Remote Changes
After the initial sync the app has all folders, feeds and items. Now you want to push changes and retrieve updates from the server. To do this, make the following request:

* **Method**: POST
* **Route**: /sync
* **HTTP headers**:
  * **Content-Type: "application/json; charset=utf-8"**
  * **Accept: "application/json"**
  * **If-None-Match: "6d82cbb050ddc7fa9cbb659014546e59"** (Etag from the previous request to the /sync route)
  * Authorization headers

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
    },/* etc */]
}
```

If no items have been read or starred, simply leave the **items** array empty, e.g.:

```js
{
    "items": []
}
```

The response will be the same as in the initial sync except if an item's fingerprint is the same as in the database: This means that the contents of the item did not change and in order to preserve bandwidth, only the status is added to the item, e.g.:

```js
{
    "data": {
        "folders": [/* new or updated folders here */],
        "feeds": [/* new or updated feeds here */],
        "items": [{
                "id": 5,
                "isStarred": false,
                "isRead": true,
        }, /* etc */]
    }
}
```
However if an item did change, the full item will be sent to the client

If the HTTP status code was either in the **4xx** or **5xx** range, the exact same request needs to be retried when doing the next sync.


**Important**: Read the **Security Guidelines**

## Feeds
TBD

## Folders
TBD