<?php

$folder = isset($_['folder']) ? $_['folder'] : null;
$is_root = ($folder->getId() == 0);

$l = new OC_l10n('news');

echo '<ul class="folders"' . (($is_root) ? 'style="margin-left: 0px !important;"' : '') .'> <li class="folder_list" >' .
	'<div class="collapsable_container" data-id="' . $folder->getId() . '">' .
		'<div class="collapsable" >' .
			'<button class="collapsable_trigger" title="' . $l->t($folder->getName()) . '"></button>' .
			'<span class="collapsable_title">' .
				$folder->getName() .
			'</span>' .
			( ($is_root) ?
			''
			:
			'<button class="svg action" id="feeds_delete" onClick="(News.Folder.delete(' . $folder->getId(). '))" title="' . $l->t('Delete folder') . '"></button>' .
			'<button class="svg action" id="feeds_edit" title="' . $l->t('Rename folder') . '"></button>' ) .
		'</div>' .
		'<ul>';