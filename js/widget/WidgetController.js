app.controller('WidgetController', function ($http, BASE_URL, ITEM_BATCH_SIZE) {
    'use strict';

    this.fetchItems = function () {
        let parameters = {
            type: 6,
            limit: ITEM_BATCH_SIZE,
            showAll: false,
            oldestFirst: false,
            search: ''
        };

        let request = {
            url: BASE_URL + '/items',
            method: 'GET',
            params: parameters,
        };
        return $http(request).then(function (response) {
            return response.data;
        });
    };

    let feeds = null;
    let items = null;
    this.fetchItems()
        .then(function (data) {
            feeds = Object.assign({}, ...data.feeds.map((f) => ({[f.id]: f})));
            items = data.items;
        });

    this.getItems = function () {
        return items;
    };

    this.getFeed = function (id) {
        return feeds[id];
    };

    this.sortIds = function (first, second) {
        return parseInt(first.value) - parseInt(second.value);
    };
});
