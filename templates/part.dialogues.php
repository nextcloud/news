<div id="addfolder_dialog" title="<?php echo $l->t('Add Folder'); ?>">
    <table>
    <tr>
        <td><?php echo $l->t('Add new folder'); ?></td>
        <td></td>
    </tr>
    <tr>
        <td><input type="text" id="folder_add_name" placeholder="<?php echo $l->t('Folder name'); ?>" class="news_input" /></td>
        <td><input type="submit" value="<?php echo $l->t('Add folder'); ?>" id="folder_add_submit" /></td>
    </tr>
    </table>
</div>

<div id="addfeed_dialog" title="<?php echo $l->t('Add Subscription'); ?>">
    <table>
    <tr>
        <td><?php echo $l->t('Add new feed'); ?></td>
        <td>
            <div class="add_parentfolder">
                <button class="dropdownBtn"><?php echo $l->t('Choose folder'); ?></button>
                <input class="inputfolderid" type="hidden" name="folderid" value="0" />
                <ul class="menu dropdownmenu">
                </ul>
            </div>
        </td>
    </tr>
    <tr>
        <td><input type="text" id="feed_add_url" placeholder="<?php echo $l->t('Address'); ?>" class="news_input" /></td>
        <td><input type="submit" value="<?php echo $l->t('Add'); ?>" id="feed_add_submit" /></td>
    </tr>
    </table>
</div>

<div id="changefolder_dialog" title="<?php echo $l->t('Change folder name'); ?>">
    <input class="inputfolderid" type="hidden" name="folderid" value="" />
    <table>
    <tr>
        <td><?php echo $l->t('Change folder name'); ?></td>
        <td></td>
    </tr>
    <tr>
        <td><input type="text" placeholder="<?php echo $l->t('Folder name'); ?>" class="news_input" /></td>
        <td><input type="submit" value="<?php echo $l->t('Change folder name'); ?>" /></td>
    </tr>
    </table>
</div>
