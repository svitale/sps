<?php
lib('Controller/Tracking');
/*
function logg($text) {
  $fp = fopen('/tmp/data.txt', 'w');
  fwrite($fp, $text."\n");
  fclose($fp);
}
*/

// if user is admin, do not restrict site choice
if (in_array('admin',$sps->auth->roles)) {
  $restrict_site = false;
} else {
  $restrict_site = true;
}
// if site is restricted, set to the value of username
if ($restrict_site) {
  $site = $sps->auth->username;
} else {
  $site = null;
}
// create the tracking object
$tracking = New Tracking();

// by default nothing is to be modified
// $action one of ['fetchShipment','fetchShipments','modifyShipment','createShipment','deleteShipment']
$action = null;

//check the url to get model and id requested
$uri_split = preg_split("#(?<!\/sps)\/#",$_SERVER['REQUEST_URI']);
$model = $uri_split[2];
if (count($uri_split) > 3 &&  is_numeric($uri_split[3])) {
  $id = $uri_split[3];
} else {
  $id = null;
}

// the only valid models for tracking are shipment and shipments
if (!in_array($model,array('shipment','shipments','sites'))) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
  exit;
}
// read properties set by browser

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
  $action = 'deleteShipment';
} else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
  $action = 'modifyShipment';
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $action = 'createShipment';
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
   if ($model == 'shipment' && is_numeric($id)) {
     $action = 'fetchShipment';
   } else if ($model == 'shipments') {
     $action = 'fetchShipments';
   } else if ($model == 'sites') {
     $action = 'listSites';
   } else { 
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      exit;
   }
}

switch ($action) {
  case 'fetchShipments':
    $array = $tracking->fetchShipments($site);
  break;
  case 'fetchShipment':
    $array = $tracking->fetchShipment($id);
  break;
  case 'createShipment':
    $array = $tracking->createShipment($site);
  break; 
  case 'deleteShipment':
    $array = $tracking->deleteShipment($id);
  break;
  case 'modifyShipment':
    $shipment = $tracking->fetchShipment($id);
    $post_data = json_decode(file_get_contents("php://input"),true);
    if (!$site && $post_data['site']) {
      $shipment->site = $post_data['site'];
    }
    if ($post_data['tracking_number']) {
      $shipment->tracking_number = $post_data['tracking_number'];
    }
    if ($post_data['handler']) {
      $shipment->handler = $post_data['handler'];
    }
    if ($post_data['delivery_date']) {
      $shipment->delivery_date = $post_data['delivery_date'];
    }
    $array = $shipment->modify();
  break;
  case 'listSites':
	$array = array();
    if($restrict_site) {
	 	$id = $site;
		$array[] = array('id'=>$id,'name'=>$id);
    } else {
		$array[] = array('id'=>'0101','name'=>'0101');
		$array[] = array('id'=>'0201','name'=>'0201');
		$array[] = array('id'=>'0202','name'=>'0202');
		$array[] = array('id'=>'0302','name'=>'0302');
		$array[] = array('id'=>'0303','name'=>'0303');
		$array[] = array('id'=>'0304','name'=>'0304');
		$array[] = array('id'=>'0401','name'=>'0401');
		$array[] = array('id'=>'0402','name'=>'0402');
		$array[] = array('id'=>'0403','name'=>'0403');
		$array[] = array('id'=>'0501','name'=>'0501');
		$array[] = array('id'=>'0601','name'=>'0601');
		$array[] = array('id'=>'0701','name'=>'0701');
	
    }
  break;
  default:
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
  exit;
}

header('content-type: application/json; charset=utf-8');
print json_encode($array);
?>
