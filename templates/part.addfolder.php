<li class="add-new">
    <div class="heading icon-add">
        <button
            data-apps-slide-toggle="#new-folder"
            news-focus="[name='folderName']"><?php p($l->t('New Folder'))?></button>
    </div>

    <div class="add-new-popup" id="new-folder">

        <form ng-submit="Navigation.createFolder(folder)" name="folderform">

            <!-- add a folder -->
            <input type="text"
                   ng-class="{'ng-invalid': Navigation.folderNameExists(folder.name)}"
                   ng-model="folder.name"
                   class="folder-input"
                   placeholder="<?php p($l->t('Folder name')); ?>"
                   title="<?php p($l->t('Folder name')); ?>"
                   name="folderName"
                   required
                   news-auto-focus>

            <p class="error" ng-show="Navigation.folderNameExists(folder.name)"><?php p($l->t('Folder exists already!')); ?></p>

            <input type="submit"
                value="<?php p($l->t('Subscribe')); ?>"
                class="primary"
                ng-disabled="Navigation.folderNameExists(folder.name)">
        </form>
    </div>
</li>
