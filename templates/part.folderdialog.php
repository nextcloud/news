
<div id="addfolder_dialog" title="<?php echo $l->t("Add Folder"); ?>">
<table width="100%" style="border: 0;">
<tr>
	<td>Add new folder</td>
	<td>
		<div class="add_parentfolder">
			<input id="inputfolderid" type="hidden" name="folderid" value="0" />
		</div>
	</td>
</tr>
<tr>
	<td><input type="text" id="folder_add_name" placeholder="<?php echo $l->t('Folder name'); ?>" class="news_input" /></td>
	<td><input type="submit" value="<?php echo $l->t('Add folder'); ?>" onclick="News.Folder.submit(this)" id="folder_add_submit" /></td>
</tr>
</table>