<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
lib('Process');
if ($sps->task = 'crf') {
    lib('Controller/Crf');
    $task = New Crf();
} else if ($sps->task = 'store') {
    lib('Controller/Store');
    $task = New Store();
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (!isset($task->active_object)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
} else {
    $id_uuid = $task->active_object->id_uuid;
}
$process = New Process();
$process->active_object = $task->active_object;
$valid = $process->retValidProcessArray();
$logged  = $process->retProcesslogArray($id_uuid);
$process_array = array('valid'=>$valid,'logged'=>$logged);
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($process_array) . ");";
?>
