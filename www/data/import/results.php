<?php
$referrer = $_SERVER['HTTP_REFERER'];
global $id_study;
global $task;
$template_dir = $GLOBALS['root_dir'] . '/www/files/Xlt/';
$instrument['rungroup'] = $_SESSION['username'] ."-". date('YmdHis');
print_r($_FILES);
handleUpload() {
$doc = $_FILES["file"]["tmp_name"];
if (file_exists($template_dir.'/instruments/'.$XLTemplate.'.xlt')) {
	$template = $template_dir . '/instruments/'.$XLTemplate.'.xlt';
} else {
	$template = $_FILES["file"]["tmp_name"];
}
$rangeArray = findNamedRanges($template,'results');
if (count($rangeArray) == 0) {
$rangeArray = findNamedRanges($template,'results_raw');
if (count($rangeArray) > 0) {
$dsttable = 'results_raw';
} else {
print "no results found!";
exit;
}
} else {
	$dsttable = 'results';
}

	$tmptable = 'tmp_'.$dsttable.'_'.session_id();
	$query = 'create table if not exists `'.$tmptable.'` like '.$dsttable;
	$result = mysql_query($query);
        if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        } else {
	$_SESSION['tmptable'] = $tmptable;
	$_SESSION['dsttable'] = $dsttable;
	}
	$rangeArray = findNamedRanges($template,$dsttable);
	$rangeArray = formatArray($doc,$rangeArray);
	$rangeArray = cleanArray($rangeArray);
	$rangeArray = fixDates($rangeArray);
	$insertArray = rangeInsertSQL($rangeArray,$tmptable);
	$varsArray = findNamedRanges($template,'vars');
	if (count($varsArray) > 0) {
		$varsArray = formatArray($doc,$varsArray);
		$varsArray = fixDates($varsArray);
		$updateArray = rangeUpdateSQL($varsArray,$tmptable);
		$sqlArray = array_merge($insertArray,$updateArray);
	} else {
		$sqlArray = $insertArray;
	}
	foreach ($sqlArray as $record) {
		$loaddata = mysql_query($record);
		if (!$loaddata) {
			echo 'Could not run query: ' . mysql_error();
		exit;
		}
        }
	$matchArray = matchResults($tmptable,$instrument);
	foreach ($matchArray as $update) {
		$matchquery = mysql_query($update);
		if (!$matchquery) {
			echo 'Could not run query: ' . mysql_error();
		exit;
        	}
	}
        $filterArray = filterSQL($tmptable,$instrument);
        foreach ($filterArray as $update) {
                $filterquery = mysql_query($update);
                if (!$filterquery) {
                        echo 'Could not run query: ' . mysql_error();
                exit;
                }
        }
}

	
$_SESSION['id_rungroup'] = $instrument['rungroup'];
header("Location: ".$referrer);
//printf("Records created: %d\n", mysql_affected_rows());
?>
