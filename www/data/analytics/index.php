<?php
lib('Analytics');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


//$message = $initialized->message;



//subjectArray($idsubject);
$apikey = $sps->tokenize();
$analytics= New Analytics();
$analytics->apikey = $apikey;
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($analytics) . ");";
?>
