<?php
if (file_exists($GLOBALS['root_dir'] . '/db/database.down')) {
	die('Site down for maintenance; it should be back up in a few minutes.');
}

include('configuration.php');
if (!isset($config)) {
	die('SPS has not been properly configured.');
}

global $dbrw;
$dbrw = mysqli_connect($config['db_host'], $config['db_user'], $config['db_password']);
if (!$dbrw) {
	die('Could not connect: ' . mysqli_error($dbrw));
}
mysqli_select_db($dbrw, $config['db_name']);


function bakeSQLRecipe($recipe) {
	global $dbrw;
	$recipefile = $GLOBALS['root_dir'] . '/db/recipes/'.$recipe.'.sql';
	
	if (file_exists($recipefile)) {
		$file_content = file($recipefile);
		foreach($file_content as $sql) {
			if(trim($sql) != "" && strpos($sql, "--") === false){
				$result = mysqli_query($dbrw, $sql);
				if(!$result) {
					print mysqli_error($dbrw)."\r\n";
					//return false;
					//exit;
				}	
     		    }
		}
		return true;
	} else {
		return false;
	}
}
