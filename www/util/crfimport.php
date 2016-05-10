<?php
lib('ranges');
lib('dbcleanup');
lib('dbi');
$referrer = $_SERVER['HTTP_REFERER'];
global $dbrw;
$doc = $_FILES["file"]["tmp_name"];
$id_study = $GLOBALS['sps']->active_study->id_study;
$task = $GLOBALS['sps']->task;
if (file_exists('../data/crf/xltemplates/bystudy/'.$id_study.'.xlt')) {
	$template = '../data/crf/xltemplates/bystudy/'.$id_study.'.xlt';
} else {
	$template = '../data/crf/xltemplates/generic-crf.xlt';
}

// create temporary table for this upload
$dsttable = 'batch_quality';
$tmptable = 'tmp_crf_'.session_id();
$sql = 'create table if not exists `'.$tmptable.'` like '.$dsttable;
$result = mysqli_query($dbrw,$sql);
if (!$result) {
	print 'Could not run query: ' . mysql_error();
	exit;
} else {
	$_SESSION['tmptable'] = $tmptable;
	$_SESSION['dsttable'] = $dsttable;
}
$sql = 'alter table `'.$tmptable.'` drop index id_uuid';
$result = mysqli_query($dbrw,$sql);

if ($template) {
	$rangeArray = findNamedRanges($template,'crf');
	$varsArray = findNamedRanges($template,'vars');
} else {
	$rangeArray = makeNamedRanges($doc,'crf');
	$varsArray = findNamedRanges($template,'vars');
}
$rangeArray = formatArray($doc,$rangeArray);
$insertArray = rangeInsertSQL($rangeArray,$tmptable);
$updateArray[] = "update `$tmptable` set id_study = '$id_study' where id_study is null";
$updateArray[] = "update `$tmptable` set id_study = '$id_study' where id_study = ''";
$sqlArray = array_merge($insertArray,$updateArray);
foreach ($sqlArray as $record) {
	$loaddata = mysql_query($record);
	if (!$loaddata) {
		echo 'Could not run query: ' . mysql_error();
	exit;
	}
}
	
header("Location: ".$referrer);
//printf("Records created: %d\n", mysql_affected_rows());
?>
