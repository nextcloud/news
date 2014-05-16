/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

var baseUrl = 'http://localhost';

exports.config = {
    seleniumAddress: 'http://localhost:4444/wd/hub',
    specs: ['../tests/e2e/**/*.js'],
    onPrepare: function () {
        browser.ignoreSynchronization = true;
        browser.get(baseUrl + '/owncloud/');
        browser.findElement(By.id('user')).sendKeys('admin');
        browser.findElement(By.id('password')).sendKeys('admin');
        browser.findElement(By.id('submit')).click();

        browser.driver.wait(function () {
            return browser.driver.getCurrentUrl().then(function (url) {
                return /apps/.test(url);
            });
        });
    },
    capabilities: {
        browserName: 'phantomjs',
        version: '',
        platform: 'ANY'
    },
    baseUrl: baseUrl
};