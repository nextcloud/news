window.News = window.News || {};


(function (window, document, $, exports, undefined) {
    'use strict';

    var articleActionPlugins = [];
    var articleActionPluginsById = {};


    /**
     * @param function action An article action plugin should look like this:
     * function (article, baseUrl) {
     *     this.title = 'A title that is displayed on hover';
     *     this.iconUrl = 'An url for the icon';
     *     this.onClick = function (event, element) {
     *
     *     };
     * }
     */
    exports.addArticleAction = function (action) {
        articleActionPlugins.push(action);
        articleActionPluginsById[action.id] = action;
    };

    exports.getArticleActionPlugins = function () {
        return articleActionPlugins;
    };

    exports.getArticleActionPluginById = function (id) {
        return articleActionPluginsById[id];
    };

})(window, document, jQuery, window.News);

