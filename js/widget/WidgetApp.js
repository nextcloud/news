/* jshint unused: false */
let app = angular.module('NewsWidget', []);

document.addEventListener('DOMContentLoaded', () => {
    'use strict';
    window.OCA.Dashboard.register('news', (el) => {
        el.innerHTML = ('<widget-component/>');

        angular.bootstrap(el, ['NewsWidget']);
    });
});
