<?php
$uri_split = preg_split("#(?<!\/sps\/data\/)\/#",$_SERVER['REQUEST_URI']);

// create the collection
$controller = $uri_split[3];
if (count($uri_split) > 3 &&  is_numeric($uri_split[4])) {
  $id = $uri_split[4];
} else {
  $id = null;
}

if (!file_exists($GLOBALS['root_dir'] . '/include/Controller/'.$controller.'.php')) {
    print "controller: /include/Controllers/$controller.php not found";
    exit;
} else {
    include_once($GLOBALS['root_dir'] . '/include/Controller/'.$controller.'.php');
}

$collection = new $controller($id);
header('content-type: application/json; charset=utf-8');
print json_encode($collection);
?>
