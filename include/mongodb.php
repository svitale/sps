<?php
if (file_exists($GLOBALS['root_dir'] . '/db/database.down')) {
	die('Site down for maintenance; it should be back up in a few minutes.');
}

include('configuration.php');
if (!isset($config)) {
	die('SPS has not been properly configured.');
}

global $mongodb;
$mongodb = new MongoClient();
