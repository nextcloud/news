<?php

OC_Util::checkAdminUser();

OC_Util::addScript( "apptemplate", "admin" );

$tmpl = new OC_Template( 'apptemplate', 'settings');

$tmpl->assign('url',OC_Config::getValue( "somesetting", '' ));

return $tmpl->fetchPage();

?>
