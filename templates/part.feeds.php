<?php

	function print_folder(OC_News_Folder $folder, $depth){
		$l = new OC_l10n('news');
		echo '<ul class="folders"' . (($depth == 0) ? 'style="margin-left: 0px !important;"' : '') .'> <li class="folder_list" >' .
			'<div class="collapsable_container" data-id="' . $folder->getId() . '">' .
				'<div class="collapsable" >' . strtoupper($folder->getName()) .
				    ( ($depth != 0) ?
				'<button class="svg action" id="feeds_delete" onClick="(News.Folder.delete(' . $folder->getId(). '))" title="' . $l->t('Delete folder') . '"></button>' .
				'<button class="svg action" id="feeds_edit" title="' . $l->t('Rename folder') . '"></button>'
				: '' ) .
				'</div>' .
				'<ul>';

		$children = $folder->getChildren();
		foreach($children as $child) {
			if ($child instanceOf OC_News_Folder){
				print_folder($child, $depth+1);
			}
			elseif ($child instanceOf OC_News_Feed) { //onhover $(element).attr('id', 'newID');
				$tmpl = new OCP\Template("news", "part.listfeed");
				$tmpl->assign('child', $child);
				$tmpl->printpage();
			}
			else {
			//TODO:handle error in this case
			}
		}
		echo '</ul></div></li></ul>';
	}
	
	print_folder($_['allfeeds'], 0);
?>