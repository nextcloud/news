describe('news page', function () {
    'use strict';

    var ptor = protractor.getInstance();

    beforeEach(function () {
        browser.ignoreSynchronization = true;
        return browser.ignoreSynchronization;
    });

    beforeEach(function () {
        ptor.get('http://localhost/owncloud/');
        ptor.findElement(By.id('user')).sendKeys('admin');
        ptor.findElement(By.id('password')).sendKeys('admin');
        ptor.findElement(By.id('submit')).click();
    });


    describe('should log in', function () {

        beforeEach(function () {
            browser.ignoreSynchronization = false;
            return browser.ignoreSynchronization;
        });

        it('should go to the news page', function () {
            ptor.get('http://localhost/owncloud/index.php/apps/news/');
            ptor.getTitle().then(function (title) {
                expect(title).toBe('News - ownCloud');
            });
        });

    });
});