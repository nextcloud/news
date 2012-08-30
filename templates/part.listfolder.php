<?php

$folder = isset($_['folder']) ? $_['folder'] : null;

$l = new OC_l10n('news');

echo '<li class="folder open" data-id="' . $folder->getId() . '">';
	echo '<button class="collapsable_trigger" title="' . $l->t('Collapse') . '"></button>';
	echo '<a href="#" class="title">' . $folder->getName() .	'</a>';
	echo '<span class="buttons">';
		echo '<button class="svg action feeds_delete" title="' . $l->t('Delete folder') . '"></button>';
		echo '<button class="svg action feeds_edit" title="' . $l->t('Rename folder') . '"></button>';
		echo '<button class="svg action feeds_markread" title="' . $l->t('Mark all read') . '"></button>';
	echo '</span>';
	echo '<ul data-id="' . $folder->getId() . '">';