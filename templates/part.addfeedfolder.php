<div id="addfeedfolder_dialog" title="<?php echo $l->t("Add Feed/Folder"); ?>">
<table width="100%" style="border: 0;">
<tr>
	<td>
	Add new feed
	</td>
	<td>
	...where?...
	</td>
</tr>
<tr>
	<td>
	<input type="text" id="feed_add_url" placeholder="<?php echo $l->t('URL'); ?>" class="news_input" />
	</td>
	<td>
	<input type="submit" value="<?php echo $l->t('Add feed'); ?>" id="feed_add_submit" />
	</td>
</tr>
	<td>
	Add new folder
	</td>
	<td>
	...where?...
	</td>
</tr>
<tr>
	<td>
	<input type="text" id="folder_add_name" placeholder="<?php echo $l->t('Folder name'); ?>" class="news_input" />
	</td>
	<td>
	<input type="submit" value="<?php echo $l->t('Add folder'); ?>" onclick="News.Feeds.submit(this)" id="folder_add_submit" />
	</td>
</tr>
</table>