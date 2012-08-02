<?php
	function print_folder(OC_News_Folder $folder, $depth){
		$l = new OC_l10n('news');
		include("part.listfolder.php");

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