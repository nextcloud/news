<?php

/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

// this is temporary.
//TODO: change it once the new app system is complete
require_once('../owncloud/lib/base.php');

// load SimplePie library
require_once('3rdparty/SimplePie/SimplePieAutoloader.php');

// Check if we are a user
OCP\User::checkLoggedIn();

OCP\App::checkAppEnabled('news');
OCP\App::setActiveNavigationEntry('news');

//OCP\Util::addscript('news','news');
OCP\Util::addStyle('news', 'news');

$tmpl = new OCP\Template( 'news', 'main', 'user' );
$tmpl->printPage();

?>
