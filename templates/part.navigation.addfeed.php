<li class="add-new">
    <div class="heading icon-add">
        <button
            data-apps-slide-toggle="#new-feed"
            news-focus="[name='address']"><?php p($l->t('Subscribe'))?></button>
    </div>

    <div class="add-new-popup" id="new-feed">

        <form ng-submit="Navigation.createFeed(Navigation.feed)" name="feedform">
            <fieldset ng-disabled="Navigation.addingFeed">
                <input type="text"
                    ng-model="Navigation.feed.url"
                    ng-class="{'ng-invalid':
                        !Navigation.addingFeed &&
                        Navigation.feedUrlExists(Navigation.feed.url)
                    }"
                    placeholder="<?php p($l->t('Web address')); ?>"
                    name="address"
                    pattern="[^\s]+"
                    required>

                <p class="error"
                    ng-show="!Navigation.addingFeed && Navigation.feedUrlExists(Navigation.feed.url)">
                    <?php p($l->t('Feed exists already!')); ?>
                </p>

                <!-- select a folder -->
                <select name="folder"
                    title="<?php p($l->t('Folder')); ?>"
                    ng-if="!Navigation.showNewFolder"
                    ng-model="Navigation.feed.existingFolder"
                    ng-options="folder.name for folder in Navigation.getFolders() track by folder.name">
                    <option value="">-- <?php p($l->t('No folder')); ?> --</option>
                </select>
                <button type="button"
                        class="icon-add add-new-folder-primary"
                        ng-hide="Navigation.showNewFolder"
                        title="<?php p($l->t('New folder')); ?>"
                        ng-click="Navigation.showNewFolder=true"
                        news-focus="#new-feed [name='folderName']"></button>

                <!-- add a folder -->
                <input type="text"
                       ng-model="Navigation.feed.newFolder"
                       ng-class="{'ng-invalid':
                            !Navigation.addingFeed &&
                            !Navigation.addingFeed &&
                            Navigation.showNewFolder &&
                            Navigation.folderNameExists(Navigation.feed.newFolder)
                        }"
                       placeholder="<?php p($l->t('Folder name')); ?>"
                       name="folderName"
                       ng-if="Navigation.showNewFolder"
                       required>
                <button type="button"
                        ng-show="Navigation.showNewFolder"
                        class="icon-close add-new-folder-primary"
                        title="<?php p($l->t('Go back')); ?>"
                        ng-click="Navigation.showNewFolder=false; Navigation.feed.newFolder=''"></button>


                <p class="error" ng-show="!Navigation.addingFeed && Navigation.folderNameExists(Navigation.feed.newFolder)"><?php p($l->t('Folder exists already!')); ?></p>

                <input type="submit"
                    value="<?php p($l->t('Subscribe')); ?>"
                    class="primary"
                    ng-disabled="Navigation.feedUrlExists(Navigation.feed.url) ||
                                (Navigation.showNewFolder && Navigation.folderNameExists(folder.name))">
            </fieldset>
        </form>
    </div>
</li>
