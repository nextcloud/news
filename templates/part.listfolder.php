<?php

$folder = isset($_['folder']) ? $_['folder'] : null;
$is_root = ($folder->getId() == 0);

$l = new OC_l10n('news');

echo '<li class="collapsable_container" data-id="' . $folder->getId() . '"' . (($is_root) ? 'style="margin-left: 0px !important;"' : '') . '>' .
		'<div class="collapsable" >' .
			'<button class="collapsable_trigger action" title="' . $folder->getName() . '"></button>' .
			'<a href="#" class="collapsable_title">' .
				$folder->getName() .
			'</a>' .
			( ($is_root) ?
			''
			:
			'<button class="svg action feeds_edit" title="' . $l->t('Rename folder') . '"></button>' .
			'<button class="svg action feeds_delete" onClick="(News.Folder.delete(' . $folder->getId(). '))" title="' . $l->t('Delete folder') . '"></button>' ) .
		'</div>' .
		'<ul>';