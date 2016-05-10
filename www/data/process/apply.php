<?php
lib('Process');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (isset($_POST['process_name']) && $_POST['process_name'] != 'null') {
    $process_name = $_POST['process_name'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if ($sps->task = 'crf') {
    lib('Controller/Crf');
    $task = New Crf();
} else {
    lib('Controller/Store');
    $task = New Store();
}
if (!isset($task->active_object)) {
    print "Error: No Active Object";
    exit;
}
$sample = $task->active_object;
$process = New Process();
$process->record = $sample;
$process->tmptable = 'batch_quality';
$process->username = $_SESSION['username'];
$process->process_name = $process_name;
$return = $process->processRecord();
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($return) . ");";
?>
