<?php
include('configuration.php');
if (!isset($config)) {
	die('SPS has not been properly configured.');
}
date_default_timezone_set('EST');
$GLOBALS['root_dir'] = $config['root_dir'];
lib('dbi');
lib('Datastore');
lib('Auth');
lib('common');
lib('Model');
lib('Sps');
if (isset($_SERVER['REQUEST_URI']) && preg_match('/^\/sps\/api\/.*/',($_SERVER['REQUEST_URI']))) {
    lib('Sps/Api');
    $sps = New Api();
} else if (isset($_SERVER['SHELL'])) {
    lib('Sps/Shell');
    $sps = New Shell();
} else {
    lib('Sps/Browser');
    $sps = New Browser();
}
lib('db');
lib('colors');
lib('newitem');
lib('containerview');
lib('detailwidget');
if (isset($sps->task) && file_exists($GLOBALS['root_dir'] . '/www/tasks/include/' . $sps->task . '.php')) {
    include_once($GLOBALS['root_dir'] . '/www/tasks/include/' . $sps->task . '.php');
}
function lib($library_name) {
	$LIBDIR = $GLOBALS['root_dir'] . '/include';
        if (is_file($LIBDIR.'/'.$library_name . '.php')) {
            $path = $LIBDIR.'/'.$library_name . '.php';
	    include_once($path);
        } else if (is_dir($LIBDIR.'/'.$library_name)) {
            $dir = $LIBDIR.'/'.$library_name;
            foreach (glob($dir.'/*.php') as $path) {
	        include_once($path);
            }
        }
        if (!isset($path)) {
            print "Error: lib(".$library_name.") was called but no library was found!\n";
        }
}
?>
