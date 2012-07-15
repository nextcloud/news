<?php

OCP\Util::addScript( 'news', 'settings');

$tmpl = new OCP\Template( 'news', 'settings');

return $tmpl->fetchPage();

