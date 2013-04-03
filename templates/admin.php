<?php \OCP\Util::addScript('news', 'public/admin'); ?>


<fieldset class="personalblock">
	<legend><strong><?php p($l->t('News Settings')); ?></strong></legend>

	<p><?php p($l->t('To prevent the news app to amount a lot of unread items this setting can be used to automatically delete those items.')); ?></p>
	<lable for="auto-purge">
		<?php p($l->t('Set the maximum number of feed items that should be unread and not starred.')); ?>
	</label>
	<input id="news-auto-purge-limit" type="text" disabled="disabled" 
       value="<?php p($_['purgeLimit']); ?>" name="auto-purge" />

</fieldset>
