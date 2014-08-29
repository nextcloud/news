<li class="add-new">
    <div class="heading icon-add">
        <button
            data-apps-slide-toggle="#new-feed"
            news-focus="[name='address']"><?php p($l->t('Subscribe'))?></button>
    </div>

    <div class="add-new-popup" id="new-feed">

        <form ng-submit="Navigation.createFeed(feed.url, feed.folder)" name="feedform">
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
                ng-model="feed.folder"
                ng-options="folder.name for folder in Navigation.getAllFolders() track by folder.name">
                <option value="" selected="selected">-- <?php p($l->t('None')); ?> --</option>
            </select>
            <button type="button"
                    class="icon-add add-new-folder-primary"
                    ng-hide="Navigation.newFolder"
                    title="<?php p($l->t('New folder')); ?>"
                    ng-click="Navigation.newFolder=true"></button>

            <!-- add a folder -->
            <input type="text"
                   ng-model="feed.folder"
                   placeholder="<?php p($l->t('Folder name')); ?>"
                   name="folderName"
                   class="folder-input"
                   ng-if="Navigation.newFolder"
                   required
                   news-auto-focus>
            <button type="button"
                    ng-show="Navigation.newFolder"
                    class="icon-close add-new-folder-primary"
                    title="<?php p($l->t('Go back')); ?>"
                    ng-click="Navigation.newFolder=false"></button>

            <input type="submit"
                value="<?php p($l->t('Subscribe')); ?>"
                class="primary">
        </form>
    </div>
</li>
