app.config(function ($provide, $httpProvider) {
    'use strict';

    $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));
    $provide.constant('ITEM_BATCH_SIZE', 7);

    // make sure that the CSRF header is only sent to the Nextcloud domain
    $provide.factory('CSRFInterceptor', function ($q, BASE_URL, $window) {
        return {
            request: function (config) {
                const token = $window.document.getElementsByTagName('head')[0]
                    .getAttribute('data-requesttoken');
                const domain =
                    $window.location.href.split($window.location.pathname)[0];
                if (config.url.indexOf(BASE_URL) === 0 ||
                    config.url.indexOf(domain) === 0) {
                    /*jshint camelcase: false */
                    config.headers.requesttoken = token;
                }

                return config || $q.when(config);
            }
        };
    });
    let errorMessages = {
        0: t('news', 'Request failed, network connection unavailable!'),
        401: t('news', 'Request unauthorized. Are you logged in?'),
        403: t('news', 'Request forbidden. Are you an admin?'),
        412: t('news', 'Token expired or app not enabled! Reload the page!'),
        500: t('news', 'Internal server error! Please check your ' +
            'data/nextcloud.log file for additional ' +
            'information!'),
        503: t('news', 'Request failed, Nextcloud is in currently ' +
            'in maintenance mode!')
    };
    $provide.factory('ConnectionErrorInterceptor', function ($q, $timeout) {
        var timer;
        return {
            responseError: function (response) {
                // status 0 is a network error
                function sendNotification() {
                    OC.Notification.showHtml(errorMessages[response.status]);
                    timer = $timeout(function () {
                        OC.Notification.hide();
                    }, 5000);
                }

                if (response.status in errorMessages) {
                    if (timer) {
                        timer.then(function () {
                            sendNotification();
                        });
                    } else {
                        sendNotification();
                    }
                }
                return $q.reject(response);
            }
        };
    });
    $httpProvider.interceptors.push('CSRFInterceptor');
    $httpProvider.interceptors.push('ConnectionErrorInterceptor');
});
