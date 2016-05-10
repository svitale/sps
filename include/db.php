<?php
	if (file_exists('/tmp/database.down')) {
		die('The system is down for maintenance; it should be back up in a few minutes.');
	}

	include('configuration.php');
	if (!isset($config)) {
		die('SPS has not been properly configured.');
	}

	$conn = mysql_connect($config['db_host'], $config['db_user'], $config['db_password']);
	mysql_select_db($config['db_name']);
?>
