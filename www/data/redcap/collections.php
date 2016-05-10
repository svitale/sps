<?php
lib('REDCap');
if (isset($_GET['type']) && $_GET['type'] != 'null') {
	$type = $_GET['type'];
} else {
	$type = null;
}
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
	$callback = $_GET['callback'];
}
if (isset($_GET['id']) && $_GET['id'] != 'null') {
	$id = $_GET['id'];
} else {
	$id = null;
}
if (isset($_POST['imported']) && $_POST['imported']  == 'true') {
	$imported =true; 
} else {
	$imported = false;
}
if (isset($_GET['study']) && $_GET['study'] != '') {
	$study = $_GET['study'];
} else {
	$study = null;
}

$redcap =  New REDCap();
$subjects = $redcap->retSubjects($imported);
//$message = $initialized->message;
print "$callback(" . json_encode($subjects) . ");";
