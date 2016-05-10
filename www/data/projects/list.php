<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    $callback = 'callback';
    //header('HTTP/1.0 403 Forbidden');
    //exit;
}
lib('Controller/Projects');
$projects = New Projects();
$list = $projects->retList();
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($list) . ");";
?>
