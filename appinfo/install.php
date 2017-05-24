<?php
namespace OCA\News\AppInfo;

use Exception;
use OC;
use Doctrine\DBAL\Platforms\MySqlPlatform;

// fail early when an incorrectly configured mysql instances is found to
// prevent update errors and data loss
$charset = OC::$server->getDatabaseConnection()->getParams()['charset'];
$platform = OC::$server->getDatabaseConnection()->getDatabasePlatform();
if ($platform instanceof MySqlPlatform && $charset !== 'utf8mb4') {
    $msg = 'App can not be installed because database MySql/MariaDb uses a ' .
           'non UTF8 charset: ' . $charset .'. Learn more on how to migrate ' .
           'your database to utf8mb4 after making a backup at ' .
           'https://dba.stackexchange.com/questions/8239/how-to-easily-convert-utf8-tables-to-utf8mb4-in-mysql-5-5';
    throw new Exception($msg);
}