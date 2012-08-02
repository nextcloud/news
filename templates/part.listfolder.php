<?php

echo '<ul class="folders"' . (($depth == 0) ? 'style="margin-left: 0px !important;"' : '') .'> <li class="folder_list" >' .
	'<div class="collapsable_container" data-id="' . $folder->getId() . '">' .
		'<div class="collapsable" >' . strtoupper($folder->getName()) .
		( ($depth != 0) ?
			'<button class="svg action" id="feeds_delete" onClick="(News.Folder.delete(' . $folder->getId(). '))" title="' . $l->t('Delete folder') . '"></button>' .
			'<button class="svg action" id="feeds_edit" title="' . $l->t('Rename folder') . '"></button>'
			: '' ) .
		'</div>' .
		'<ul>';

?>