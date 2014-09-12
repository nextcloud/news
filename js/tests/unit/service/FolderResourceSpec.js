/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('FolderResource', function () {
    'use strict';

    var resource,
        http;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
    }));

    afterEach(function () {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


    beforeEach(inject(function (FolderResource, $httpBackend) {
        resource = FolderResource;
        http = $httpBackend;
        FolderResource.receive([
            {id: 1, name: 'ye'},
            {id: 2, name: 'SYE'},
            {id: 3, name: 'hore', opened: true}
        ]);
    }));


    it ('should delete a folder', inject(function (FolderResource) {
        http.expectDELETE('base/folders/1').respond(200, {});

        FolderResource.delete('ye');

        http.flush();

        expect(FolderResource.size()).toBe(2);
    }));


    it ('should rename a folder', inject(function (FolderResource) {
        http.expectPOST('base/folders/1/rename', {
            folderName: 'heho'
        }).respond(200, {});

        FolderResource.rename('ye', 'heho');

        http.flush();

        expect(FolderResource.get('heho').id).toBe(1);
    }));


    it ('should handle a folderrename error', inject(function (FolderResource) {
        http.expectPOST('base/folders/1/rename', {
            folderName: 'heho'
        }).respond(400, {});

        FolderResource.rename('ye', 'heho');

        http.flush();

        expect(FolderResource.get('ye').id).toBe(1);
    }));


    it ('should open a folder', inject(function (FolderResource) {
        http.expectPOST('base/folders/3/open', {
            folderId: 3,
            open: false,
        }).respond(200, {});

        FolderResource.toggleOpen('hore');

        http.flush();

        expect(FolderResource.get('hore').opened).toBe(false);
    }));


    it ('should create a folder', inject(function (FolderResource) {
        http.expectPOST('base/folders', {
            folderName: 'hey'
        }).respond(200, {});

        FolderResource.create(' hey ');

        http.flush();

        expect(FolderResource.size()).toBe(4);
    }));


    it ('should set a folder error message', inject(function (FolderResource) {
        http.expectPOST('base/folders', {
            folderName: 'hey'
        }).respond(400, {message: 'carramba'});

        FolderResource.create('hey');

        http.flush();

        expect(FolderResource.get('hey').error).toBe('carramba');
    }));


    it ('should undo a delete folder', inject(function (FolderResource) {
        http.expectDELETE('base/folders/1').respond(200, {});

        FolderResource.delete('ye');

        http.flush();


        http.expectPOST('base/folders/1/restore').respond(200, {});

        FolderResource.undoDelete();

        http.flush();

        expect(FolderResource.get('ye').id).toBe(1);
    }));


});
