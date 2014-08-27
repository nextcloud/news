<li class="add-new">
    <div class="heading icon-add">
        <button
            data-apps-slide-toggle=".add-new-popup"
            news-focus="[name='address']"><?php p($l->t('Subscribe'))?></button>
    </div>

    <div class="add-new-popup">

        <form>
            <input type="text"
                ng-model="feedUrl"
                placeholder="<?php p($l->t('Web-Address')); ?>"
                name="address">

            <!-- standard folder select box -->
            <div ng-hide="Navigation.newFolder">
                <select name="folder"
                    title="<?php p($l->t('Folder')); ?>"
                    ng-model="folderId"
                    ng-options="folder.name for folder in Navigation.getAllFolders() track by folder.name"
                    ng-hide="addNewFolder">
                    <option value="" selected="selected"><?php p($l->t('Top Level')); ?></option>
                </select>
                <button class="icon-add add-new-folder-primary"
                        title="<?php p($l->t('New Folder')); ?>"
                        ng-click="Navigation.newFolder=true"
                        news-focus="[name='folderName']">
            </div>

            <!-- adding a new folder -->
            <div ng-show="Navigation.newFolder">
                <input type="text"
                       ng-model="folderName"
                       placeholder="<?php p($l->t('Folder-Name')); ?>"
                       name="folderName"
                       ng-if="Navigation.newFolder">
                <button class="icon-checkmark add-new-folder-primary"
                        title="<?php p($l->t('Create folder')); ?>"
                        ng-click="Navigation.newFolder=false">
                <button class="icon-close add-new-folder-secondary"
                        title="<?php p($l->t('Cancel')); ?>"
                        ng-click="Navigation.newFolder=false">
            </div>

            </button>
            <input title="<?php p($l->t('Subscribe')); ?>"
                    value="<?php p($l->t('Subscribe')); ?>"
                    class="primary"
                    type="submit"
                    ng-disabled="!feedUrl.trim()"
                    ng-click="createFeed(feedUrl, folderId.id)">
        </form>

    </div>
</li>
