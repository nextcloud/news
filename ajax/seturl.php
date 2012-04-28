<?php
/**
 * Copyright (c) 2011, Frank Karlitschek <karlitschek@kde.org>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

require_once('../../../lib/base.php');
OC_Util::checkAdminUser();

OC_Config::setValue( 'somesetting', $_POST['somesetting'] );

echo 'true';

?>
