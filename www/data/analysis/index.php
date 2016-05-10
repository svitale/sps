<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
lib('Controller/Results');
if (isset($_POST['limit'])) {
    $limit = $_POST['limit'];
} else {
    $limit = 1000;
}
if (isset($_POST['start'])) {
    $start = $_POST['start'];
}  else {
    $start = 0;
}
if (isset($_POST['rungroup'])) {
    $rungroup = $_POST['rungroup'];
}  else {
    $rungroup = null;
}
if (isset($_POST['assay'])) {
    $assay = $_POST['assay'];
}  else {
    $assay = null;
}
if (isset($_POST['plate'])) {
    $plate = $_POST['plate'];
}  else {
    $plate = null;
}


$results = New Results();
$results->start = $start;
$results->limit = $limit;
$results->plate = $plate;
$results->assay = $assay;
$results->rungroup = $rungroup;
$results->fetchResults();
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($results) . ");";
?>
