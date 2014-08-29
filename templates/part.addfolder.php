<li class="add-new">
    <div class="heading icon-add">
        <button
            data-apps-slide-toggle="#new-folder"
            news-focus="[name='folderName']"><?php p($l->t('New Folder'))?></button>
    </div>

    <div class="add-new-popup" id="new-folder">

        <form ng-submit="Navigation.createFolder(folderName)" name="folderform">

            <!-- add a folder -->
            <input type="text"
                   ng-model="folderName"
                   placeholder="<?php p($l->t('Folder name')); ?>"
                   title="<?php p($l->t('Folder name')); ?>"
                   name="folderName"
                   required
                   news-auto-focus>

            <input type="submit"
                value="<?php p($l->t('Subscribe')); ?>"
                class="primary">
        </form>
    </div>
</li>
