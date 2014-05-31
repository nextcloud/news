<li class="add-new">
    <div class="list-title list-title-with-icon heading">
        <button data-apps-slide-toggle=".add-new-popup">+ <?php p($l->t('Add Website'))?></button>
    </div>

    <div class="add-new-popup">

        <p class="error">
            <span ng-show="Navigation.feedExistsError">
                <?php p($l->t('Error: address exists already!')); ?>
            </span>
            <span ng-show="Navigation.folderExistsError">
                <?php p($l->t('Error: folder exists already')); ?>
            </span>
        </p>
        <form>

            <input type="text"
                ng-model="feedUrl"
                placeholder="<?php p($l->t('Address')); ?>"
                name="adress"
                autofocus>
            <button title="<?php p($l->t('Add')); ?>"
                    class="primary"
                    ng-disabled="!feedUrl.trim()"
                    ng-click="createFeed(feedUrl, folderId.id)"><?php p($l->t('Add')); ?></button>
        </form>
        <form>
            <select name="folder"
                    data-create="<?php p($l->t('New folder')); ?>"
                    title="<?php p($l->t('Folder')); ?>"
                    ng-model="folderId"
                    ng-options="folder.name for folder in Navigation.getAllFolders() track by folder.name"
                    ng-hide="addNewFolder">
                <option value="" selected="selected"><?php p($l->t('Choose folder')); ?></option>
            </select>
            <button title="<?php p($l->t('New folder')); ?>"
                    ng-click="addNewFolder=true"
                    ng-hide="addNewFolder"
                    class="action-button new-button action"></button>
            <input type="text"
                    ng-model="folderName"
                    ng-if="addNewFolder"
                    name="foldername"
                    placeholder="<?php p($l->t('Folder name')); ?>"
                    autofocus
                    class="folder-input"
                    ng-keyup="{13: 'Navigation.createFolder(folderName)'}"/>
            <button title="<?php p($l->t('Back to folder selection')); ?>"
                    ng-show="addNewFolder"
                    ng-click="addNewFolder=false"
                    class="action-button back-button action"></button>
            <button title="<?php p($l->t('Create folder')); ?>"
                    ng-show="addNewFolder"
                    ng-click="Navigation.createFolder(folderName)"
                    ng-disabled="!folderName.trim()"
                    ng-class="{loading: Navigation.isAddingFolder()}"
                    class="action-button create-button action">
            </button>
        </form>

    </div>
</li>
