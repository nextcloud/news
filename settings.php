<?php

OC_Util::checkAdminUser();

OC_Util::addScript( "news", "admin" );

$tmpl = new OC_Template( 'news', 'settings');

$tmpl->assign('url',OC_Config::getValue( "somesetting", '' ));

return $tmpl->fetchPage();

?>
