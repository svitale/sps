<?php
if (isset($_GET['type']) && $_GET['type'] != 'null') {
	$type = $_GET['type'];
} else {
	$type = null;
}
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
	$callback = $_GET['callback'];
} else {
	$callback = 'handleJSONP';
}
if (isset($_GET['id']) && $_GET['id'] != 'null') {
	$id = $_GET['id'];
} else {
	$id = null;
}
if (isset($_GET['study']) && $_GET['study'] != '') {
	$study = $_GET['study'];
} else {
	$study = null;
}
if (isset($_POST['reset'])) {
    $sps->resetSession();
}
$allowed_keys = array('datestart','dateend','id_instrument','id_assay','destination','sample_type','id_study','id_rungroup','name_plate','shipment_type','id_visit','uqc');
if (isset($_POST['filters'])) {
    $filters = $_POST['filters'];
    foreach ($filters as $key=>$value) {
        if (in_array($key,$allowed_keys)) {
            $sps->setFilter($key,$value);
        }
    }
}

//$message = $initialized->message;
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($sps) . ");";
