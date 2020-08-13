window.News = window.News || {};


(function (window, document, $, exports, undefined) {
    'use strict';

    var articleActionPlugins = [];

    exports.addArticleAction = function (action) {
        articleActionPlugins.push(action);
    };

    exports.getArticleActionPlugins = function () {
        return articleActionPlugins;
    };

})(window, document, jQuery, window.News);

