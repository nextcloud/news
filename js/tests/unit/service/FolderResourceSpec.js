/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('FolderResource', () => {
    'use strict';

    let resource,
        http;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    beforeEach(inject((FolderResource, $httpBackend) => {
        resource = FolderResource;
        http = $httpBackend;
        FolderResource.receive([
            {id: 1, name: 'ye'},
            {id: 2, name: 'SYE'},
            {id: 3, name: 'hore', opened: true}
        ]);
    }));


    it ('should delete a folder', inject((FolderResource) => {
        http.expectDELETE('base/folders/1').respond(200, {});

        FolderResource.delete('ye');

        http.flush();

        expect(FolderResource.size()).toBe(2);
    }));


    it ('should rename a folder', inject((FolderResource) => {
        http.expectPOST('base/folders/1/rename', {
            folderName: 'HEHO'
        }).respond(200, {});

        FolderResource.rename('ye', 'heho');

        http.flush();

        expect(FolderResource.get('HEHO').id).toBe(1);
    }));


    it ('should not rename a folder if it exists', inject((FolderResource) => {
        http.expectPOST('base/folders/1/rename', {
            folderName: 'SYE'
        }).respond(200, {});

        FolderResource.rename('ye', 'sye');

        http.flush();

        expect(FolderResource.get('ye').id).toBe(1);
    }));


    it ('should open a folder', inject((FolderResource) => {
        http.expectPOST('base/folders/3/open', {
            folderId: 3,
            open: false,
        }).respond(200, {});

        FolderResource.toggleOpen('hore');

        http.flush();

        expect(FolderResource.get('hore').opened).toBe(false);
    }));


    it ('should create a folder', inject((FolderResource) => {
        http.expectPOST('base/folders', {
            folderName: 'HEY'
        }).respond(200, {});

        FolderResource.create('hey');

        http.flush();

        expect(FolderResource.size()).toBe(4);
    }));


    it ('should not create a folder if it exists', inject((FolderResource) => {
        http.expectPOST('base/folders', {
            folderName: 'SYE'
        }).respond(200, {});

        FolderResource.create('SYE');

        http.flush();

        expect(FolderResource.size()).toBe(3);
    }));


    it ('should undo a delete folder', inject((FolderResource) => {
        http.expectDELETE('base/folders/1').respond(200, {});

        FolderResource.delete('ye');

        http.flush();


        http.expectPOST('base/folders/1/restore').respond(200, {});

        FolderResource.undoDelete();

        http.flush();

        expect(FolderResource.get('ye').id).toBe(1);
    }));


    afterEach(() => {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


});
