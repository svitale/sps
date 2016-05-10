<?php
$uri_split = preg_split("#(?<!\/sps\/data\/)\/#",$_SERVER['REQUEST_URI']);

// create the collection
$controller = $uri_split[3];
if (count($uri_split) > 3 &&  is_numeric($uri_split[3])) {
  $id = $uri_split[3];
} else {
  $id = null;
}

header('content-type: application/json; charset=utf-8');
print json_encode($sps);
?>
