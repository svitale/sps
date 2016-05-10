<?php
lib('Process');
if ((isset($_GET['id']) && is_numeric($_GET['id'])) && (type($_GET['id']) == 'box')) {
    $container['id'] = $_GET['id'];
    $container['type'] = 'box';
    $container['num_daughters'] = 1;
    $process_name = $_GET['process_name'];
} else {
    print "Error: The specified item is not a box";
    exit;
}
$tmptable = 'tmp_crf_'.session_id();
$dsttable = 'batch_quality';
$query = 'create table if not exists `'.$tmptable.'` like `'.$dsttable.'`';
$result = mysql_query($query);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}
$_SESSION['tmptable'] = $tmptable;
$sampleset = New Process;
$sampleset->username = $_SESSION['username'];
$sampleset->container = $container;
$sampleset->tmptable = $tmptable;
$sampleset->process_name = $process_name;
$sampleset->contentsArray = $sampleset->retContentsArray();
$process_result = $sampleset->processContents();
if (is_array($process_result)) {
    header('Location: /sps/?task=crf');
} else if ($process_result == true) {
    print "The Process has been logged";
} else {
    print "aliquots could not be created";
}
