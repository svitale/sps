<?php
lib('Controller/Store');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


//$message = $initialized->message;



//subjectArray($idsubject);
$store = New Store();
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($store) . ");";
?>
