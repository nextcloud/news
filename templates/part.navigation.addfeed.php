<li class="add-new">
    <div class="heading icon-add">
        <button
            data-apps-slide-toggle="#new-feed"
            news-focus="[name='address']"><?php p($l->t('Subscribe'))?></button>
    </div>

    <div class="add-new-popup" id="new-feed">

        <form ng-submit="Navigation.createFeed(feed)" name="feedform">
            <input type="text"
                ng-model="feed.url"
                placeholder="<?php p($l->t('Web address')); ?>"
                name="address"
                pattern="[^\s]+"
                required>

            <!-- select a folder -->
            <select name="folder"
                title="<?php p($l->t('Folder')); ?>"
                ng-if="!Navigation.newFolder"
                ng-model="$parent.feed.folderId"
                ng-options="folder.name for folder in Navigation.getFolders() track by folder.name">
                <option value="">-- <?php p($l->t('No folder')); ?> --</option>
            </select>
            <button type="button"
                    class="icon-add add-new-folder-primary"
                    ng-hide="Navigation.newFolder"
                    title="<?php p($l->t('New folder')); ?>"
                    ng-click="Navigation.newFolder=true"
                    news-focus="#new-feed [name='folderName']"></button>

            <!-- add a folder -->
            <input type="text"
                   ng-model="$parent.feed.folder"
                   ng-class="{'ng-invalid': Navigation.newFolder && Navigation.folderNameExists($parent.feed.folder)}"
                   placeholder="<?php p($l->t('Folder name')); ?>"
                   name="folderName"
                   class="folder-input"
                   ng-if="Navigation.newFolder"
                   required>
            <button type="button"
                    ng-show="Navigation.newFolder"
                    class="icon-close add-new-folder-primary"
                    title="<?php p($l->t('Go back')); ?>"
                    ng-click="Navigation.newFolder=false; feed.folder=''"></button>


            <p class="error" ng-show="Navigation.folderNameExists(feed.folder)"><?php p($l->t('Folder exists already!')); ?></p>

            <input type="submit"
                value="<?php p($l->t('Subscribe')); ?>"
                class="primary"
                ng-disabled="Navigation.newFolder && Navigation.folderNameExists(folder.name)">
        </form>
    </div>
</li>
