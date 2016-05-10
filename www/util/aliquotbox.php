<?php
lib('Process');
if ((isset($_GET['id']) && is_numeric($_GET['id'])) && (type($_GET['id']) == 'box') && (isset($_GET['num_daughters']) && is_numeric($_GET['num_daughters']))) {
    $container['id'] = $_GET['id'];
    $container['type'] = 'box';
    $container['num_daughters'] = $_GET['num_daughters'];
} else {
    print "either the specified item is not a box or number of daughters not specified";
    exit;
}
$sps->task = 'crf';
$sps->resetSession();
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
//$sampleset->contentsArray = $sampleset->retContentsArray();
//$daughter= $sampleset->aliquotContents();
$daughter= $sampleset->aliquotBox();
if (count($daughter) > 0) {
    header('Location: /sps/');
} else {
    print "aliquots could not be created";
}
