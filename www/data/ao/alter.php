<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
$taskname = $sps->task;
if ($taskname == 'crf') {
    lib('Controller/Crf');
    $task = New Crf();
} else if ($taskname == 'store') {
    lib('Controller/Store');
    $task = New Store();
} else {
   print "Error: can't figure out what to do with this record";
}
    $value = $_POST['value'];
    $field = $_POST['field'];
    $ao = new InventoryObject(); 
    $ao = $task->active_object;
    $ao->$field = $value;
    $ao->modifyRecord();
$sps->$taskname = $task;
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($sps) . ");";
?>
