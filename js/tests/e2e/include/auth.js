/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

exports.login = function (browser) {
    browser.ignoreSynchronization = true;
    browser.get('http://localhost/owncloud/');
    browser.findElement(By.id('user')).sendKeys('admin');
    browser.findElement(By.id('password')).sendKeys('admin');
    browser.findElement(By.id('submit')).click();
};