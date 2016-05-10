<?php
lib('ranges');
lib('dbcleanup');
lib('dbi');
lib('Controller/Crf');
$referrer = $_SERVER['HTTP_REFERER'];
global $id_study,$task,$dbrw;
$doc = $_FILES["file"]["tmp_name"];
$crf = New Crf();
$template =  $crf->xls_template;

// create temporary table for this upload
$dsttable = 'batch_quality';
$tmptable = 'tmp_crf_'.session_id();
$crf->tmptable = $tmptable;
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

$crf->genUuids();
	
header("Location: ".$referrer);
?>
