<?php
lib('Controller/Crf');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
	$callback = $_GET['callback'];
} else {
$callback = 'foo';
//    header('HTTP/1.0 403 Forbidden');
//    exit;
}
$crf = New Crf();
$crf->fetchBatchObjectsArray();
$batch = $crf->batch;
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($batch) . ");";

?>
