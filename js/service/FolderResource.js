/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FolderResource', (Resource, $http, BASE_URL) => {
    'use strict';

    class FolderResource extends Resource {

        constructor ($http, BASE_URL) {
            super($http, BASE_URL, 'name');
            this.deleted = null;
        }


        delete (folderName) {
            let folder = this.get(folderName);
            this.deleted = folder;

            super.delete(folderName);

            return this.http.delete(`${this.BASE_URL}/folders/${folder.id}`);
        }


        toggleOpen (folderName) {
            let folder = this.get(folderName);
            folder.opened = !folder.opened;

            return this.http({
                url: `${this.BASE_URL}/folders/${folder.id}/open`,
                method: 'POST',
                data: {
                    folderId: folder.id,
                    open: folder.opened
                }
            });
        }


        rename (folderName, toFolderName) {
            toFolderName = toFolderName.toUpperCase();
            let folder = this.get(folderName);

            // still do http request if folder exists but dont change the name
            // to have one point of failure
            if (!this.get(toFolderName)) {
                folder.name = toFolderName;

                delete this.hashMap[folderName];
                this.hashMap[toFolderName] = folder;
            }

            return this.http({
                url: `${this.BASE_URL}/folders/${folder.id}/rename`,
                method: 'POST',
                data: {
                    folderName: toFolderName
                }
            });
        }


        create (folderName) {
            folderName = folderName.toUpperCase();

            // still do http request if folder exists but dont change the name
            // to have one point of failure
            if (!this.get(folderName)) {
                let folder = {
                    name: folderName
                };

                this.add(folder);
            }

            return this.http({
                url: `${this.BASE_URL}/folders`,
                method: 'POST',
                data: {
                    folderName: folderName
                }
            });
        }


        undoDelete () {
            if (this.deleted) {
                this.add(this.deleted);

                return this.http.post(
                    `${this.BASE_URL}/folders/${this.deleted.id}/restore`
                );
            }
        }


    }

    return new FolderResource($http, BASE_URL);
});