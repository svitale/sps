<?php
$uri_split = preg_split("#(?<!\/sps\/data\/)\/#",$_SERVER['REQUEST_URI']);

// create the object
$controller = $uri_split[3];
if (count($uri_split) > 3 &&  isset($uri_split[4]) && is_numeric($uri_split[4])) {
  $id = $uri_split[4];
} else {
  $id = null;
}
//this is the controller portion of the url 
$controller_url = parse_url($controller);
$controller_path =  $controller_url['path'];
$controller_query = '';
if(isset($controller_url['query'])) {
$controller_query =  $controller_url['query'];
} 
// determine if there is a query in the request
$query = array(); 
if($controller_query) {
    $decoded = urldecode($controller_query);
    $array = explode('&',$decoded);
    for ($i=0; $i < count($array); $i++ ) {
        $q = explode('=',$array[$i]);
        $query[$q[0]] = $q[1];
    }
}

if (!file_exists($GLOBALS['root_dir'] . '/include/Controller/'.$controller_path.'.php')) {
    print "controller: /include/Controllers/$controller_path.php not found";
    exit;
} else {
    include_once($GLOBALS['root_dir'] . '/include/Controller/'.$controller_path.'.php');
}

$Model = new $controller_path($id,$query);
if (isset($Model->model)) {
    $model = $Model->model;
} else {
    $model = $Model;
}
header('content-type: application/json; charset=utf-8');
print json_encode($model);
?>
