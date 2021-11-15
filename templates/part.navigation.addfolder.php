<li class="add-new">
    <div class="heading">
        <button
            class="icon-add-folder"
            data-apps-slide-toggle="#new-folder"
            news-focus="#new-folder [name='folderName']">
                <?php p($l->t('New Folder'))?>
        </button>
    </div>

    <div class="add-new-popup" id="new-folder">

        <form ng-submit="Navigation.createFolder(folder)" name="folderform">
            <fieldset ng-disabled="Navigation.addingFolder">
            <!-- add a folder -->
                <input type="text"
                       ng-class="{
                            'ng-invalid': !Navigation.addingFolder &&
                                Navigation.folderNameExists(folder.name)
                        }"
                       ng-model="folder.name"
                       placeholder="<?php p($l->t('Folder name')); ?>"
                       title="<?php p($l->t('Folder name')); ?>"
                       name="folderName"
                       required>

                <p class="error" ng-show="!Navigation.addingFolder &&
                    Navigation.folderNameExists(folder.name)">
                    <?php p($l->t('Folder exists already!')); ?>
                </p>

                <input type="submit"
                    value="<?php p($l->t('Create')); ?>"
                    class="primary"
                    ng-disabled="Navigation.folderNameExists(folder.name)">
            </fieldset>
        </form>
    </div>
</li>
